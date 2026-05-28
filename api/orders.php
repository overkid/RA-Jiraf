<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = db();
    crm_ensure_schema($pdo);
    $client = client_auth_user($pdo);
    if (!$client) {
        http_response_code(401);
        echo json_encode(['message' => 'Для оформления заказа войдите в аккаунт'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $input = json_decode(file_get_contents('php://input') ?: '{}', true);
    if (!is_array($input)) {
        http_response_code(422);
        echo json_encode(['message' => 'Некорректный формат запроса'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $serviceId = (int) ($input['service_id'] ?? 0);
    $comment = trim((string) ($input['comment'] ?? ''));
    $quantity = max(1, (float) ($input['quantity'] ?? 1));
    $area = max(0, (float) ($input['area'] ?? 0));
    $selectedOptionIds = array_values(array_filter(array_map('intval', (array) ($input['option_ids'] ?? [])), static fn (int $id): bool => $id > 0));

    if ($serviceId <= 0) {
        http_response_code(422);
        echo json_encode(['message' => 'Выберите услугу'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare('SELECT id, title, base_price, unit_name FROM services WHERE id = :id AND calculator_enabled = 1 LIMIT 1');
    $stmt->execute([':id' => $serviceId]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$service) {
        http_response_code(404);
        echo json_encode(['message' => 'Услуга не найдена'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $options = [];
    if ($selectedOptionIds) {
        $placeholders = implode(',', array_fill(0, count($selectedOptionIds), '?'));
        $stmt = $pdo->prepare("SELECT id, option_type, title, price_delta, multiplier FROM calculation_options WHERE service_id = ? AND is_active = 1 AND id IN ($placeholders)");
        $stmt->execute(array_merge([$serviceId], $selectedOptionIds));
        $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $basePrice = (float) ($service['base_price'] ?? 0);
    $areaFactor = $area > 0 ? $area : 1;
    $total = $basePrice * $quantity * $areaFactor;
    foreach ($options as $option) {
        $total = ($total * (float) ($option['multiplier'] ?? 1)) + (float) ($option['price_delta'] ?? 0);
    }
    $total = round(max(0, $total), 2);

    $payload = [
        'service_id' => $serviceId,
        'service_title' => (string) $service['title'],
        'quantity' => $quantity,
        'area' => $area,
        'selected_options' => $options,
        'total' => $total,
        'comment' => $comment,
    ];

    $pdo->beginTransaction();
    $stmt = $pdo->prepare(
        'INSERT INTO orders (client_id, service_id, title, status, total_amount, manager_comment, calculator_payload)
         VALUES (:client_id, :service_id, :title, "review", :total_amount, :comment, :payload)'
    );
    $stmt->execute([
        ':client_id' => (int) $client['id'],
        ':service_id' => $serviceId,
        ':title' => (string) $service['title'],
        ':total_amount' => $total,
        ':comment' => $comment,
        ':payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);
    $orderId = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare('INSERT INTO order_items (order_id, service_id, title, quantity, unit_price, amount) VALUES (:order_id, :service_id, :title, :quantity, :unit_price, :amount)');
    $stmt->execute([
        ':order_id' => $orderId,
        ':service_id' => $serviceId,
        ':title' => (string) $service['title'],
        ':quantity' => $quantity,
        ':unit_price' => $basePrice,
        ':amount' => $total,
    ]);

    $stmt = $pdo->prepare('INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, comment) VALUES (:order_id, NULL, "review", "client", "Заказ оформлен клиентом")');
    $stmt->execute([':order_id' => $orderId]);
    $pdo->commit();

    echo json_encode(['message' => 'ok', 'order_id' => $orderId, 'total' => $total], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['message' => 'Не удалось оформить заказ'], JSON_UNESCAPED_UNICODE);
}

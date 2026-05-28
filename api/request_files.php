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

$orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
if ($orderId <= 0) {
    http_response_code(422);
    echo json_encode(['message' => 'Выберите заказ'], JSON_UNESCAPED_UNICODE);
    exit;
}
if (!isset($_FILES['layout_file']) || !is_array($_FILES['layout_file'])) {
    http_response_code(422);
    echo json_encode(['message' => 'Файл не передан'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = db();
    crm_ensure_schema($pdo);
    $client = client_auth_user($pdo);
    if (!$client) {
        http_response_code(401);
        echo json_encode(['message' => 'Для загрузки макета войдите в аккаунт'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare('SELECT id FROM orders WHERE id = :id AND client_id = :client_id LIMIT 1');
    $stmt->execute([':id' => $orderId, ':client_id' => (int) $client['id']]);
    if (!$stmt->fetchColumn()) {
        http_response_code(404);
        echo json_encode(['message' => 'Заказ не найден'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stored = crm_store_uploaded_file($_FILES['layout_file'], (string) $client['phone'], null, $orderId, 'client');
    $fileId = crm_insert_file($pdo, $stored);
    echo json_encode(['message' => 'ok', 'file_id' => $fileId, 'file' => $stored], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['message' => $exception->getMessage()], JSON_UNESCAPED_UNICODE);
}

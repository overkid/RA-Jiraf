<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/db.php';

$action = (string) ($_GET['action'] ?? '');

if ($action === 'get_pricing') {
    $service = trim((string) ($_GET['service'] ?? ''));

    if ($service === '') {
        http_response_code(400);
        echo json_encode(['message' => 'Service name is required'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $stmt = db()->prepare('SELECT material_name, price_coefficient, base_price FROM service_pricing WHERE service_title = ? ORDER BY id');
        $stmt->execute([$service]);
        $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $basePrice = 500;
        if (!empty($materials)) {
            $basePrice = (int) $materials[0]['base_price'];
        }

        $formattedMaterials = array_map(function ($material) {
            return [
                'name' => $material['material_name'],
                'coefficient' => (float) $material['price_coefficient']
            ];
        }, $materials);

        echo json_encode(['materials' => $formattedMaterials, 'base_price' => $basePrice], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $exception) {
        http_response_code(500);
        echo json_encode(['message' => 'Error fetching pricing'], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

try {
    $stmt = db()->query('SELECT id, category, title, description FROM services ORDER BY category, id');
    echo json_encode(['services' => $stmt->fetchAll()], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['message' => 'Ошибка получения услуг'], JSON_UNESCAPED_UNICODE);
}


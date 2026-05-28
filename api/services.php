<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/crm.php';

try {
    $pdo = db();
    crm_ensure_schema($pdo);
    $stmt = $pdo->query('SELECT id, category, title, description, base_price, unit_name, calculator_enabled FROM services ORDER BY category, id');
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $options = $pdo->query('SELECT id, service_id, option_type, title, price_delta, multiplier, is_active, sort_order FROM calculation_options WHERE is_active = 1 ORDER BY service_id, option_type, sort_order, id')->fetchAll(PDO::FETCH_ASSOC);
    $optionsByService = [];
    foreach ($options as $option) {
        $serviceId = (int) ($option['service_id'] ?? 0);
        if ($serviceId > 0) {
            $optionsByService[$serviceId][] = $option;
        }
    }

    foreach ($services as &$service) {
        $serviceId = (int) ($service['id'] ?? 0);
        $service['options'] = $optionsByService[$serviceId] ?? [];
    }
    unset($service);

    echo json_encode(['services' => $services], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['message' => 'Ошибка получения услуг'], JSON_UNESCAPED_UNICODE);
}

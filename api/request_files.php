<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/crm.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

$phone = trim((string) ($_POST['phone'] ?? ''));
$requestId = isset($_POST['request_id']) ? (int) $_POST['request_id'] : null;
$orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : null;
$requestId = $requestId && $requestId > 0 ? $requestId : null;
$orderId = $orderId && $orderId > 0 ? $orderId : null;

if ($phone === '') {
    http_response_code(422);
    echo json_encode(['message' => 'Укажите телефон клиента'], JSON_UNESCAPED_UNICODE);
    exit;
}
if ($requestId === null && $orderId === null) {
    http_response_code(422);
    echo json_encode(['message' => 'Выберите заявку или заказ'], JSON_UNESCAPED_UNICODE);
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
    $stored = crm_store_uploaded_file($_FILES['layout_file'], $phone, $requestId, $orderId, 'client');
    $fileId = crm_insert_file($pdo, $stored);
    echo json_encode(['message' => 'ok', 'file_id' => $fileId, 'file' => $stored], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['message' => $exception->getMessage()], JSON_UNESCAPED_UNICODE);
}

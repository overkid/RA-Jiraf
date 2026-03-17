<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

$input = json_decode(file_get_contents('php://input') ?: '{}', true);
$name = trim((string) ($input['name'] ?? ''));
$phone = trim((string) ($input['phone'] ?? ''));
$comment = trim((string) ($input['comment'] ?? ''));

if ($name === '' || $phone === '') {
    http_response_code(422);
    echo json_encode(['message' => 'Имя и телефон обязательны'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $stmt = db()->prepare('INSERT INTO client_requests (name, phone, comment) VALUES (:name, :phone, :comment)');
    $stmt->execute([
        ':name' => $name,
        ':phone' => $phone,
        ':comment' => $comment,
    ]);

    echo json_encode(['message' => 'ok'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode([
        'message' => 'Ошибка сохранения заявки',
        'details' => $exception->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}


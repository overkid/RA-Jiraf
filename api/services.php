<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/db.php';

try {
    $stmt = db()->query('SELECT id, category, title, description FROM services ORDER BY category, id');
    echo json_encode(['services' => $stmt->fetchAll()], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode([
        'message' => 'Ошибка получения услуг',
        'details' => $exception->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}

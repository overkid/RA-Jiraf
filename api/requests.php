<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/db.php';

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


    $emailStmt = db()->prepare('SELECT content_value FROM site_content WHERE content_key = :key LIMIT 1');
    $emailStmt->execute([':key' => 'notification_email']);
    $notificationEmail = trim((string) ($emailStmt->fetchColumn() ?: ''));

    if ($notificationEmail !== '' && filter_var($notificationEmail, FILTER_VALIDATE_EMAIL)) {
        $subject = 'Новая заявка с сайта РА «Жираф»';
        $messageLines = [
            'Поступила новая заявка:',
            'Имя: ' . $name,
            'Телефон: ' . $phone,
            'Комментарий: ' . ($comment !== '' ? $comment : '—'),
        ];
        $message = implode("
", $messageLines);
        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
        ];

        @mail($notificationEmail, $subject, $message, implode("
", $headers));
    }

    echo json_encode(['message' => 'ok'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode([
        'message' => 'Ошибка сохранения заявки',
        'details' => $exception->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}

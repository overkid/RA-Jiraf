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

$input = json_decode(file_get_contents('php://input') ?: '{}', true);
if (!is_array($input)) {
    http_response_code(422);
    echo json_encode(['message' => 'Некорректный формат запроса'], JSON_UNESCAPED_UNICODE);
    exit;
}

$name = trim((string) ($input['name'] ?? ''));
$phone = trim((string) ($input['phone'] ?? ''));
$email = trim((string) ($input['email'] ?? ''));
$comment = trim((string) ($input['comment'] ?? ''));
$serviceTitle = trim((string) ($input['service_title'] ?? ''));
$serviceIsOther = filter_var($input['service_is_other'] ?? false, FILTER_VALIDATE_BOOLEAN);
$estimatedTotal = isset($input['estimated_total']) && is_numeric($input['estimated_total']) ? round((float) $input['estimated_total'], 2) : null;
$calculatorPayload = isset($input['calculator']) && is_array($input['calculator']) ? $input['calculator'] : null;
$stringLength = static function (string $value): int {
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
};
$phoneDigits = preg_replace('/\D+/', '', $phone);

if ($name === '' || $phone === '' || $comment === '') {
    http_response_code(422);
    echo json_encode(['message' => 'Имя, телефон и комментарий обязательны'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($stringLength($name) > 100) {
    http_response_code(422);
    echo json_encode(['message' => 'Имя не должно быть длиннее 100 символов'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!is_string($phoneDigits) || strlen($phoneDigits) !== 11 || $phoneDigits[0] !== '7') {
    http_response_code(422);
    echo json_encode(['message' => 'Введите корректный номер телефона'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($stringLength($comment) > 2000) {
    http_response_code(422);
    echo json_encode(['message' => 'Комментарий слишком длинный'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($serviceTitle !== '' && $stringLength($serviceTitle) > 255) {
    http_response_code(422);
    echo json_encode(['message' => 'Название услуги слишком длинное'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['message' => 'Введите корректный e-mail'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $serviceValue = $serviceTitle !== '' ? $serviceTitle : null;
    $calculatorJson = $calculatorPayload !== null ? json_encode($calculatorPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
    $pdo = db();
    crm_ensure_schema($pdo);

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO client_requests (name, phone, email, service_title, service_is_other, comment, calculator_payload, estimated_total)
             VALUES (:name, :phone, :email, :service_title, :service_is_other, :comment, :calculator_payload, :estimated_total)'
        );
        $stmt->execute([
            ':name' => $name,
            ':phone' => crm_normalize_phone($phone),
            ':email' => $email !== '' ? $email : null,
            ':service_title' => $serviceValue,
            ':service_is_other' => $serviceIsOther ? 1 : 0,
            ':comment' => $comment,
            ':calculator_payload' => $calculatorJson,
            ':estimated_total' => $estimatedTotal,
        ]);
    } catch (Throwable $exception) {
        $isMissingColumn =
            $exception instanceof PDOException && ($exception->getCode() === '42S22' || stripos($exception->getMessage(), 'Unknown column') !== false);

        if (!$isMissingColumn) {
            throw $exception;
        }

        $serviceLabel = $serviceIsOther ? 'Другое' : $serviceTitle;
        $fallbackComment = $comment;
        if ($serviceLabel !== '') {
            $fallbackComment = trim($comment . PHP_EOL . 'Услуга: ' . $serviceLabel);
        }

        $stmt = $pdo->prepare('INSERT INTO client_requests (name, phone, comment) VALUES (:name, :phone, :comment)');
        $stmt->execute([
            ':name' => $name,
            ':phone' => crm_normalize_phone($phone),
            ':comment' => $fallbackComment,
        ]);
    }

    $requestId = (int) $pdo->lastInsertId();
    crm_find_or_create_client($pdo, $name, $phone, $email);

    echo json_encode(['message' => 'ok', 'request_id' => $requestId], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['message' => 'Ошибка сохранения заявки'], JSON_UNESCAPED_UNICODE);
}


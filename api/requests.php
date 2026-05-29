<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/db.php';

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
$comment = trim((string) ($input['comment'] ?? ''));
$serviceTitle = trim((string) ($input['service_title'] ?? ''));
$serviceIsOther = filter_var($input['service_is_other'] ?? false, FILTER_VALIDATE_BOOLEAN);
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

try {
    $serviceValue = $serviceTitle !== '' ? $serviceTitle : null;

    try {
        $stmt = db()->prepare(
            'INSERT INTO client_requests (name, phone, service_title, service_is_other, comment)
             VALUES (:name, :phone, :service_title, :service_is_other, :comment)'
        );
        $stmt->execute([
            ':name' => $name,
            ':phone' => $phone,
            ':service_title' => $serviceValue,
            ':service_is_other' => $serviceIsOther ? 1 : 0,
            ':comment' => $comment,
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

        $stmt = db()->prepare('INSERT INTO client_requests (name, phone, comment) VALUES (:name, :phone, :comment)');
        $stmt->execute([
            ':name' => $name,
            ':phone' => $phone,
            ':comment' => $fallbackComment,
        ]);
    }

    $requestId = (int) db()->lastInsertId();

    if (!empty($input['order_options']) && is_array($input['order_options'])) {
        $orderOptions = $input['order_options'];
        $width = (int) filter_var($orderOptions['width'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 10, 'max_range' => 300]]);
        $height = (int) filter_var($orderOptions['height'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 10, 'max_range' => 300]]);
        $quantity = (int) filter_var($orderOptions['quantity'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000]]);
        $materialType = trim((string) ($orderOptions['material_type'] ?? ''));
        $calculatedPrice = (float) filter_var($orderOptions['calculated_price'] ?? 0, FILTER_VALIDATE_FLOAT);

        if ($width >= 10 && $height >= 10 && $quantity >= 1 && $materialType !== '' && $calculatedPrice > 0) {
            try {
                $optionsStmt = db()->prepare(
                    'INSERT INTO order_options (request_id, width, height, material_type, quantity, calculated_price)
                     VALUES (:request_id, :width, :height, :material_type, :quantity, :calculated_price)'
                );
                $optionsStmt->execute([
                    ':request_id' => $requestId,
                    ':width' => $width,
                    ':height' => $height,
                    ':material_type' => $materialType,
                    ':quantity' => $quantity,
                    ':calculated_price' => $calculatedPrice,
                ]);
            } catch (Throwable $optionsException) {
                // If order_options table doesn't exist, silently continue
            }
        }
    }

    echo json_encode(['message' => 'ok'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['message' => 'Ошибка сохранения заявки'], JSON_UNESCAPED_UNICODE);
}


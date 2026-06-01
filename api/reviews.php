<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['message' => 'Method not allowed']));
}

$input = json_decode((string) file_get_contents('php://input'), true) ?? [];

$name = trim((string) ($input['name'] ?? ''));
$email = trim((string) ($input['email'] ?? ''));
$rating = (int) ($input['rating'] ?? 0);
$reviewText = trim((string) ($input['review_text'] ?? ''));

error_log("DEBUG: Received data - name: $name, rating: $rating, reviewText: $reviewText");

$errors = [];

if (strlen($name) < 2 || strlen($name) > 100) {
    $errors[] = 'Имя должно быть от 2 до 100 символов';
}

if ($rating < 1 || $rating > 5) {
    $errors[] = 'Оценка должна быть от 1 до 5';
}

if (strlen($reviewText) < 5 || strlen($reviewText) > 200) {
    $errors[] = 'Текст отзыва должен быть от 5 до 200 символов';
}

if (!empty($errors)) {
    http_response_code(422);
    exit(json_encode(['message' => implode(', ', $errors)]));
}

try {
    $pdo = db();

    $stmt = $pdo->prepare('INSERT INTO client_reviews (name, email, rating, review_text, review_status) VALUES (:name, :email, :rating, :review_text, :status)');

    $success = $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':rating' => $rating,
        ':review_text' => $reviewText,
        ':status' => 'pending'
    ]);

    if (!$success) {
        throw new Exception('Failed to insert review');
    }

    $lastId = $pdo->lastInsertId();
    error_log("DEBUG: Review saved - ID: $lastId, rating value: $rating");

    // Verify what was saved
    $verify = $pdo->prepare('SELECT rating FROM client_reviews WHERE id = :id');
    $verify->execute([':id' => $lastId]);
    $saved = $verify->fetch(PDO::FETCH_ASSOC);
    error_log("DEBUG: Verified in DB - saved rating: " . ($saved['rating'] ?? 'NULL'));

    http_response_code(201);
    echo json_encode(['message' => 'ok']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Ошибка при отправке отзыва']);
}

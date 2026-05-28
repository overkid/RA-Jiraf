<?php

declare(strict_types=1);

const ORDER_STATUS_LABELS = [
    'new' => 'Новый',
    'calculation' => 'Расчёт',
    'approval' => 'Согласование',
    'prepayment' => 'Ожидает оплату',
    'production' => 'В производстве',
    'ready' => 'Готов',
    'completed' => 'Завершён',
    'cancelled' => 'Отменён',
];

const FILE_ENTITY_LABELS = [
    'request' => 'Заявка',
    'order' => 'Заказ',
];

function crm_normalize_order_status(string $status): string
{
    return array_key_exists($status, ORDER_STATUS_LABELS) ? $status : 'new';
}

function crm_ensure_schema(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS clients (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            phone VARCHAR(40) NOT NULL,
            email VARCHAR(160) NULL,
            company VARCHAR(180) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_clients_phone (phone)
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS calculation_options (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            service_id INT UNSIGNED NOT NULL,
            option_type VARCHAR(40) NOT NULL,
            title VARCHAR(160) NOT NULL,
            price_delta DECIMAL(12,2) NOT NULL DEFAULT 0,
            multiplier DECIMAL(8,3) NOT NULL DEFAULT 1,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 100,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_calculation_options_service (service_id, option_type, is_active, sort_order)
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS orders (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            client_id INT UNSIGNED NOT NULL,
            request_id INT UNSIGNED NULL,
            service_id INT UNSIGNED NULL,
            title VARCHAR(255) NOT NULL,
            status VARCHAR(40) NOT NULL DEFAULT "new",
            total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
            deadline DATE NULL,
            manager_comment TEXT NULL,
            calculator_payload JSON NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_orders_client_status (client_id, status),
            INDEX idx_orders_request (request_id)
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS order_items (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            order_id INT UNSIGNED NOT NULL,
            service_id INT UNSIGNED NULL,
            title VARCHAR(255) NOT NULL,
            quantity DECIMAL(12,2) NOT NULL DEFAULT 1,
            unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
            amount DECIMAL(12,2) NOT NULL DEFAULT 0,
            INDEX idx_order_items_order (order_id)
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS order_status_history (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            order_id INT UNSIGNED NOT NULL,
            old_status VARCHAR(40) NULL,
            new_status VARCHAR(40) NOT NULL,
            changed_by VARCHAR(80) NOT NULL,
            comment TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_order_history_order_created (order_id, created_at)
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS request_files (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            request_id INT UNSIGNED NULL,
            order_id INT UNSIGNED NULL,
            client_phone VARCHAR(40) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            stored_path VARCHAR(255) NOT NULL,
            mime_type VARCHAR(120) NOT NULL,
            file_size INT UNSIGNED NOT NULL,
            uploaded_by VARCHAR(40) NOT NULL DEFAULT "client",
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_request_files_request (request_id, created_at),
            INDEX idx_request_files_order (order_id, created_at),
            INDEX idx_request_files_phone (client_phone)
        )'
    );

    foreach ([
        'ALTER TABLE services ADD COLUMN base_price DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER description',
        'ALTER TABLE services ADD COLUMN unit_name VARCHAR(40) NOT NULL DEFAULT "шт." AFTER base_price',
        'ALTER TABLE services ADD COLUMN calculator_enabled TINYINT(1) NOT NULL DEFAULT 1 AFTER unit_name',
        'ALTER TABLE client_requests ADD COLUMN email VARCHAR(160) NULL AFTER phone',
        'ALTER TABLE client_requests ADD COLUMN calculator_payload JSON NULL AFTER comment',
        'ALTER TABLE client_requests ADD COLUMN estimated_total DECIMAL(12,2) NULL AFTER calculator_payload',
    ] as $sql) {
        try {
            $pdo->exec($sql);
        } catch (Throwable $exception) {
            // Existing column or legacy MySQL limitation; keep page working.
        }
    }

    try {
        $pdo->exec("UPDATE services SET base_price = CASE
            WHEN category = 'Широкоформатная печать' THEN 950
            WHEN category = 'Наружная реклама' THEN 3500
            WHEN category = 'Сувенирная продукция' THEN 420
            ELSE 850
        END WHERE base_price = 0");
        $optionCount = (int) $pdo->query('SELECT COUNT(*) FROM calculation_options')->fetchColumn();
        if ($optionCount === 0) {
            $pdo->exec("INSERT INTO calculation_options (service_id, option_type, title, price_delta, multiplier, sort_order)
                SELECT id, 'Материал', 'Стандарт', 0, 1, 10 FROM services");
            $pdo->exec("INSERT INTO calculation_options (service_id, option_type, title, price_delta, multiplier, sort_order)
                SELECT id, 'Срочность', 'Обычный срок', 0, 1, 10 FROM services");
            $pdo->exec("INSERT INTO calculation_options (service_id, option_type, title, price_delta, multiplier, sort_order)
                SELECT id, 'Срочность', 'Срочно +30%', 0, 1.3, 20 FROM services");
        }
    } catch (Throwable $exception) {
        // Demo seed is optional.
    }
}

function crm_normalize_phone(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone) ?: '';
    if (strlen($digits) === 10) {
        $digits = '7' . $digits;
    }
    if (strlen($digits) === 11 && $digits[0] === '8') {
        $digits = '7' . substr($digits, 1);
    }
    return $digits !== '' ? '+' . $digits : trim($phone);
}

function crm_find_or_create_client(PDO $pdo, string $name, string $phone, ?string $email = null, ?string $company = null): int
{
    $normalizedPhone = crm_normalize_phone($phone);
    $stmt = $pdo->prepare('SELECT id FROM clients WHERE phone = :phone LIMIT 1');
    $stmt->execute([':phone' => $normalizedPhone]);
    $clientId = (int) ($stmt->fetchColumn() ?: 0);

    if ($clientId > 0) {
        $stmt = $pdo->prepare('UPDATE clients SET name = COALESCE(NULLIF(:name, ""), name), email = NULLIF(:email, ""), company = NULLIF(:company, "") WHERE id = :id');
        $stmt->execute([
            ':name' => trim($name),
            ':email' => trim((string) $email),
            ':company' => trim((string) $company),
            ':id' => $clientId,
        ]);
        return $clientId;
    }

    $stmt = $pdo->prepare('INSERT INTO clients (name, phone, email, company) VALUES (:name, :phone, NULLIF(:email, ""), NULLIF(:company, ""))');
    $stmt->execute([
        ':name' => trim($name) !== '' ? trim($name) : 'Клиент',
        ':phone' => $normalizedPhone,
        ':email' => trim((string) $email),
        ':company' => trim((string) $company),
    ]);

    return (int) $pdo->lastInsertId();
}

function crm_create_order_from_request(PDO $pdo, int $requestId, string $changedBy = 'system'): int
{
    $stmt = $pdo->prepare('SELECT * FROM client_requests WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $requestId]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$request) {
        throw new RuntimeException('Заявка не найдена.');
    }

    $clientId = crm_find_or_create_client(
        $pdo,
        (string) ($request['name'] ?? 'Клиент'),
        (string) ($request['phone'] ?? ''),
        (string) ($request['email'] ?? '')
    );

    $serviceTitle = trim((string) ($request['service_title'] ?? ''));
    $title = $serviceTitle !== '' ? $serviceTitle : 'Индивидуальный заказ';
    $serviceId = null;
    if ($serviceTitle !== '') {
        $serviceStmt = $pdo->prepare('SELECT id FROM services WHERE title = :title LIMIT 1');
        $serviceStmt->execute([':title' => $serviceTitle]);
        $foundServiceId = (int) ($serviceStmt->fetchColumn() ?: 0);
        $serviceId = $foundServiceId > 0 ? $foundServiceId : null;
    }

    $total = isset($request['estimated_total']) ? (float) $request['estimated_total'] : 0.0;
    $payload = (string) ($request['calculator_payload'] ?? '');
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO orders (client_id, request_id, service_id, title, status, total_amount, manager_comment, calculator_payload)
             VALUES (:client_id, :request_id, :service_id, :title, "calculation", :total_amount, :comment, :payload)'
        );
        $stmt->execute([
            ':client_id' => $clientId,
            ':request_id' => $requestId,
            ':service_id' => $serviceId,
            ':title' => $title,
            ':total_amount' => $total,
            ':comment' => (string) ($request['comment'] ?? ''),
            ':payload' => $payload !== '' ? $payload : null,
        ]);
        $orderId = (int) $pdo->lastInsertId();

        $stmt = $pdo->prepare('INSERT INTO order_items (order_id, service_id, title, quantity, unit_price, amount) VALUES (:order_id, :service_id, :title, 1, :amount, :amount)');
        $stmt->execute([
            ':order_id' => $orderId,
            ':service_id' => $serviceId,
            ':title' => $title,
            ':amount' => $total,
        ]);

        $stmt = $pdo->prepare('INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, comment) VALUES (:order_id, NULL, "calculation", :changed_by, "Заказ создан из заявки")');
        $stmt->execute([':order_id' => $orderId, ':changed_by' => $changedBy]);

        $stmt = $pdo->prepare('UPDATE client_requests SET request_status = "in_progress" WHERE id = :id');
        $stmt->execute([':id' => $requestId]);

        $stmt = $pdo->prepare('UPDATE request_files SET order_id = :order_id WHERE request_id = :request_id AND order_id IS NULL');
        $stmt->execute([':order_id' => $orderId, ':request_id' => $requestId]);

        $pdo->commit();
        return $orderId;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}

function crm_change_order_status(PDO $pdo, int $orderId, string $newStatus, string $changedBy, string $comment = ''): void
{
    $newStatus = crm_normalize_order_status($newStatus);
    $stmt = $pdo->prepare('SELECT status FROM orders WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $orderId]);
    $oldStatus = $stmt->fetchColumn();
    if (!is_string($oldStatus)) {
        throw new RuntimeException('Заказ не найден.');
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('UPDATE orders SET status = :status WHERE id = :id');
        $stmt->execute([':status' => $newStatus, ':id' => $orderId]);

        $stmt = $pdo->prepare('INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, comment) VALUES (:order_id, :old_status, :new_status, :changed_by, :comment)');
        $stmt->execute([
            ':order_id' => $orderId,
            ':old_status' => $oldStatus,
            ':new_status' => $newStatus,
            ':changed_by' => $changedBy,
            ':comment' => trim($comment),
        ]);
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}

function crm_store_uploaded_file(array $file, string $phone, ?int $requestId, ?int $orderId, string $uploadedBy = 'client'): array
{
    $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    $tmpPath = (string) ($file['tmp_name'] ?? '');
    $fileSize = (int) ($file['size'] ?? 0);
    $originalName = basename((string) ($file['name'] ?? 'file'));

    if ($errorCode !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Не удалось загрузить файл. Код ошибки: ' . $errorCode);
    }
    if ($fileSize <= 0 || $fileSize > 20 * 1024 * 1024) {
        throw new RuntimeException('Размер файла должен быть от 1 байта до 20 МБ.');
    }
    if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
        throw new RuntimeException('Некорректный загруженный файл.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = strtolower((string) $finfo->file($tmpPath));
    $allowed = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
        'application/zip' => 'zip',
        'application/x-zip-compressed' => 'zip',
    ];
    $extension = $allowed[$mime] ?? null;
    if ($extension === null) {
        throw new RuntimeException('Разрешены PDF, JPG, PNG, WEBP, SVG и ZIP.');
    }

    $uploadDir = __DIR__ . '/../media/uploads/briefs';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('Не удалось подготовить папку для загрузки.');
    }

    $prefix = $requestId ? 'request-' . $requestId : 'order-' . (int) $orderId;
    $fileName = $prefix . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(6)) . '.' . $extension;
    $targetPath = $uploadDir . '/' . $fileName;
    if (!move_uploaded_file($tmpPath, $targetPath)) {
        throw new RuntimeException('Не удалось сохранить файл.');
    }

    return [
        'original_name' => $originalName,
        'stored_path' => 'media/uploads/briefs/' . $fileName,
        'mime_type' => $mime,
        'file_size' => $fileSize,
        'client_phone' => crm_normalize_phone($phone),
        'request_id' => $requestId,
        'order_id' => $orderId,
        'uploaded_by' => $uploadedBy,
    ];
}

function crm_insert_file(PDO $pdo, array $storedFile): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO request_files (request_id, order_id, client_phone, original_name, stored_path, mime_type, file_size, uploaded_by)
         VALUES (:request_id, :order_id, :client_phone, :original_name, :stored_path, :mime_type, :file_size, :uploaded_by)'
    );
    $stmt->execute([
        ':request_id' => $storedFile['request_id'],
        ':order_id' => $storedFile['order_id'],
        ':client_phone' => $storedFile['client_phone'],
        ':original_name' => $storedFile['original_name'],
        ':stored_path' => $storedFile['stored_path'],
        ':mime_type' => $storedFile['mime_type'],
        ':file_size' => $storedFile['file_size'],
        ':uploaded_by' => $storedFile['uploaded_by'],
    ]);
    return (int) $pdo->lastInsertId();
}

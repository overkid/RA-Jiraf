<?php

declare(strict_types=1);

require_once __DIR__ . '/api/content.php';
require_once __DIR__ . '/api/crm.php';

$isHttps =
    (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
    || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Strict',
]);
ini_set('session.use_strict_mode', '1');
session_start();

$errors = [];
$messages = [];
$adminConfigPath = __DIR__ . '/config/admin.php';
$adminConfig = file_exists($adminConfigPath) ? require $adminConfigPath : null;
$currentLogin = (string) ($_SESSION['admin_login'] ?? '');
$currentRole = (string) ($_SESSION['admin_role'] ?? '');
$loggedIn = !empty($_SESSION['admin_logged_in']) && $currentLogin !== '' && in_array($currentRole, ['admin', 'manager'], true);

if (!$loggedIn) {
    unset($_SESSION['admin_logged_in'], $_SESSION['admin_login'], $_SESSION['admin_role']);
    $currentLogin = '';
    $currentRole = '';
}

$serviceCategories = [
    'Типография и полиграфия',
    'Сувенирная продукция',
    'Широкоформатная печать',
    'Наружная реклама',
];

const ADMIN_FORM_ACTIONS = [
    'login',
    'add_service',
    'update_service',
    'delete_service',
    'delete_request',
    'update_request_status',
    'add_request_note',
    'upload_site_image',
    'restore_site_image',
    'save_site_text',
    'add_calc_option',
    'update_calc_option',
    'delete_calc_option',
    'convert_request_order',
    'update_order_status',
    'upload_request_file',
];
const ADMIN_CSRF_TOKEN_KEY = 'admin_csrf_token';
const ADMIN_LOGIN_ATTEMPTS_KEY = 'admin_login_attempts';
const ADMIN_LOGIN_LOCK_UNTIL_KEY = 'admin_login_lock_until';
const ADMIN_SEEN_REQUEST_IDS_PREFIX = 'admin_seen_request_ids_';
const ADMIN_MAX_LOGIN_ATTEMPTS = 5;
const ADMIN_LOGIN_LOCK_SECONDS = 600;

const REQUEST_STATUS_LABELS = [
    'new' => 'Новая',
    'in_progress' => 'В работе',
    'closed' => 'Закрыта',
];

$services = [];
$requests = [];
$requestNotesByRequestId = [];
$requestFilesByRequestId = [];
$orderFilesByOrderId = [];
$orders = [];
$clients = [];
$calculationOptions = [];
$newRequestLookup = [];

$siteTextDefaults = default_home_content();
$siteImageDefaults = default_site_images();
$siteTextValues = $siteTextDefaults;
$siteImageValues = $siteImageDefaults;

$siteTextLabels = [
    'hero_title' => 'Главная: заголовок первого экрана',
    'hero_text' => 'Главная: описание первого экрана',
    'services_title' => 'Главная: заголовок блока услуг',
    'services_subtitle' => 'Главная: подзаголовок блока услуг',
    'highlights_title' => 'Главная: заголовок преимуществ',
    'highlight_1' => 'Преимущество 1',
    'highlight_2' => 'Преимущество 2',
    'highlight_3' => 'Преимущество 3',
    'portfolio_title' => 'Главная: заголовок портфолио',
    'portfolio_subtitle' => 'Главная: подзаголовок портфолио',
    'footer_title' => 'Футер: заголовок',
    'footer_text' => 'Футер: описание',
    'catalog_title' => 'Услуги: заголовок страницы',
    'catalog_subtitle' => 'Услуги: подзаголовок страницы',
    'catalog_help_title' => 'Услуги: заголовок блока помощи',
    'catalog_help_subtitle' => 'Услуги: подзаголовок блока помощи',
    'nav_home_label' => 'Навигация: Главная',
    'nav_services_label' => 'Навигация: Услуги',
    'nav_contact_button' => 'Навигация: кнопка связи',
    'contact_button_text' => 'Кнопка связи на странице',
    'manager_modal_title' => 'Модалка заявки: заголовок',
    'manager_modal_text' => 'Модалка заявки: описание',
    'manager_name_label' => 'Модалка заявки: поле Имя',
    'manager_name_placeholder' => 'Модалка заявки: placeholder имени',
    'manager_phone_label' => 'Модалка заявки: поле телефона',
    'manager_service_label' => 'Модалка заявки: поле услуги',
    'manager_service_placeholder' => 'Модалка заявки: placeholder услуги',
    'manager_service_other' => 'Модалка заявки: вариант Другое',
    'manager_comment_label' => 'Модалка заявки: поле комментария',
    'manager_submit_button' => 'Модалка заявки: кнопка отправки',
    'manager_success_text' => 'Модалка заявки: текст успешной отправки',
    'service_modal_title' => 'Модалка услуги: заголовок',
    'service_modal_fallback_text' => 'Модалка услуги: текст по умолчанию',
    'service_modal_note' => 'Модалка услуги: пояснение',
    'service_modal_contact_button' => 'Модалка услуги: кнопка связи',
    'footer_email' => 'Футер: e-mail',
    'footer_phone_1' => 'Футер: телефон 1',
    'footer_phone_2' => 'Футер: телефон 2',
    'footer_address_title' => 'Футер: заголовок адреса',
    'footer_address_line_1' => 'Футер: адрес, строка 1',
    'footer_address_line_2' => 'Футер: адрес, строка 2',
];

$siteImageLabels = [
    'services_card_1' => 'Главная: карточка услуги 1',
    'services_card_2' => 'Главная: карточка услуги 2',
    'services_card_3' => 'Главная: карточка услуги 3',
    'services_card_4' => 'Главная: карточка услуги 4',
    'portfolio_card_1' => 'Портфолио: карточка 1',
    'portfolio_card_2' => 'Портфолио: карточка 2',
    'portfolio_card_3' => 'Портфолио: карточка 3',
    'portfolio_card_4' => 'Портфолио: карточка 4',
    'portfolio_card_5' => 'Портфолио: карточка 5',
    'portfolio_card_6' => 'Портфолио: карточка 6',
    'portfolio_card_7' => 'Портфолио: карточка 7',
    'portfolio_card_8' => 'Портфолио: карточка 8',
];

$formatDateTime = static function (?string $value): string {
    if (!$value) {
        return '';
    }

    try {
        $date = new DateTime($value);
        return $date->format('d.m.Y H:i');
    } catch (Throwable $exception) {
        return $value;
    }
};

$escape = static function (?string $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$stringLength = static function (string $value): int {
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
};

$normalizeRequestStatus = static function (string $status): string {
    return array_key_exists($status, REQUEST_STATUS_LABELS) ? $status : 'new';
};

$resolveUsersFromConfig = static function ($config): array {
    if (!is_array($config)) {
        return [];
    }

    $users = [];

    if (isset($config['users']) && is_array($config['users'])) {
        foreach ($config['users'] as $login => $userData) {
            $normalizedLogin = trim((string) $login);
            if ($normalizedLogin === '' || !is_array($userData)) {
                continue;
            }

            $passwordHash = trim((string) ($userData['password_hash'] ?? ''));
            if ($passwordHash === '') {
                continue;
            }

            $role = (string) ($userData['role'] ?? 'manager');
            $users[$normalizedLogin] = [
                'password_hash' => $passwordHash,
                'role' => $role === 'admin' ? 'admin' : 'manager',
            ];
        }
    }

    if ($users) {
        return $users;
    }

    $legacyUser = trim((string) ($config['username'] ?? ''));
    $legacyHash = trim((string) ($config['password_hash'] ?? ''));
    if ($legacyUser !== '' && $legacyHash !== '') {
        $users[$legacyUser] = [
            'password_hash' => $legacyHash,
            'role' => 'admin',
        ];
    }

    return $users;
};

$ensureAdminSchema = static function (PDO $pdo): void {
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS site_content (
            content_key VARCHAR(120) PRIMARY KEY,
            content_value TEXT NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )'
    );

    try {
        $pdo->exec("ALTER TABLE client_requests ADD COLUMN request_status VARCHAR(20) NOT NULL DEFAULT 'new' AFTER comment");
    } catch (Throwable $exception) {
        // Column already exists.
    }

    try {
        $pdo->exec("UPDATE client_requests SET request_status = 'new' WHERE request_status IS NULL OR request_status = ''");
    } catch (Throwable $exception) {
        // Legacy schema update ignored.
    }

    crm_ensure_schema($pdo);

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS request_notes (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            request_id INT UNSIGNED NOT NULL,
            author_login VARCHAR(64) NOT NULL,
            author_role VARCHAR(20) NOT NULL,
            note_text TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_request_notes_request_id_created_at (request_id, created_at)
        )'
    );
};

if (empty($_SESSION[ADMIN_CSRF_TOKEN_KEY]) || !is_string($_SESSION[ADMIN_CSRF_TOKEN_KEY])) {
    $_SESSION[ADMIN_CSRF_TOKEN_KEY] = bin2hex(random_bytes(32));
}
$csrfToken = (string) $_SESSION[ADMIN_CSRF_TOKEN_KEY];

$refreshCsrfToken = static function () use (&$csrfToken): void {
    $_SESSION[ADMIN_CSRF_TOKEN_KEY] = bin2hex(random_bytes(32));
    $csrfToken = (string) $_SESSION[ADMIN_CSRF_TOKEN_KEY];
};

$isValidCsrfToken = static function (string $token) use (&$csrfToken): bool {
    return $token !== '' && hash_equals($csrfToken, $token);
};

$verifyPasswordHash = static function (string $password, string $storedHash): bool {
    $normalizedHash = trim($storedHash);
    if ($normalizedHash === '') {
        return false;
    }

    $passwordInfo = password_get_info($normalizedHash);
    if (!empty($passwordInfo['algo'])) {
        return password_verify($password, $normalizedHash);
    }

    if (str_starts_with($normalizedHash, 'pbkdf2_sha256$')) {
        $parts = explode('$', $normalizedHash);
        if (count($parts) !== 4) {
            return false;
        }

        [, $iterationsRaw, $saltBase64, $hashBase64] = $parts;
        $iterations = filter_var($iterationsRaw, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 100000, 'max_range' => 2000000],
        ]);

        $salt = base64_decode($saltBase64, true);
        $expectedHash = base64_decode($hashBase64, true);

        if ($iterations === false || !is_string($salt) || !is_string($expectedHash) || $salt === '' || $expectedHash === '') {
            return false;
        }

        $derivedHash = hash_pbkdf2('sha256', $password, $salt, (int) $iterations, strlen($expectedHash), true);
        return hash_equals($expectedHash, $derivedHash);
    }

    return false;
};

$authUsers = $resolveUsersFromConfig($adminConfig);

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet, noimageindex');
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $csrfTokenFromRequest = trim((string) ($_POST['csrf_token'] ?? ''));

    if (!$isValidCsrfToken($csrfTokenFromRequest)) {
        $errors[] = 'Сессия формы истекла. Обновите страницу и попробуйте снова.';
    } elseif (!in_array($action, ADMIN_FORM_ACTIONS, true)) {
        $errors[] = 'Неизвестное действие.';
    } elseif ($action === 'login') {
        $inputUser = trim((string) ($_POST['username'] ?? ''));
        $inputPass = trim((string) ($_POST['password'] ?? ''));
        $lockUntil = (int) ($_SESSION[ADMIN_LOGIN_LOCK_UNTIL_KEY] ?? 0);
        $now = time();

        if ($lockUntil > 0 && $lockUntil <= $now) {
            unset($_SESSION[ADMIN_LOGIN_LOCK_UNTIL_KEY], $_SESSION[ADMIN_LOGIN_ATTEMPTS_KEY]);
            $lockUntil = 0;
        }

        if (!$authUsers) {
            $errors[] = 'Файл config/admin.php не найден или в нем не заданы пользователи.';
        } elseif ($lockUntil > $now) {
            $minutesLeft = (int) ceil(($lockUntil - $now) / 60);
            $errors[] = 'Слишком много попыток входа. Повторите через ' . $minutesLeft . ' мин.';
        } elseif ($inputUser === '' || $inputPass === '') {
            $errors[] = 'Введите логин и пароль.';
        } else {
            $userData = $authUsers[$inputUser] ?? null;
            $validPassword = is_array($userData) && $verifyPasswordHash($inputPass, (string) ($userData['password_hash'] ?? ''));

            if ($validPassword) {
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_login'] = $inputUser;
                $_SESSION['admin_role'] = (string) ($userData['role'] ?? 'manager');
                unset($_SESSION[ADMIN_LOGIN_ATTEMPTS_KEY], $_SESSION[ADMIN_LOGIN_LOCK_UNTIL_KEY]);
                $refreshCsrfToken();
                $loggedIn = true;
                $currentLogin = $inputUser;
                $currentRole = (string) ($_SESSION['admin_role'] ?? 'manager');
                $messages[] = 'Вы вошли в админку.';
            } else {
                $attempts = (int) ($_SESSION[ADMIN_LOGIN_ATTEMPTS_KEY] ?? 0) + 1;

                if ($attempts >= ADMIN_MAX_LOGIN_ATTEMPTS) {
                    $_SESSION[ADMIN_LOGIN_ATTEMPTS_KEY] = 0;
                    $_SESSION[ADMIN_LOGIN_LOCK_UNTIL_KEY] = time() + ADMIN_LOGIN_LOCK_SECONDS;
                    $errors[] = 'Слишком много попыток входа. Доступ временно заблокирован на 10 минут.';
                } else {
                    $_SESSION[ADMIN_LOGIN_ATTEMPTS_KEY] = $attempts;
                    $errors[] = 'Неверный логин или пароль.';
                }
            }
        }
    } elseif (!$loggedIn) {
        $errors[] = 'Сначала войдите в админку.';
    } else {
        try {
            require_once __DIR__ . '/api/db.php';
            $pdo = db();
            $ensureAdminSchema($pdo);
        } catch (Throwable $exception) {
            $pdo = null;
            $errors[] = 'Не удалось подключиться к базе данных: ' . $exception->getMessage();
        }

        if ($pdo instanceof PDO) {
            $isAdmin = $currentRole === 'admin';
            $managerRequestActions = ['update_request_status', 'add_request_note', 'convert_request_order', 'update_order_status', 'upload_request_file'];
            $adminOnlyActions = ['add_service', 'update_service', 'delete_service', 'delete_request', 'upload_site_image', 'restore_site_image', 'save_site_text', 'add_calc_option', 'update_calc_option', 'delete_calc_option'];

            if (in_array($action, $adminOnlyActions, true) && !$isAdmin) {
                $errors[] = 'Недостаточно прав для этого действия.';
            } elseif (in_array($action, $managerRequestActions, true) && !in_array($currentRole, ['admin', 'manager'], true)) {
                $errors[] = 'Недостаточно прав для работы с заявками.';
            } else {
                if ($action === 'add_service' || $action === 'update_service') {
                    $category = trim((string) ($_POST['category'] ?? ''));
                    $title = trim((string) ($_POST['title'] ?? ''));
                    $description = trim((string) ($_POST['description'] ?? ''));
                    $basePrice = max(0, round((float) ($_POST['base_price'] ?? 0), 2));
                    $unitName = trim((string) ($_POST['unit_name'] ?? 'шт.'));
                    $calculatorEnabled = isset($_POST['calculator_enabled']) ? 1 : 0;
                    if ($unitName === '') { $unitName = 'шт.'; }

                    if ($category === '' || $title === '') {
                        $errors[] = 'Категория и название услуги обязательны.';
                    } elseif (!in_array($category, $serviceCategories, true)) {
                        $errors[] = 'Выберите категорию из списка.';
                    } else {
                        try {
                            if ($action === 'add_service') {
                                $stmt = $pdo->prepare('INSERT INTO services (category, title, description, base_price, unit_name, calculator_enabled) VALUES (:category, :title, :description, :base_price, :unit_name, :calculator_enabled)');
                                $stmt->execute([
                                    ':category' => $category,
                                    ':title' => $title,
                                    ':description' => $description,
                                    ':base_price' => $basePrice,
                                    ':unit_name' => $unitName,
                                    ':calculator_enabled' => $calculatorEnabled,
                                ]);
                                $messages[] = 'Услуга добавлена.';
                            } else {
                                $serviceId = (int) ($_POST['service_id'] ?? 0);
                                if ($serviceId <= 0) {
                                    $errors[] = 'Не удалось определить услугу для обновления.';
                                } else {
                                    $stmt = $pdo->prepare('UPDATE services SET category = :category, title = :title, description = :description, base_price = :base_price, unit_name = :unit_name, calculator_enabled = :calculator_enabled WHERE id = :id');
                                    $stmt->execute([
                                        ':category' => $category,
                                        ':title' => $title,
                                        ':description' => $description,
                                        ':base_price' => $basePrice,
                                        ':unit_name' => $unitName,
                                        ':calculator_enabled' => $calculatorEnabled,
                                        ':id' => $serviceId,
                                    ]);
                                    $messages[] = 'Услуга обновлена.';
                                }
                            }
                        } catch (Throwable $exception) {
                            $errors[] = 'Ошибка сохранения услуги: ' . $exception->getMessage();
                        }
                    }
                }

                if ($action === 'delete_service') {
                    $serviceId = (int) ($_POST['service_id'] ?? 0);
                    if ($serviceId <= 0) {
                        $errors[] = 'Не удалось определить услугу для удаления.';
                    } else {
                        try {
                            $stmt = $pdo->prepare('DELETE FROM services WHERE id = :id');
                            $stmt->execute([':id' => $serviceId]);
                            $messages[] = 'Услуга удалена.';
                        } catch (Throwable $exception) {
                            $errors[] = 'Не удалось удалить услугу: ' . $exception->getMessage();
                        }
                    }
                }

                if ($action === 'update_request_status') {
                    $requestId = (int) ($_POST['request_id'] ?? 0);
                    $requestStatus = $normalizeRequestStatus(trim((string) ($_POST['request_status'] ?? 'new')));

                    if ($requestId <= 0) {
                        $errors[] = 'Не удалось определить заявку.';
                    } else {
                        try {
                            $stmt = $pdo->prepare('UPDATE client_requests SET request_status = :request_status WHERE id = :id');
                            $stmt->execute([
                                ':request_status' => $requestStatus,
                                ':id' => $requestId,
                            ]);
                            $messages[] = 'Статус заявки обновлен.';
                        } catch (Throwable $exception) {
                            $errors[] = 'Не удалось обновить статус заявки: ' . $exception->getMessage();
                        }
                    }
                }

                if ($action === 'add_request_note') {
                    $requestId = (int) ($_POST['request_id'] ?? 0);
                    $noteText = trim((string) ($_POST['note_text'] ?? ''));

                    if ($requestId <= 0) {
                        $errors[] = 'Не удалось определить заявку.';
                    } elseif ($noteText === '') {
                        $errors[] = 'Текст заметки не должен быть пустым.';
                    } elseif ($stringLength($noteText) > 3000) {
                        $errors[] = 'Заметка слишком длинная.';
                    } else {
                        try {
                            $stmt = $pdo->prepare('INSERT INTO request_notes (request_id, author_login, author_role, note_text) VALUES (:request_id, :author_login, :author_role, :note_text)');
                            $stmt->execute([
                                ':request_id' => $requestId,
                                ':author_login' => $currentLogin,
                                ':author_role' => $currentRole,
                                ':note_text' => $noteText,
                            ]);
                            $messages[] = 'Заметка добавлена.';
                        } catch (Throwable $exception) {
                            $errors[] = 'Не удалось сохранить заметку: ' . $exception->getMessage();
                        }
                    }
                }

                if ($action === 'delete_request') {
                    $requestId = (int) ($_POST['request_id'] ?? 0);
                    if ($requestId <= 0) {
                        $errors[] = 'Не удалось определить заявку для удаления.';
                    } else {
                        try {
                            $pdo->beginTransaction();
                            $stmtNotes = $pdo->prepare('DELETE FROM request_notes WHERE request_id = :id');
                            $stmtNotes->execute([':id' => $requestId]);

                            $stmt = $pdo->prepare('DELETE FROM client_requests WHERE id = :id');
                            $stmt->execute([':id' => $requestId]);

                            if ($stmt->rowCount() > 0) {
                                $pdo->commit();
                                $messages[] = 'Заявка удалена.';
                            } else {
                                $pdo->rollBack();
                                $errors[] = 'Заявка не найдена или уже удалена.';
                            }

                            $seenKey = ADMIN_SEEN_REQUEST_IDS_PREFIX . $currentLogin;
                            $seenRequestIds = array_map('intval', (array) ($_SESSION[$seenKey] ?? []));
                            $_SESSION[$seenKey] = array_values(array_filter($seenRequestIds, static fn (int $seenId): bool => $seenId !== $requestId));
                        } catch (Throwable $exception) {
                            if ($pdo->inTransaction()) {
                                $pdo->rollBack();
                            }
                            $errors[] = 'Не удалось удалить заявку: ' . $exception->getMessage();
                        }
                    }
                }


                if ($action === 'convert_request_order') {
                    $requestId = (int) ($_POST['request_id'] ?? 0);
                    if ($requestId <= 0) {
                        $errors[] = 'Не удалось определить заявку для создания заказа.';
                    } else {
                        try {
                            $orderId = crm_create_order_from_request($pdo, $requestId, $currentLogin);
                            $messages[] = 'Создан заказ №' . $orderId . ' из заявки.';
                        } catch (Throwable $exception) {
                            $errors[] = 'Не удалось создать заказ: ' . $exception->getMessage();
                        }
                    }
                }

                if ($action === 'update_order_status') {
                    $orderId = (int) ($_POST['order_id'] ?? 0);
                    $orderStatus = crm_normalize_order_status(trim((string) ($_POST['order_status'] ?? 'new')));
                    $comment = trim((string) ($_POST['status_comment'] ?? ''));
                    if ($orderId <= 0) {
                        $errors[] = 'Не удалось определить заказ.';
                    } else {
                        try {
                            crm_change_order_status($pdo, $orderId, $orderStatus, $currentLogin, $comment);
                            $messages[] = 'Статус заказа обновлён.';
                        } catch (Throwable $exception) {
                            $errors[] = 'Не удалось обновить заказ: ' . $exception->getMessage();
                        }
                    }
                }

                if ($action === 'upload_request_file') {
                    $requestId = (int) ($_POST['request_id'] ?? 0);
                    $orderId = (int) ($_POST['order_id'] ?? 0);
                    $phone = trim((string) ($_POST['phone'] ?? ''));
                    if ($phone === '' || (!isset($_FILES['layout_file']) || !is_array($_FILES['layout_file']))) {
                        $errors[] = 'Укажите телефон и выберите файл.';
                    } else {
                        try {
                            $stored = crm_store_uploaded_file($_FILES['layout_file'], $phone, $requestId > 0 ? $requestId : null, $orderId > 0 ? $orderId : null, 'manager');
                            crm_insert_file($pdo, $stored);
                            $messages[] = 'Файл макета прикреплён.';
                        } catch (Throwable $exception) {
                            $errors[] = 'Не удалось загрузить файл: ' . $exception->getMessage();
                        }
                    }
                }

                if ($action === 'add_calc_option' || $action === 'update_calc_option') {
                    $serviceId = (int) ($_POST['service_id'] ?? 0);
                    $optionId = (int) ($_POST['option_id'] ?? 0);
                    $optionType = trim((string) ($_POST['option_type'] ?? 'Материал'));
                    $title = trim((string) ($_POST['option_title'] ?? ''));
                    $priceDelta = round((float) ($_POST['price_delta'] ?? 0), 2);
                    $multiplier = max(0.01, round((float) ($_POST['multiplier'] ?? 1), 3));
                    $sortOrder = (int) ($_POST['sort_order'] ?? 100);
                    $isActive = isset($_POST['is_active']) ? 1 : 0;

                    if ($serviceId <= 0 || $title === '') {
                        $errors[] = 'Для параметра калькулятора нужны услуга и название.';
                    } else {
                        try {
                            if ($action === 'add_calc_option') {
                                $stmt = $pdo->prepare('INSERT INTO calculation_options (service_id, option_type, title, price_delta, multiplier, is_active, sort_order) VALUES (:service_id, :option_type, :title, :price_delta, :multiplier, :is_active, :sort_order)');
                                $messages[] = 'Параметр калькулятора добавлен.';
                            } else {
                                $stmt = $pdo->prepare('UPDATE calculation_options SET service_id = :service_id, option_type = :option_type, title = :title, price_delta = :price_delta, multiplier = :multiplier, is_active = :is_active, sort_order = :sort_order WHERE id = :option_id');
                            }
                            $params = [
                                ':service_id' => $serviceId,
                                ':option_type' => $optionType,
                                ':title' => $title,
                                ':price_delta' => $priceDelta,
                                ':multiplier' => $multiplier,
                                ':is_active' => $isActive,
                                ':sort_order' => $sortOrder,
                            ];
                            if ($action === 'update_calc_option') {
                                if ($optionId <= 0) { throw new RuntimeException('Параметр не найден.'); }
                                $params[':option_id'] = $optionId;
                                $messages[] = 'Параметр калькулятора обновлён.';
                            }
                            $stmt->execute($params);
                        } catch (Throwable $exception) {
                            $errors[] = 'Не удалось сохранить параметр калькулятора: ' . $exception->getMessage();
                        }
                    }
                }

                if ($action === 'delete_calc_option') {
                    $optionId = (int) ($_POST['option_id'] ?? 0);
                    if ($optionId <= 0) {
                        $errors[] = 'Не удалось определить параметр калькулятора.';
                    } else {
                        try {
                            $stmt = $pdo->prepare('DELETE FROM calculation_options WHERE id = :id');
                            $stmt->execute([':id' => $optionId]);
                            $messages[] = 'Параметр калькулятора удалён.';
                        } catch (Throwable $exception) {
                            $errors[] = 'Не удалось удалить параметр: ' . $exception->getMessage();
                        }
                    }
                }

                if ($action === 'upload_site_image') {
                    $imageKey = trim((string) ($_POST['image_key'] ?? ''));
                    if (!array_key_exists($imageKey, $siteImageDefaults)) {
                        $errors[] = 'Неизвестный слот изображения.';
                    } elseif (!isset($_FILES['site_image']) || !is_array($_FILES['site_image'])) {
                        $errors[] = 'Файл не передан.';
                    } else {
                        $file = $_FILES['site_image'];
                        $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
                        $tmpPath = (string) ($file['tmp_name'] ?? '');
                        $fileSize = (int) ($file['size'] ?? 0);
                        $allowedMimeToExtension = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

                        if ($errorCode !== UPLOAD_ERR_OK) {
                            $errors[] = 'Не удалось загрузить файл. Код ошибки: ' . $errorCode;
                        } elseif ($fileSize <= 0 || $fileSize > 8 * 1024 * 1024) {
                            $errors[] = 'Размер файла должен быть от 1 байта до 8 МБ.';
                        } elseif ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
                            $errors[] = 'Некорректный загруженный файл.';
                        } else {
                            $finfo = new finfo(FILEINFO_MIME_TYPE);
                            $mime = strtolower((string) $finfo->file($tmpPath));
                            $extension = $allowedMimeToExtension[$mime] ?? null;

                            if ($extension === null) {
                                $errors[] = 'Разрешены только JPG, PNG и WEBP.';
                            } else {
                                $uploadDir = __DIR__ . '/media/uploads';
                                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                                    $errors[] = 'Не удалось подготовить папку для загрузки.';
                                } else {
                                    $safeKey = preg_replace('/[^a-z0-9_\-]/i', '-', $imageKey) ?: 'image';
                                    $fileName = $safeKey . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(6)) . '.' . $extension;
                                    $targetPath = $uploadDir . '/' . $fileName;
                                    $relativePath = 'media/uploads/' . $fileName;

                                    if (!move_uploaded_file($tmpPath, $targetPath)) {
                                        $errors[] = 'Не удалось сохранить загруженный файл.';
                                    } else {
                                        save_site_images($pdo, [$imageKey => $relativePath]);
                                        $messages[] = 'Изображение обновлено.';
                                    }
                                }
                            }
                        }
                    }
                }

                if ($action === 'restore_site_image') {
                    $imageKey = trim((string) ($_POST['image_key'] ?? ''));
                    if (!array_key_exists($imageKey, $siteImageDefaults)) {
                        $errors[] = 'Неизвестный слот изображения.';
                    } else {
                        try {
                            save_site_images($pdo, [$imageKey => (string) $siteImageDefaults[$imageKey]]);
                            $messages[] = 'Изображение восстановлено по умолчанию.';
                        } catch (Throwable $exception) {
                            $errors[] = 'Не удалось восстановить изображение: ' . $exception->getMessage();
                        }
                    }
                }

                if ($action === 'save_site_text') {
                    $incoming = $_POST['site_text'] ?? [];
                    $payload = [];
                    foreach ($siteTextDefaults as $key => $defaultValue) {
                        if (is_array($incoming) && array_key_exists($key, $incoming)) {
                            $payload[$key] = trim((string) $incoming[$key]);
                        }
                    }

                    if ($payload) {
                        save_home_content($pdo, $payload);
                        $messages[] = 'Тексты сайта сохранены.';
                    } else {
                        $errors[] = 'Нет данных для сохранения.';
                    }
                }
            }
        }
    }
}

if ($loggedIn) {
    try {
        require_once __DIR__ . '/api/db.php';
        $pdo = db();
        $ensureAdminSchema($pdo);

        if ($currentRole === 'admin') {
            $services = $pdo->query('SELECT id, category, title, description, base_price, unit_name, calculator_enabled FROM services ORDER BY category, id')->fetchAll(PDO::FETCH_ASSOC);
            $siteTextValues = get_home_content($pdo);
            $siteImageValues = get_site_images($pdo);
        }

        try {
            $requests = $pdo->query(
                "SELECT id, name, phone, email, comment, calculator_payload, estimated_total, created_at, service_title, service_is_other, request_status
                 FROM client_requests
                 ORDER BY
                   CASE request_status
                     WHEN 'new' THEN 0
                     WHEN 'in_progress' THEN 1
                     WHEN 'closed' THEN 2
                     ELSE 3
                   END ASC,
                   created_at DESC"
            )->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $exception) {
            $requests = $pdo->query(
                "SELECT id, name, phone, comment, created_at, service_title, service_is_other, 'new' AS request_status
                 FROM client_requests
                 ORDER BY created_at DESC"
            )->fetchAll(PDO::FETCH_ASSOC);
        }

        try {
            $notes = $pdo->query('SELECT id, request_id, author_login, author_role, note_text, created_at FROM request_notes ORDER BY request_id ASC, created_at ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $exception) {
            $notes = [];
        }



        $clients = $pdo->query('SELECT * FROM clients ORDER BY updated_at DESC, created_at DESC LIMIT 80')->fetchAll(PDO::FETCH_ASSOC);
        $orders = $pdo->query(
            "SELECT o.*, c.name AS client_name, c.phone AS client_phone
             FROM orders o
             INNER JOIN clients c ON c.id = o.client_id
             ORDER BY
               CASE o.status
                 WHEN 'review' THEN 0
                 WHEN 'in_process' THEN 1
                 WHEN 'ready' THEN 2
                 ELSE 3
               END ASC,
               o.updated_at DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
        $calculationOptions = $pdo->query('SELECT co.*, s.title AS service_title FROM calculation_options co INNER JOIN services s ON s.id = co.service_id ORDER BY s.category, s.id, co.option_type, co.sort_order, co.id')->fetchAll(PDO::FETCH_ASSOC);
        $files = $pdo->query('SELECT * FROM request_files ORDER BY created_at DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);
        $requestFilesByRequestId = [];
        $orderFilesByOrderId = [];
        foreach ($files as $file) {
            $fileRequestId = (int) ($file['request_id'] ?? 0);
            $fileOrderId = (int) ($file['order_id'] ?? 0);
            if ($fileRequestId > 0) { $requestFilesByRequestId[$fileRequestId][] = $file; }
            if ($fileOrderId > 0) { $orderFilesByOrderId[$fileOrderId][] = $file; }
        }

        $requestNotesByRequestId = [];
        foreach ($notes as $note) {
            $requestId = (int) ($note['request_id'] ?? 0);
            if ($requestId > 0) {
                $requestNotesByRequestId[$requestId][] = $note;
            }
        }

        $seenKey = ADMIN_SEEN_REQUEST_IDS_PREFIX . $currentLogin;
        $seenRequestIds = array_values(array_unique(array_map('intval', (array) ($_SESSION[$seenKey] ?? []))));
        $requestIds = array_values(array_filter(array_map(static fn (array $request): int => (int) ($request['id'] ?? 0), $requests), static fn (int $id): bool => $id > 0));

        $newRequestIds = array_values(array_diff($requestIds, $seenRequestIds));
        $newRequestLookup = array_fill_keys($newRequestIds, true);
        $_SESSION[$seenKey] = array_values(array_unique(array_merge($seenRequestIds, $requestIds)));
    } catch (Throwable $exception) {
        $errors[] = 'Не удалось загрузить данные: ' . $exception->getMessage();
    }
}
?>
<!doctype html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex" />
    <meta name="googlebot" content="noindex, nofollow, noarchive, nosnippet, noimageindex" />
    <title>РА «Жираф» — Админка</title>
    <link rel="icon" href="media/favicon.ico" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&family=Nunito:wght@700;800&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="styles.css" />
    <script src="animations.js" defer></script>
  </head>
  <body class="admin-page">
    <header class="admin-header">
      <div class="container">
        <nav class="top-nav top-nav-catalog">
          <a class="logo" href="index.php"><img src="media/logo/Logo-Full.svg" alt="РА Жираф" /></a>
          <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" data-nav-toggle>
            <svg class="nav-toggle-icon nav-toggle-icon--bars" aria-hidden="true"><use href="media/icons/sprite.svg#menu-bars"></use></svg>
            <svg class="nav-toggle-icon nav-toggle-icon--close" aria-hidden="true"><use href="media/icons/sprite.svg#menu-x"></use></svg>
            <span class="sr-only">Меню</span>
          </button>
          <div class="nav-panel" id="primary-nav" data-nav-panel aria-hidden="true">
            <ul class="menu">
              <li>
                <a href="index.php">
                  <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#home"></use></svg>
                  Главная
                </a>
              </li>
              <li>
                <a href="services.php">
                  <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#catalog"></use></svg>
                  Услуги
                </a>
              </li>
              <li>
                <a href="admin.php">
                  <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#case"></use></svg>
                  Админка
                </a>
              </li>
            </ul>
            <div class="nav-actions">
              <a class="nav-vk" href="https://vk.com/giraf33" target="_blank" rel="noopener noreferrer" aria-label="ВКонтакте">
                <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#vk"></use></svg>
              </a>
              <?php if ($loggedIn): ?>
                <span class="admin-role-chip"><?= $escape(strtoupper($currentRole)) ?></span>
                <a class="btn btn-nav" href="admin.php?logout=1">Выйти</a>
              <?php endif; ?>
            </div>
          </div>
        </nav>

        <div class="catalog-hero">
          <h1>Админ-панель</h1>
          <?php if ($loggedIn && $currentRole === 'admin'): ?>
            <p class="section-subtitle">Заявки, каталог услуг, тексты и фотографии сайта</p>
          <?php elseif ($loggedIn): ?>
            <p class="section-subtitle">Работа с заявками клиентов</p>
          <?php else: ?>
            <p class="section-subtitle">Вход для администратора и менеджера</p>
          <?php endif; ?>
        </div>
      </div>
    </header>

    <?php if (!$loggedIn): ?>
      <main>
        <section class="section admin-panel">
          <div class="container">
            <div class="admin-card admin-card--center">
              <h2>Вход в админку</h2>
              <p class="section-subtitle">Введите логин и пароль пользователя с доступом</p>

              <?php if ($errors): ?>
                <div class="admin-alert admin-alert--error">
                  <?php foreach ($errors as $message): ?>
                    <p><?= $escape($message) ?></p>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <form class="admin-form" method="post">
                <input type="hidden" name="action" value="login" />
                <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>" />
                <label for="admin-username">Логин</label>
                <input id="admin-username" type="text" name="username" autocomplete="username" required />

                <label for="admin-password">Пароль</label>
                <input id="admin-password" type="password" name="password" autocomplete="current-password" required />

                <button class="btn btn-nav" type="submit">
                  <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#message"></use></svg>Войти
                </button>
              </form>
            </div>
          </div>
        </section>
      </main>
    <?php else: ?>
      <main>
        <section class="section admin-panel">
          <div class="container">
            <?php if ($errors): ?>
              <div class="admin-alert admin-alert--error">
                <?php foreach ($errors as $message): ?>
                  <p><?= $escape($message) ?></p>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <?php if ($messages): ?>
              <div class="admin-alert admin-alert--success">
                <?php foreach ($messages as $message): ?>
                  <p><?= $escape($message) ?></p>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <div class="admin-section-title">
              <h2 id="requests">Заявки пользователей</h2>
            </div>

            <?php if (!$requests): ?>
              <div class="admin-card">
                <p class="section-subtitle">Заявок пока нет.</p>
              </div>
            <?php else: ?>
              <div class="admin-list">
                <?php foreach ($requests as $request): ?>
                  <?php
                  $requestId = (int) ($request['id'] ?? 0);
                  $isNewRequest = isset($newRequestLookup[$requestId]);
                  $requestStatus = $normalizeRequestStatus((string) ($request['request_status'] ?? 'new'));
                  $requestServiceTitle = trim((string) ($request['service_title'] ?? ''));
                  $requestServiceIsOther = (bool) ($request['service_is_other'] ?? false);
                  $requestServiceValue = $requestServiceIsOther ? 'Другое' : $requestServiceTitle;
                  $requestNotes = $requestNotesByRequestId[$requestId] ?? [];
                  $requestNotesCount = count($requestNotes);
                  $visibleRequestNotes = $requestNotesCount > 2 ? array_slice($requestNotes, 0, 2) : $requestNotes;
                  $hiddenRequestNotes = $requestNotesCount > 2 ? array_slice($requestNotes, 2) : [];
                  ?>
                  <div class="admin-item">
                    <div class="admin-request-head">
                      <h3><?= $escape((string) ($request['name'] ?? 'Без имени')) ?></h3>
                      <div class="admin-request-badges">
                        <?php if ($isNewRequest): ?>
                          <span class="admin-badge-new">Новое</span>
                        <?php endif; ?>
                        <span class="admin-status-badge admin-status-badge--<?= $escape($requestStatus) ?>">
                          <?= $escape((string) (REQUEST_STATUS_LABELS[$requestStatus] ?? REQUEST_STATUS_LABELS['new'])) ?>
                        </span>
                      </div>
                    </div>
                    <p><span>ID:</span> <?= $escape((string) $requestId) ?></p>
                    <p><span>Телефон:</span> <?= $escape((string) ($request['phone'] ?? '')) ?></p>
                    <?php if ($requestServiceValue !== ''): ?>
                      <p><span>Услуга:</span> <?= $escape($requestServiceValue) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($request['comment'])): ?>
                      <p><span>Комментарий:</span> <?= $escape((string) ($request['comment'] ?? '')) ?></p>
                    <?php endif; ?>
                    <p><span>Дата:</span> <?= $escape($formatDateTime((string) ($request['created_at'] ?? ''))) ?></p>
                    <?php if (!empty($request['estimated_total'])): ?>
                      <p><span>Оценка калькулятора:</span> <?= $escape(number_format((float) $request['estimated_total'], 0, ',', ' ')) ?> ₽</p>
                    <?php endif; ?>

                    <?php $requestFiles = $requestFilesByRequestId[$requestId] ?? []; ?>
                    <div class="admin-files">
                      <h4>Макеты и файлы</h4>
                      <?php if (!$requestFiles): ?>
                        <p class="admin-note-empty">Файлов пока нет.</p>
                      <?php else: ?>
                        <?php foreach ($requestFiles as $file): ?>
                          <a class="admin-file-link" href="<?= $escape((string) $file['stored_path']) ?>" target="_blank" rel="noopener noreferrer"><?= $escape((string) $file['original_name']) ?></a>
                        <?php endforeach; ?>
                      <?php endif; ?>
                      <form class="admin-form admin-request-note-form" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload_request_file" />
                        <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>" />
                        <input type="hidden" name="request_id" value="<?= $escape((string) $requestId) ?>" />
                        <input type="hidden" name="phone" value="<?= $escape((string) ($request['phone'] ?? '')) ?>" />
                        <label for="request-file-<?= $escape((string) $requestId) ?>">Прикрепить макет к заявке</label>
                        <input id="request-file-<?= $escape((string) $requestId) ?>" type="file" name="layout_file" accept=".pdf,.jpg,.jpeg,.png,.webp,.svg,.zip" required />
                        <button class="btn btn-nav" type="submit">Загрузить файл</button>
                      </form>
                    </div>

                    <form class="admin-form admin-request-status-form" method="post">
                      <input type="hidden" name="action" value="convert_request_order" />
                      <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>" />
                      <input type="hidden" name="request_id" value="<?= $escape((string) $requestId) ?>" />
                      <button class="btn btn-contact" type="submit">Создать заказ из заявки</button>
                    </form>

                    <form class="admin-form admin-request-status-form" method="post">
                      <input type="hidden" name="action" value="update_request_status" />
                      <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>" />
                      <input type="hidden" name="request_id" value="<?= $escape((string) $requestId) ?>" />
                      <label for="request-status-<?= $escape((string) $requestId) ?>">Статус заявки</label>
                      <div class="admin-inline-fields">
                        <select id="request-status-<?= $escape((string) $requestId) ?>" name="request_status" required>
                          <?php foreach (REQUEST_STATUS_LABELS as $statusKey => $statusLabel): ?>
                            <option value="<?= $escape((string) $statusKey) ?>" <?= $statusKey === $requestStatus ? 'selected' : '' ?>>
                              <?= $escape($statusLabel) ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                        <button class="btn btn-nav" type="submit">Сохранить</button>
                      </div>
                    </form>

                    <div class="admin-notes">
                      <h4>Заметки</h4>
                      <?php if (!$requestNotes): ?>
                        <p class="admin-note-empty">Пока нет заметок.</p>
                      <?php else: ?>
                        <?php foreach ($visibleRequestNotes as $note): ?>
                          <article class="admin-note-item">
                            <p class="admin-note-meta">
                              <?= $escape((string) ($note['author_login'] ?? '')) ?>
                              (<?= $escape((string) ($note['author_role'] ?? '')) ?>)
                              • <?= $escape($formatDateTime((string) ($note['created_at'] ?? ''))) ?>
                            </p>
                            <p><?= nl2br($escape((string) ($note['note_text'] ?? ''))) ?></p>
                          </article>
                        <?php endforeach; ?>
                        <?php if ($hiddenRequestNotes): ?>
                          <details class="admin-notes-more">
                            <summary>
                              <span class="admin-notes-more-label admin-notes-more-label--more">Показать еще (<?= $escape((string) count($hiddenRequestNotes)) ?>)</span>
                              <span class="admin-notes-more-label admin-notes-more-label--less">Скрыть</span>
                            </summary>
                            <div class="admin-notes-more-list">
                              <?php foreach ($hiddenRequestNotes as $note): ?>
                                <article class="admin-note-item">
                                  <p class="admin-note-meta">
                                    <?= $escape((string) ($note['author_login'] ?? '')) ?>
                                    (<?= $escape((string) ($note['author_role'] ?? '')) ?>)
                                    • <?= $escape($formatDateTime((string) ($note['created_at'] ?? ''))) ?>
                                  </p>
                                  <p><?= nl2br($escape((string) ($note['note_text'] ?? ''))) ?></p>
                                </article>
                              <?php endforeach; ?>
                            </div>
                          </details>
                        <?php endif; ?>
                      <?php endif; ?>
                    </div>

                    <details class="admin-note-editor">
                      <summary>Добавить заметку</summary>
                      <form class="admin-form admin-request-note-form" method="post">
                        <input type="hidden" name="action" value="add_request_note" />
                        <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>" />
                        <input type="hidden" name="request_id" value="<?= $escape((string) $requestId) ?>" />
                        <label for="request-note-<?= $escape((string) $requestId) ?>">Текст заметки</label>
                        <textarea id="request-note-<?= $escape((string) $requestId) ?>" name="note_text" rows="3" maxlength="3000" required></textarea>
                        <button class="btn btn-nav" type="submit">Сохранить</button>
                      </form>
                    </details>

                    <?php if ($currentRole === 'admin'): ?>
                      <form class="admin-form admin-request-delete-form" method="post">
                        <input type="hidden" name="action" value="delete_request" />
                        <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>" />
                        <input type="hidden" name="request_id" value="<?= $escape((string) $requestId) ?>" />
                        <button class="btn btn-danger" type="submit" onclick="return confirm('Удалить заявку?')">Удалить заявку</button>
                      </form>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </section>


        <section class="section admin-panel admin-panel-alt">
          <div class="container">
            <div class="admin-section-title">
              <h2 id="orders-admin">Заказы и клиенты</h2>
            </div>
            <div class="crm-dashboard">
              <article class="crm-metric"><span><?= $escape((string) count($clients)) ?></span><p>Клиентов</p></article>
              <article class="crm-metric"><span><?= $escape((string) count($orders)) ?></span><p>Заказов</p></article>
              <article class="crm-metric"><span><?= $escape(number_format(array_sum(array_map(static fn (array $order): float => (float) ($order['total_amount'] ?? 0), $orders)), 0, ',', ' ')) ?> ₽</span><p>Сумма заказов</p></article>
            </div>

            <?php if (!$orders): ?>
              <div class="admin-card"><p class="section-subtitle">Заказов пока нет. Создайте заказ из заявки.</p></div>
            <?php else: ?>
              <div class="admin-kanban">
                <?php foreach (ORDER_STATUS_LABELS as $statusKey => $statusLabel): ?>
                  <div class="admin-kanban-column">
                    <h3><?= $escape($statusLabel) ?></h3>
                    <?php foreach ($orders as $order): ?>
                      <?php $orderStatus = crm_normalize_order_status((string) ($order['status'] ?? 'new')); ?>
                      <?php if ($orderStatus !== $statusKey) { continue; } ?>
                      <?php $orderId = (int) ($order['id'] ?? 0); $orderFiles = $orderFilesByOrderId[$orderId] ?? []; ?>
                      <article class="admin-item admin-order-card">
                        <div class="admin-request-head">
                          <h3>№<?= $escape((string) $orderId) ?> · <?= $escape((string) ($order['title'] ?? 'Заказ')) ?></h3>
                          <span class="admin-status-badge admin-status-badge--<?= $escape($orderStatus) ?>"><?= $escape($statusLabel) ?></span>
                        </div>
                        <p><span>Клиент:</span> <?= $escape((string) ($order['client_name'] ?? '')) ?></p>
                        <p><span>Телефон:</span> <?= $escape((string) ($order['client_phone'] ?? '')) ?></p>
                        <p><span>Сумма:</span> <?= $escape(number_format((float) ($order['total_amount'] ?? 0), 0, ',', ' ')) ?> ₽</p>
                        <?php
                          $orderPayload = json_decode((string) ($order['calculator_payload'] ?? ''), true);
                          $orderOptions = is_array($orderPayload) ? (array) ($orderPayload['selected_options'] ?? []) : [];
                        ?>
                        <?php if (is_array($orderPayload)): ?>
                          <p><span>Количество:</span> <?= $escape((string) ($orderPayload['quantity'] ?? '1')) ?></p>
                          <?php if (!empty($orderPayload['area'])): ?><p><span>Площадь:</span> <?= $escape((string) $orderPayload['area']) ?> м²</p><?php endif; ?>
                          <?php if ($orderOptions): ?>
                            <p><span>Параметры:</span>
                              <?= $escape(implode(', ', array_map(static fn ($option): string => (string) ($option['title'] ?? ''), $orderOptions))) ?>
                            </p>
                          <?php endif; ?>
                        <?php endif; ?>
                        <?php if (!empty($order['manager_comment'])): ?>
                          <p><span>Комментарий:</span> <?= nl2br($escape((string) $order['manager_comment'])) ?></p>
                        <?php endif; ?>
                        <?php if ($orderFiles): ?>
                          <div class="admin-files admin-files--compact">
                            <?php foreach ($orderFiles as $file): ?>
                              <a class="admin-file-link" href="<?= $escape((string) $file['stored_path']) ?>" target="_blank" rel="noopener noreferrer"><?= $escape((string) $file['original_name']) ?></a>
                            <?php endforeach; ?>
                          </div>
                        <?php endif; ?>
                        <form class="admin-form admin-request-status-form" method="post">
                          <input type="hidden" name="action" value="update_order_status" />
                          <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>" />
                          <input type="hidden" name="order_id" value="<?= $escape((string) $orderId) ?>" />
                          <label for="order-status-<?= $escape((string) $orderId) ?>">Новый статус</label>
                          <select id="order-status-<?= $escape((string) $orderId) ?>" name="order_status">
                            <?php foreach (ORDER_STATUS_LABELS as $nextKey => $nextLabel): ?>
                              <option value="<?= $escape($nextKey) ?>" <?= $nextKey === $orderStatus ? 'selected' : '' ?>><?= $escape($nextLabel) ?></option>
                            <?php endforeach; ?>
                          </select>
                          <label for="order-comment-<?= $escape((string) $orderId) ?>">Комментарий к смене статуса</label>
                          <input id="order-comment-<?= $escape((string) $orderId) ?>" type="text" name="status_comment" placeholder="Например: макет согласован" />
                          <button class="btn btn-nav" type="submit">Обновить заказ</button>
                        </form>
                      </article>
                    <?php endforeach; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </section>

        <?php if ($currentRole === 'admin'): ?>
          <section class="section admin-panel admin-panel-alt">
            <div class="container">
              <div class="admin-collapsible" data-admin-section data-section-id="services">
                <div class="admin-section-title">
                  <h2 id="services-admin">Каталог услуг</h2>
                  <button class="btn btn-collapse-toggle" type="button" data-admin-section-toggle aria-expanded="true">Свернуть</button>
                </div>
                <div class="admin-collapsible-body" data-admin-section-body>
                  <div class="admin-card admin-card-add-service">
                    <h3>Добавить услугу</h3>
                    <form class="admin-form" method="post">
                      <input type="hidden" name="action" value="add_service" />
                      <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>" />
                      <label for="service-category-new">Категория</label>
                      <select id="service-category-new" name="category" required>
                        <option value="" disabled selected>Выберите категорию</option>
                        <?php foreach ($serviceCategories as $category): ?>
                          <option value="<?= $escape($category) ?>"><?= $escape($category) ?></option>
                        <?php endforeach; ?>
                      </select>

                      <label for="service-title-new">Название</label>
                      <input id="service-title-new" type="text" name="title" required />

                      <label for="service-description-new">Описание для карточки услуги</label>
                      <textarea id="service-description-new" name="description" rows="6"></textarea>

                      <div class="admin-inline-fields">
                        <div>
                          <label for="service-base-price-new">Базовая цена, ₽</label>
                          <input id="service-base-price-new" type="number" min="0" step="0.01" name="base_price" value="0" />
                        </div>
                        <div>
                          <label for="service-unit-new">Единица</label>
                          <input id="service-unit-new" type="text" name="unit_name" value="шт." />
                        </div>
                      </div>
                      <label class="admin-check"><input type="checkbox" name="calculator_enabled" checked /> Показывать в калькуляторе</label>

                      <button class="btn btn-nav" type="submit"><svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#message"></use></svg>Добавить услугу</button>
                    </form>
                  </div>

                  <div class="admin-grid">
                    <?php if (!$services): ?>
                      <div class="admin-card"><p class="section-subtitle">Услуги не найдены. Добавьте первую услугу выше.</p></div>
                    <?php endif; ?>

                    <?php foreach ($services as $service): ?>
                      <?php $serviceId = (int) ($service['id'] ?? 0); ?>
                      <div class="admin-card admin-service">
                        <div class="admin-service-meta">
                          <h3><?= $escape((string) ($service['title'] ?? '')) ?></h3>
                          <span class="admin-service-id">ID: <?= $escape((string) $serviceId) ?></span>
                        </div>
                        <form class="admin-form" method="post">
                          <input type="hidden" name="action" value="update_service" />
                          <input type="hidden" name="service_id" value="<?= $escape((string) $serviceId) ?>" />
                          <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>" />

                          <label for="service-category-<?= $escape((string) $serviceId) ?>">Категория</label>
                          <select id="service-category-<?= $escape((string) $serviceId) ?>" name="category" required>
                            <?php foreach ($serviceCategories as $category): ?>
                              <option value="<?= $escape($category) ?>" <?= ($category === ($service['category'] ?? '')) ? 'selected' : '' ?>><?= $escape($category) ?></option>
                            <?php endforeach; ?>
                          </select>

                          <label for="service-title-<?= $escape((string) $serviceId) ?>">Название</label>
                          <input id="service-title-<?= $escape((string) $serviceId) ?>" type="text" name="title" value="<?= $escape((string) ($service['title'] ?? '')) ?>" required />

                          <label for="service-description-<?= $escape((string) $serviceId) ?>">Описание для карточки услуги</label>
                          <textarea id="service-description-<?= $escape((string) $serviceId) ?>" name="description" rows="6"><?= $escape((string) ($service['description'] ?? '')) ?></textarea>

                          <div class="admin-inline-fields">
                            <div>
                              <label for="service-base-price-<?= $escape((string) $serviceId) ?>">Базовая цена, ₽</label>
                              <input id="service-base-price-<?= $escape((string) $serviceId) ?>" type="number" min="0" step="0.01" name="base_price" value="<?= $escape((string) ($service['base_price'] ?? '0')) ?>" />
                            </div>
                            <div>
                              <label for="service-unit-<?= $escape((string) $serviceId) ?>">Единица</label>
                              <input id="service-unit-<?= $escape((string) $serviceId) ?>" type="text" name="unit_name" value="<?= $escape((string) ($service['unit_name'] ?? 'шт.')) ?>" />
                            </div>
                          </div>
                          <label class="admin-check"><input type="checkbox" name="calculator_enabled" <?= !empty($service['calculator_enabled']) ? 'checked' : '' ?> /> Показывать в калькуляторе</label>

                          <div class="admin-actions">
                            <button class="btn btn-nav" type="submit"><svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#message"></use></svg>Сохранить</button>
                            <button class="btn btn-danger" type="submit" form="delete-service-<?= $escape((string) $serviceId) ?>" onclick="return confirm('Удалить услугу?')">Удалить</button>
                          </div>
                        </form>
                        <form id="delete-service-<?= $escape((string) $serviceId) ?>" method="post" class="admin-form admin-form-inline">
                          <input type="hidden" name="action" value="delete_service" />
                          <input type="hidden" name="service_id" value="<?= $escape((string) $serviceId) ?>" />
                          <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>" />
                        </form>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            </div>
          </section>


          <section class="section admin-panel admin-panel-alt">
            <div class="container">
              <div class="admin-collapsible" data-admin-section data-section-id="calculator-admin">
                <div class="admin-section-title">
                  <h2 id="calculator-admin">Калькулятор стоимости</h2>
                  <button class="btn btn-collapse-toggle" type="button" data-admin-section-toggle aria-expanded="true">Свернуть</button>
                </div>
                <div class="admin-collapsible-body" data-admin-section-body>
                  <div class="admin-card admin-card-add-service">
                    <h3>Добавить параметр расчёта</h3>
                    <form class="admin-form" method="post">
                      <input type="hidden" name="action" value="add_calc_option" />
                      <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>" />
                      <label for="calc-service-new">Услуга</label>
                      <select id="calc-service-new" name="service_id" required>
                        <option value="" disabled selected>Выберите услугу</option>
                        <?php foreach ($services as $service): ?>
                          <option value="<?= $escape((string) ($service['id'] ?? 0)) ?>"><?= $escape((string) ($service['title'] ?? '')) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <div class="admin-inline-fields">
                        <div>
                          <label for="calc-type-new">Группа</label>
                          <input id="calc-type-new" type="text" name="option_type" value="Материал" required />
                        </div>
                        <div>
                          <label for="calc-title-new">Название</label>
                          <input id="calc-title-new" type="text" name="option_title" placeholder="Например: матовая бумага" required />
                        </div>
                      </div>
                      <div class="admin-inline-fields">
                        <div>
                          <label for="calc-price-new">Наценка, ₽</label>
                          <input id="calc-price-new" type="number" step="0.01" name="price_delta" value="0" />
                        </div>
                        <div>
                          <label for="calc-multiplier-new">Коэффициент</label>
                          <input id="calc-multiplier-new" type="number" step="0.001" min="0.01" name="multiplier" value="1" />
                        </div>
                        <div>
                          <label for="calc-sort-new">Сортировка</label>
                          <input id="calc-sort-new" type="number" name="sort_order" value="100" />
                        </div>
                      </div>
                      <label class="admin-check"><input type="checkbox" name="is_active" checked /> Активен</label>
                      <button class="btn btn-nav" type="submit">Добавить параметр</button>
                    </form>
                  </div>

                  <div class="admin-grid">
                    <?php foreach ($calculationOptions as $option): ?>
                      <?php $optionId = (int) ($option['id'] ?? 0); ?>
                      <div class="admin-card admin-service">
                        <h3><?= $escape((string) ($option['service_title'] ?? 'Услуга')) ?></h3>
                        <form class="admin-form" method="post">
                          <input type="hidden" name="action" value="update_calc_option" />
                          <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>" />
                          <input type="hidden" name="option_id" value="<?= $escape((string) $optionId) ?>" />
                          <label for="calc-service-<?= $escape((string) $optionId) ?>">Услуга</label>
                          <select id="calc-service-<?= $escape((string) $optionId) ?>" name="service_id" required>
                            <?php foreach ($services as $service): ?>
                              <option value="<?= $escape((string) ($service['id'] ?? 0)) ?>" <?= (int) ($service['id'] ?? 0) === (int) ($option['service_id'] ?? 0) ? 'selected' : '' ?>><?= $escape((string) ($service['title'] ?? '')) ?></option>
                            <?php endforeach; ?>
                          </select>
                          <label for="calc-type-<?= $escape((string) $optionId) ?>">Группа</label>
                          <input id="calc-type-<?= $escape((string) $optionId) ?>" type="text" name="option_type" value="<?= $escape((string) ($option['option_type'] ?? '')) ?>" required />
                          <label for="calc-title-<?= $escape((string) $optionId) ?>">Название</label>
                          <input id="calc-title-<?= $escape((string) $optionId) ?>" type="text" name="option_title" value="<?= $escape((string) ($option['title'] ?? '')) ?>" required />
                          <div class="admin-inline-fields">
                            <div><label for="calc-price-<?= $escape((string) $optionId) ?>">Наценка</label><input id="calc-price-<?= $escape((string) $optionId) ?>" type="number" step="0.01" name="price_delta" value="<?= $escape((string) ($option['price_delta'] ?? '0')) ?>" /></div>
                            <div><label for="calc-mult-<?= $escape((string) $optionId) ?>">Коэффициент</label><input id="calc-mult-<?= $escape((string) $optionId) ?>" type="number" step="0.001" min="0.01" name="multiplier" value="<?= $escape((string) ($option['multiplier'] ?? '1')) ?>" /></div>
                            <div><label for="calc-sort-<?= $escape((string) $optionId) ?>">Сортировка</label><input id="calc-sort-<?= $escape((string) $optionId) ?>" type="number" name="sort_order" value="<?= $escape((string) ($option['sort_order'] ?? '100')) ?>" /></div>
                          </div>
                          <label class="admin-check"><input type="checkbox" name="is_active" <?= !empty($option['is_active']) ? 'checked' : '' ?> /> Активен</label>
                          <div class="admin-actions">
                            <button class="btn btn-nav" type="submit">Сохранить</button>
                            <button class="btn btn-danger" type="submit" form="delete-calc-<?= $escape((string) $optionId) ?>" onclick="return confirm('Удалить параметр калькулятора?')">Удалить</button>
                          </div>
                        </form>
                        <form id="delete-calc-<?= $escape((string) $optionId) ?>" method="post" class="admin-form admin-form-inline">
                          <input type="hidden" name="action" value="delete_calc_option" />
                          <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>" />
                          <input type="hidden" name="option_id" value="<?= $escape((string) $optionId) ?>" />
                        </form>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <section class="section admin-panel admin-panel-alt">
            <div class="container">
              <div class="admin-collapsible" data-admin-section data-section-id="images">
                <div class="admin-section-title">
                  <h2>Фотографии сайта</h2>
                  <button class="btn btn-collapse-toggle" type="button" data-admin-section-toggle aria-expanded="true">Свернуть</button>
                </div>
                <div class="admin-collapsible-body" data-admin-section-body>
                  <div class="admin-grid admin-grid-2">
                    <?php foreach ($siteImageDefaults as $imageKey => $defaultPath): ?>
                      <?php $currentImagePath = (string) ($siteImageValues[$imageKey] ?? $defaultPath); $displayLabel = $siteImageLabels[$imageKey] ?? $imageKey; ?>
                      <div class="admin-card admin-image-card">
                        <h3><?= $escape($displayLabel) ?></h3>
                        <div class="admin-image-preview"><img src="<?= $escape($currentImagePath) ?>" alt="<?= $escape($displayLabel) ?>" loading="lazy" /></div>
                        <p class="admin-service-id"><?= $escape($currentImagePath) ?></p>
                        <form class="admin-form" method="post" enctype="multipart/form-data">
                          <input type="hidden" name="action" value="upload_site_image" />
                          <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>" />
                          <input type="hidden" name="image_key" value="<?= $escape($imageKey) ?>" />
                          <label for="site-image-<?= $escape($imageKey) ?>">Новый файл (JPG / PNG / WEBP)</label>
                          <input id="site-image-<?= $escape($imageKey) ?>" type="file" name="site_image" accept=".jpg,.jpeg,.png,.webp" required />
                          <button class="btn btn-nav" type="submit">Заменить фото</button>
                        </form>
                        <form class="admin-form" method="post">
                          <input type="hidden" name="action" value="restore_site_image" />
                          <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>" />
                          <input type="hidden" name="image_key" value="<?= $escape($imageKey) ?>" />
                          <button class="btn btn-danger" type="submit" onclick="return confirm('Восстановить фото по умолчанию?')">Восстановить по умолчанию</button>
                        </form>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <section class="section admin-panel admin-panel-alt">
            <div class="container">
              <div class="admin-collapsible" data-admin-section data-section-id="texts">
                <div class="admin-section-title">
                  <h2>Тексты сайта</h2>
                  <button class="btn btn-collapse-toggle" type="button" data-admin-section-toggle aria-expanded="true">Свернуть</button>
                </div>
                <div class="admin-collapsible-body" data-admin-section-body>
                  <div class="admin-card">
                    <form class="admin-form" method="post">
                      <input type="hidden" name="action" value="save_site_text" />
                      <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>" />

                      <?php foreach ($siteTextDefaults as $contentKey => $defaultValue): ?>
                        <?php $label = $siteTextLabels[$contentKey] ?? $contentKey; $value = (string) ($siteTextValues[$contentKey] ?? $defaultValue); $rows = $stringLength($value) > 120 ? 4 : 2; ?>
                        <label for="site-text-<?= $escape($contentKey) ?>"><?= $escape($label) ?></label>
                        <textarea id="site-text-<?= $escape($contentKey) ?>" name="site_text[<?= $escape($contentKey) ?>]" rows="<?= $escape((string) $rows) ?>"><?= $escape($value) ?></textarea>
                      <?php endforeach; ?>

                      <button class="btn btn-nav" type="submit"><svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#message"></use></svg>Сохранить тексты</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </section>
        <?php endif; ?>
      </main>
    <?php endif; ?>

    <script src="app.js" defer></script>
  </body>
</html>

<?php

declare(strict_types=1);

require_once __DIR__ . '/api/content.php';
require_once __DIR__ . '/api/seo.php';
require_once __DIR__ . '/api/auth.php';

client_session_start();
$errors = [];
$values = ['name' => '', 'email' => '', 'phone' => ''];
$escape = static fn (?string $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$homeContent = default_home_content();

try {
    $pdo = db();
    crm_ensure_schema($pdo);
    $homeContent = get_home_content($pdo);
    if (client_auth_user($pdo)) {
        header('Location: cabinet.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $values['name'] = trim((string) ($_POST['name'] ?? ''));
        $values['email'] = trim((string) ($_POST['email'] ?? ''));
        $values['phone'] = trim((string) ($_POST['phone'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordRepeat = (string) ($_POST['password_repeat'] ?? '');
        $consent = isset($_POST['privacy_consent']);

        if ($values['name'] === '') { $errors[] = 'Введите имя.'; }
        if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) { $errors[] = 'Введите корректную почту.'; }
        if (strlen(preg_replace('/\D+/', '', $values['phone']) ?: '') < 10) { $errors[] = 'Введите корректный телефон.'; }
        if (strlen($password) < 6) { $errors[] = 'Пароль должен быть не короче 6 символов.'; }
        if ($password !== $passwordRepeat) { $errors[] = 'Пароли не совпадают.'; }
        if (!$consent) { $errors[] = 'Необходимо согласие с политикой конфиденциальности.'; }

        if (!$errors) {
            $phone = crm_normalize_phone($values['phone']);
            $stmt = $pdo->prepare('SELECT id FROM clients WHERE email = :email OR phone = :phone LIMIT 1');
            $stmt->execute([':email' => $values['email'], ':phone' => $phone]);
            if ($stmt->fetchColumn()) {
                $errors[] = 'Пользователь с такой почтой или телефоном уже зарегистрирован.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO clients (name, email, phone, password_hash) VALUES (:name, :email, :phone, :password_hash)');
                $stmt->execute([
                    ':name' => $values['name'],
                    ':email' => $values['email'],
                    ':phone' => $phone,
                    ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ]);
                $_SESSION['client_id'] = (int) $pdo->lastInsertId();
                session_regenerate_id(true);
                header('Location: cabinet.php');
                exit;
            }
        }
    }
} catch (Throwable $exception) {
    $errors[] = 'Ошибка регистрации: ' . $exception->getMessage();
}

$pageTitle = 'Регистрация клиента — РА «Жираф»';
?>
<!doctype html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $escape($pageTitle) ?></title>
    <meta name="robots" content="noindex, follow" />
    <link rel="canonical" href="<?= $escape(seo_url('/register.php')) ?>" />
    <link rel="icon" href="media/favicon.ico" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&family=Nunito:wght@700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body class="auth-page">
    <main class="section auth-section">
      <div class="container">
        <div class="auth-card manager-modal">
          <a class="logo auth-logo" href="index.php"><img src="media/logo/Logo-Full.svg" alt="РА Жираф" /></a>
          <h1>Регистрация</h1>
          <p class="manager-modal-subtitle">Создайте аккаунт, чтобы оформлять заказы и отслеживать статусы.</p>
          <?php if ($errors): ?><div class="admin-alert admin-alert--error"><?php foreach ($errors as $error): ?><p><?= $escape($error) ?></p><?php endforeach; ?></div><?php endif; ?>
          <form class="manager-form auth-form" method="post">
            <div class="manager-form-fields">
              <label for="register-name">Имя</label>
              <input id="register-name" type="text" name="name" value="<?= $escape($values['name']) ?>" autocomplete="name" required />
              <label for="register-email">Почта</label>
              <input id="register-email" type="email" name="email" value="<?= $escape($values['email']) ?>" autocomplete="email" required />
              <label for="register-phone">Телефон</label>
              <div class="manager-field"><input id="register-phone" type="tel" name="phone" value="<?= $escape($values['phone']) ?>" autocomplete="tel" required /></div>
              <label for="register-password">Пароль</label>
              <input id="register-password" type="password" name="password" autocomplete="new-password" required />
              <label for="register-password-repeat">Повтор пароля</label>
              <input id="register-password-repeat" type="password" name="password_repeat" autocomplete="new-password" required />
            </div>
            <div class="manager-consent">
              <input id="register-consent" type="checkbox" name="privacy_consent" required />
              <label for="register-consent" class="manager-consent-label">Я согласен на <a href="privacy.php" target="_blank" rel="noopener noreferrer">обработку персональных данных</a></label>
            </div>
            <button class="btn manager-submit" type="submit">Зарегистрироваться</button>
          </form>
          <p class="auth-switch">Уже есть аккаунт? <a href="login.php">Войдите</a></p>
        </div>
      </div>
    </main>
  </body>
</html>

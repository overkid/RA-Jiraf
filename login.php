<?php

declare(strict_types=1);

require_once __DIR__ . '/api/content.php';
require_once __DIR__ . '/api/seo.php';
require_once __DIR__ . '/api/auth.php';

client_session_start();
$errors = [];
$email = trim((string) ($_POST['email'] ?? ''));
$escape = static fn (?string $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

try {
    $pdo = db();
    crm_ensure_schema($pdo);
    if (client_auth_user($pdo)) {
        header('Location: cabinet.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = (string) ($_POST['password'] ?? '');
        if ($email === '' || $password === '') {
            $errors[] = 'Введите почту и пароль.';
        } elseif (!client_login($pdo, $email, $password)) {
            $errors[] = 'Неверная почта или пароль.';
        } else {
            header('Location: cabinet.php');
            exit;
        }
    }
} catch (Throwable $exception) {
    $errors[] = 'Ошибка входа: ' . $exception->getMessage();
}

$pageTitle = 'Вход клиента — РА «Жираф»';
?>
<!doctype html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $escape($pageTitle) ?></title>
    <meta name="robots" content="noindex, follow" />
    <link rel="canonical" href="<?= $escape(seo_url('/login.php')) ?>" />
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
          <h1>Вход</h1>
          <p class="manager-modal-subtitle">Войдите, чтобы оформить заказ и отслеживать его статус.</p>
          <?php if ($errors): ?><div class="admin-alert admin-alert--error"><?php foreach ($errors as $error): ?><p><?= $escape($error) ?></p><?php endforeach; ?></div><?php endif; ?>
          <form class="manager-form auth-form" method="post">
            <div class="manager-form-fields">
              <label for="login-email">Почта</label>
              <input id="login-email" type="email" name="email" value="<?= $escape($email) ?>" autocomplete="email" required />
              <label for="login-password">Пароль</label>
              <input id="login-password" type="password" name="password" autocomplete="current-password" required />
            </div>
            <button class="btn manager-submit" type="submit">Войти</button>
          </form>
          <p class="auth-switch">Еще нет аккаунта? <a href="register.php">Зарегистрируйтесь</a></p>
        </div>
      </div>
    </main>
  </body>
</html>

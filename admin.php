<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/api/db.php';

const CONTENT_FIELDS = [
    'hero_title' => 'Заголовок первого экрана',
    'hero_subtitle' => 'Подзаголовок первого экрана',
    'services_title' => 'Заголовок блока услуг',
    'services_subtitle' => 'Подзаголовок блока услуг',
    'footer_title' => 'Заголовок нижнего блока',
    'footer_text' => 'Текст нижнего блока',
];

$defaults = [
    'hero_title' => 'Рекламное агентство полного цикла',
    'hero_subtitle' => 'Мы предлагаем свои производственные и рекламные услуги на территории всей Владимирской области',
    'services_title' => 'Полный спектр услуг',
    'services_subtitle' => 'Услуги рекламного агентства покрывают почти все возможные потребности',
    'footer_title' => 'Мы готовы решить вашу проблему',
    'footer_text' => 'Вам не обязательно ехать в офис рекламного агентства — можно оформить заказ дистанционно по удобному каналу связи',
];

$errors = [];
$success = null;

function isAdminLoggedIn(): bool
{
    return ($_SESSION['is_admin'] ?? false) === true;
}

function loadAdminCredentials(): array
{
    $configPath = __DIR__ . '/config/admin.php';
    if (!file_exists($configPath)) {
        throw new RuntimeException('Файл config/admin.php не найден. Скопируйте config/admin.php.example в config/admin.php');
    }

    $config = require $configPath;
    if (!isset($config['username'], $config['password'])) {
        throw new RuntimeException('В config/admin.php должны быть username и password');
    }

    return $config;
}

function getContentMap(PDO $pdo, array $defaults): array
{
    $result = $defaults;
    $stmt = $pdo->query('SELECT content_key, content_value FROM site_content');

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $result[$row['content_key']] = $row['content_value'];
    }

    return $result;
}

try {
    $pdo = db();
} catch (Throwable $exception) {
    $pdo = null;
    $errors[] = $exception->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'login') {
        try {
            $credentials = loadAdminCredentials();
            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');

            if ($username === $credentials['username'] && $password === $credentials['password']) {
                $_SESSION['is_admin'] = true;
                header('Location: admin.php');
                exit;
            }

            $errors[] = 'Неверный логин или пароль.';
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }
    }

    if ($action === 'logout') {
        session_destroy();
        header('Location: admin.php');
        exit;
    }

    if (isAdminLoggedIn() && $pdo instanceof PDO) {
        if ($action === 'save_home_content') {
            $stmt = $pdo->prepare('INSERT INTO site_content (content_key, content_value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)');

            foreach (array_keys(CONTENT_FIELDS) as $key) {
                $value = trim((string) ($_POST[$key] ?? ''));
                if ($value === '') {
                    $errors[] = 'Все поля главной страницы обязательны.';
                    break;
                }
                $stmt->execute([':key' => $key, ':value' => $value]);
            }

            if (!$errors) {
                $success = 'Тексты главной страницы сохранены.';
            }
        }

        if ($action === 'add_service') {
            $category = trim((string) ($_POST['category'] ?? ''));
            $title = trim((string) ($_POST['title'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? ''));

            if ($category === '' || $title === '' || $description === '') {
                $errors[] = 'Для добавления услуги заполните все поля.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO services (category, title, description) VALUES (:category, :title, :description)');
                $stmt->execute([
                    ':category' => $category,
                    ':title' => $title,
                    ':description' => $description,
                ]);
                $success = 'Услуга добавлена.';
            }
        }

        if ($action === 'update_service') {
            $id = (int) ($_POST['id'] ?? 0);
            $category = trim((string) ($_POST['category'] ?? ''));
            $title = trim((string) ($_POST['title'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? ''));

            if ($id <= 0 || $category === '' || $title === '' || $description === '') {
                $errors[] = 'Для редактирования услуги заполните все поля.';
            } else {
                $stmt = $pdo->prepare('UPDATE services SET category = :category, title = :title, description = :description WHERE id = :id');
                $stmt->execute([
                    ':id' => $id,
                    ':category' => $category,
                    ':title' => $title,
                    ':description' => $description,
                ]);
                $success = 'Услуга обновлена.';
            }
        }

        if ($action === 'delete_service') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare('DELETE FROM services WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $success = 'Услуга удалена.';
            }
        }
    }
}

$content = $defaults;
$services = [];
$requests = [];

if (isAdminLoggedIn() && $pdo instanceof PDO) {
    $content = getContentMap($pdo, $defaults);
    $services = $pdo->query('SELECT id, category, title, description FROM services ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
    $requests = $pdo->query('SELECT id, name, phone, comment, created_at FROM client_requests ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Админка — РА «Жираф»</title>
    <link rel="icon" href="media/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body class="admin-page">
    <header class="admin-header">
      <div class="container admin-header-inner">
        <a class="logo" href="index.php"><img src="media/logo/Logo-Full.svg" alt="РА Жираф" /></a>
        <h1>Админ-панель</h1>
        <?php if (isAdminLoggedIn()): ?>
          <form method="post" class="admin-logout">
            <input type="hidden" name="action" value="logout" />
            <button type="submit" class="btn">Выйти</button>
          </form>
        <?php endif; ?>
      </div>
    </header>

    <main class="admin-layout">
      <?php if ($errors): ?>
        <div class="admin-alert admin-alert-error"><?= htmlspecialchars(implode(' ', $errors), ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="admin-alert admin-alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>

      <?php if (!isAdminLoggedIn()): ?>
        <section class="admin-card admin-login-card">
          <h2>Вход в админку</h2>
          <p class="admin-muted">Введите логин и пароль из файла <code>config/admin.php</code>.</p>
          <form method="post" class="admin-form-stack">
            <input type="hidden" name="action" value="login" />
            <label>Логин<input type="text" name="username" required /></label>
            <label>Пароль<input type="password" name="password" required /></label>
            <button type="submit" class="btn btn-nav">Войти</button>
          </form>
        </section>
      <?php else: ?>
        <section class="admin-card">
          <h2>Тексты главной страницы</h2>
          <form method="post" class="admin-form-grid">
            <input type="hidden" name="action" value="save_home_content" />
            <?php foreach (CONTENT_FIELDS as $key => $label): ?>
              <label>
                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                <?php if (strpos($key, 'title') !== false): ?>
                  <input type="text" name="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($content[$key] ?? '', ENT_QUOTES, 'UTF-8') ?>" required />
                <?php else: ?>
                  <textarea name="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" rows="3" required><?= htmlspecialchars($content[$key] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                <?php endif; ?>
              </label>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-nav">Сохранить тексты</button>
          </form>
        </section>

        <section class="admin-card">
          <h2>Каталог услуг</h2>

          <h3>Добавить услугу</h3>
          <form method="post" class="admin-form-grid admin-add-service">
            <input type="hidden" name="action" value="add_service" />
            <label>Категория<input type="text" name="category" required /></label>
            <label>Название<input type="text" name="title" required /></label>
            <label>Описание<textarea name="description" rows="3" required></textarea></label>
            <button type="submit" class="btn btn-nav">Добавить</button>
          </form>

          <div class="admin-services-list">
            <?php foreach ($services as $service): ?>
              <form method="post" class="admin-service-item">
                <input type="hidden" name="id" value="<?= (int) $service['id'] ?>" />
                <strong>ID #<?= (int) $service['id'] ?></strong>
                <label>Категория<input type="text" name="category" value="<?= htmlspecialchars($service['category'], ENT_QUOTES, 'UTF-8') ?>" required /></label>
                <label>Название<input type="text" name="title" value="<?= htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8') ?>" required /></label>
                <label>Описание<textarea name="description" rows="2" required><?= htmlspecialchars($service['description'], ENT_QUOTES, 'UTF-8') ?></textarea></label>
                <div class="admin-actions">
                  <button type="submit" name="action" value="update_service" class="btn">Сохранить</button>
                  <button type="submit" name="action" value="delete_service" class="btn">Удалить</button>
                </div>
              </form>
            <?php endforeach; ?>
          </div>
        </section>

        <section class="admin-card">
          <h2>Заявки пользователей</h2>
          <div class="admin-requests-table-wrap">
            <table class="admin-requests-table">
              <thead>
                <tr><th>ID</th><th>Дата</th><th>Имя</th><th>Телефон</th><th>Комментарий</th></tr>
              </thead>
              <tbody>
                <?php foreach ($requests as $request): ?>
                  <tr>
                    <td><?= (int) $request['id'] ?></td>
                    <td><?= htmlspecialchars($request['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($request['name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($request['phone'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($request['comment'] ?: '—', ENT_QUOTES, 'UTF-8') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>
      <?php endif; ?>
    </main>
  </body>
</html>

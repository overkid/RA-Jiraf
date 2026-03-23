<?php

declare(strict_types=1);

session_start();

$errors = [];
$messages = [];
$adminConfigPath = __DIR__ . '/config/admin.php';
$adminConfig = file_exists($adminConfigPath) ? require $adminConfigPath : null;
$loggedIn = !empty($_SESSION['admin_logged_in']);

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
];
$services = [];
$requests = [];
$newRequestLookup = [];

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

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if (!in_array($action, ADMIN_FORM_ACTIONS, true)) {
        $errors[] = 'Неизвестное действие.';
    } elseif ($action === 'login') {
        $inputUser = trim((string) ($_POST['username'] ?? ''));
        $inputPass = trim((string) ($_POST['password'] ?? ''));

        if (!$adminConfig) {
            $errors[] = 'Файл config/admin.php не найден. Скопируйте config/admin.php.example и задайте логин с паролем.';
        } elseif ($inputUser === '' || $inputPass === '') {
            $errors[] = 'Введите логин и пароль.';
        } else {
            $configUser = (string) ($adminConfig['username'] ?? '');
            $configPassword = (string) ($adminConfig['password'] ?? '');
            $configHash = (string) ($adminConfig['password_hash'] ?? '');

            $validUser = $configUser !== '' && hash_equals($configUser, $inputUser);
            $validPassword = false;

            if ($configHash !== '') {
                $validPassword = password_verify($inputPass, $configHash);
            } elseif ($configPassword !== '') {
                $validPassword = hash_equals($configPassword, $inputPass);
            }

            if ($validUser && $validPassword) {
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $loggedIn = true;
                $messages[] = 'Вы вошли в админку.';
            } else {
                $errors[] = 'Неверный логин или пароль.';
            }
        }
    } elseif (!$loggedIn) {
        $errors[] = 'Сначала войдите в админку.';
    } else {
        try {
            require_once __DIR__ . '/api/db.php';
            $pdo = db();
        } catch (Throwable $exception) {
            $pdo = null;
            $errors[] = 'Не удалось подключиться к базе данных: ' . $exception->getMessage();
        }

        if ($pdo instanceof PDO) {
            if ($action === 'add_service') {
                $category = trim((string) ($_POST['category'] ?? ''));
                $title = trim((string) ($_POST['title'] ?? ''));
                $description = trim((string) ($_POST['description'] ?? ''));

                if ($category === '' || $title === '') {
                    $errors[] = 'Категория и название услуги обязательны.';
                } elseif (!in_array($category, $serviceCategories, true)) {
                    $errors[] = 'Выберите категорию из списка.';
                } else {
                    try {
                        $stmt = $pdo->prepare(
                            'INSERT INTO services (category, title, description) VALUES (:category, :title, :description)'
                        );
                        $stmt->execute([
                            ':category' => $category,
                            ':title' => $title,
                            ':description' => $description,
                        ]);
                        $messages[] = 'Услуга добавлена.';
                    } catch (Throwable $exception) {
                        $errors[] = 'Не удалось добавить услугу: ' . $exception->getMessage();
                    }
                }
            }

            if ($action === 'update_service') {
                $serviceId = (int) ($_POST['service_id'] ?? 0);
                $category = trim((string) ($_POST['category'] ?? ''));
                $title = trim((string) ($_POST['title'] ?? ''));
                $description = trim((string) ($_POST['description'] ?? ''));

                if ($serviceId <= 0) {
                    $errors[] = 'Не удалось определить услугу для обновления.';
                } elseif ($category === '' || $title === '') {
                    $errors[] = 'Категория и название услуги обязательны.';
                } elseif (!in_array($category, $serviceCategories, true)) {
                    $errors[] = 'Выберите категорию из списка.';
                } else {
                    try {
                        $stmt = $pdo->prepare(
                            'UPDATE services SET category = :category, title = :title, description = :description WHERE id = :id'
                        );
                        $stmt->execute([
                            ':category' => $category,
                            ':title' => $title,
                            ':description' => $description,
                            ':id' => $serviceId,
                        ]);
                        $messages[] = 'Услуга обновлена.';
                    } catch (Throwable $exception) {
                        $errors[] = 'Не удалось обновить услугу: ' . $exception->getMessage();
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

            if ($action === 'delete_request') {
                $requestId = (int) ($_POST['request_id'] ?? 0);
                if ($requestId <= 0) {
                    $errors[] = 'Не удалось определить заявку для удаления.';
                } else {
                    try {
                        $stmt = $pdo->prepare('DELETE FROM client_requests WHERE id = :id');
                        $stmt->execute([':id' => $requestId]);

                        if ($stmt->rowCount() > 0) {
                            $messages[] = 'Заявка удалена.';
                        } else {
                            $errors[] = 'Заявка не найдена или уже удалена.';
                        }

                        $seenRequestIds = array_map('intval', (array) ($_SESSION['admin_seen_request_ids'] ?? []));
                        $_SESSION['admin_seen_request_ids'] = array_values(array_filter(
                            $seenRequestIds,
                            static fn (int $seenId): bool => $seenId !== $requestId
                        ));
                    } catch (Throwable $exception) {
                        $errors[] = 'Не удалось удалить заявку: ' . $exception->getMessage();
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
        $services = $pdo->query('SELECT id, category, title, description FROM services ORDER BY category, id')->fetchAll();
        try {
            $requests = $pdo
                ->query('SELECT id, name, phone, comment, created_at, service_title, service_is_other FROM client_requests ORDER BY created_at DESC')
                ->fetchAll();
        } catch (Throwable $exception) {
            $requests = $pdo->query('SELECT id, name, phone, comment, created_at FROM client_requests ORDER BY created_at DESC')->fetchAll();
        }

        $seenRequestIds = array_values(array_unique(array_map('intval', (array) ($_SESSION['admin_seen_request_ids'] ?? []))));
        $requestIds = array_values(array_filter(
            array_map(static fn (array $request): int => (int) ($request['id'] ?? 0), $requests),
            static fn (int $id): bool => $id > 0
        ));

        $newRequestIds = array_values(array_diff($requestIds, $seenRequestIds));
        $newRequestLookup = array_fill_keys($newRequestIds, true);

        $_SESSION['admin_seen_request_ids'] = array_values(array_unique(array_merge($seenRequestIds, $requestIds)));
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
              <a class="nav-vk" href="#" aria-label="ВКонтакте">
                <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#vk"></use></svg>
              </a>
              <?php if ($loggedIn): ?>
                <a class="btn btn-nav" href="admin.php?logout=1">Выйти</a>
              <?php endif; ?>
            </div>
          </div>
        </nav>

        <div class="catalog-hero">
          <h1>Админ-панель</h1>
          <p class="section-subtitle">Управление услугами и заявками клиентов</p>
        </div>
      </div>
    </header>

    <?php if (!$loggedIn): ?>
      <main>
        <section class="section admin-panel">
          <div class="container">
            <div class="admin-card admin-card--center">
              <h2>Вход в админку</h2>
              <p class="section-subtitle">Введите логин и пароль администратора</p>

              <?php if ($errors): ?>
                <div class="admin-alert admin-alert--error">
                  <?php foreach ($errors as $message): ?>
                    <p><?= $escape($message) ?></p>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <form class="admin-form" method="post">
                <input type="hidden" name="action" value="login" />
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
                  ?>
                  <div class="admin-item">
                    <div class="admin-request-head">
                      <h3><?= $escape($request['name'] ?? '') ?></h3>
                      <?php if ($isNewRequest): ?>
                        <span class="admin-badge-new">Новое</span>
                      <?php endif; ?>
                    </div>
                    <p><span>ID:</span> <?= $escape((string) $requestId) ?></p>
                    <p><span>Телефон:</span> <?= $escape($request['phone'] ?? '') ?></p>
                    <?php
                    $requestServiceTitle = trim((string) ($request['service_title'] ?? ''));
                    $requestServiceIsOther = (bool) ($request['service_is_other'] ?? false);
                    $requestServiceValue = $requestServiceIsOther ? 'Другое' : $requestServiceTitle;
                    ?>
                    <?php if ($requestServiceValue !== ''): ?>
                      <p><span>Услуга:</span> <?= $escape($requestServiceValue) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($request['comment'])): ?>
                      <p><span>Комментарий:</span> <?= $escape($request['comment'] ?? '') ?></p>
                    <?php endif; ?>
                    <p><span>Дата:</span> <?= $escape($formatDateTime($request['created_at'] ?? null)) ?></p>
                    <form class="admin-form admin-request-delete-form" method="post">
                      <input type="hidden" name="action" value="delete_request" />
                      <input type="hidden" name="request_id" value="<?= $escape((string) $requestId) ?>" />
                      <button class="btn btn-danger" type="submit" onclick="return confirm('Удалить заявку?')">
                        Удалить заявку
                      </button>
                    </form>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </section>

        <section class="section admin-panel admin-panel-alt">
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
              <h2 id="services-admin">Каталог услуг</h2>
            </div>

            <div class="admin-card">
              <h3>Добавить услугу</h3>
              <form class="admin-form" method="post">
                <input type="hidden" name="action" value="add_service" />
                <label for="service-category-new">Категория</label>
                <select id="service-category-new" name="category" required>
                  <option value="" disabled selected>Выберите категорию</option>
                  <?php foreach ($serviceCategories as $category): ?>
                    <option value="<?= $escape($category) ?>"><?= $escape($category) ?></option>
                  <?php endforeach; ?>
                </select>

                <label for="service-title-new">Название</label>
                <input id="service-title-new" type="text" name="title" required />

                <label for="service-description-new">Описание для карточки услуги (открывается по кнопке «Подробнее»)</label>
                <textarea id="service-description-new" name="description" rows="6"></textarea>

                <button class="btn btn-nav" type="submit">
                  <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#message"></use></svg>Добавить услугу
                </button>
              </form>
            </div>

            <div class="admin-grid">
              <?php if (!$services): ?>
                <div class="admin-card">
                  <p class="section-subtitle">Услуги не найдены. Добавьте первую услугу выше.</p>
                </div>
              <?php endif; ?>

              <?php foreach ($services as $service): ?>
                <div class="admin-card admin-service">
                  <div class="admin-service-meta">
                    <h3><?= $escape($service['title'] ?? '') ?></h3>
                    <span class="admin-service-id">ID: <?= $escape((string) ($service['id'] ?? '')) ?></span>
                  </div>
                  <form class="admin-form" method="post">
                    <input type="hidden" name="action" value="update_service" />
                    <input type="hidden" name="service_id" value="<?= $escape((string) ($service['id'] ?? '')) ?>" />

                    <label for="service-category-<?= $escape((string) ($service['id'] ?? '')) ?>">Категория</label>
                    <select
                      id="service-category-<?= $escape((string) ($service['id'] ?? '')) ?>"
                      name="category"
                      required
                    >
                      <?php foreach ($serviceCategories as $category): ?>
                        <option
                          value="<?= $escape($category) ?>"
                          <?= ($category === ($service['category'] ?? '')) ? 'selected' : '' ?>
                        >
                          <?= $escape($category) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>

                    <label for="service-title-<?= $escape((string) ($service['id'] ?? '')) ?>">Название</label>
                    <input
                      id="service-title-<?= $escape((string) ($service['id'] ?? '')) ?>"
                      type="text"
                      name="title"
                      value="<?= $escape($service['title'] ?? '') ?>"
                      required
                    />

                    <label for="service-description-<?= $escape((string) ($service['id'] ?? '')) ?>">Описание для карточки услуги (открывается по кнопке «Подробнее»)</label>
                    <textarea
                      id="service-description-<?= $escape((string) ($service['id'] ?? '')) ?>"
                      name="description"
                      rows="6"
                    ><?= $escape($service['description'] ?? '') ?></textarea>

                    <div class="admin-actions">
                      <button class="btn btn-nav" type="submit">
                        <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#message"></use></svg>Сохранить
                      </button>
                      <button
                        class="btn btn-danger"
                        type="submit"
                        form="delete-service-<?= $escape((string) ($service['id'] ?? '')) ?>"
                        onclick="return confirm('Удалить услугу?')"
                      >
                        Удалить
                      </button>
                    </div>
                  </form>
                  <form
                    id="delete-service-<?= $escape((string) ($service['id'] ?? '')) ?>"
                    method="post"
                    class="admin-form admin-form-inline"
                  >
                    <input type="hidden" name="action" value="delete_service" />
                    <input type="hidden" name="service_id" value="<?= $escape((string) ($service['id'] ?? '')) ?>" />
                  </form>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
      </main>
    <?php endif; ?>

    <script src="app.js" defer></script>
  </body>
</html>








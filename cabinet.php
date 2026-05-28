<?php

declare(strict_types=1);

require_once __DIR__ . '/api/content.php';
require_once __DIR__ . '/api/seo.php';
require_once __DIR__ . '/api/auth.php';

$escape = static fn (?string $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$formatDateTime = static function (?string $value): string {
    if (!$value) return '';
    try { return (new DateTime($value))->format('d.m.Y H:i'); } catch (Throwable) { return $value; }
};

$errors = [];
$messages = [];
$homeContent = default_home_content();
$orders = [];
$currentOrders = [];
$historyOrders = [];
$files = [];

try {
    $pdo = db();
    crm_ensure_schema($pdo);
    $client = client_require_user($pdo);
    $homeContent = get_home_content($pdo);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'update_profile') {
            $name = trim((string) ($_POST['name'] ?? ''));
            if ($name === '') {
                $errors[] = 'Имя не должно быть пустым.';
            } else {
                $stmt = $pdo->prepare('UPDATE clients SET name = :name WHERE id = :id');
                $stmt->execute([':name' => $name, ':id' => (int) $client['id']]);
                $client['name'] = $name;
                $messages[] = 'Имя обновлено.';
            }
        }

        if ($action === 'upload_order_file' && isset($_FILES['layout_file'])) {
            $orderId = (int) ($_POST['order_id'] ?? 0);
            $stmt = $pdo->prepare('SELECT id FROM orders WHERE id = :id AND client_id = :client_id LIMIT 1');
            $stmt->execute([':id' => $orderId, ':client_id' => (int) $client['id']]);
            if (!$stmt->fetchColumn()) {
                $errors[] = 'Заказ не найден.';
            } else {
                $stored = crm_store_uploaded_file($_FILES['layout_file'], (string) $client['phone'], null, $orderId, 'client');
                crm_insert_file($pdo, $stored);
                $messages[] = 'Файл макета прикреплён к заказу.';
            }
        }
    }

    $stmt = $pdo->prepare('SELECT * FROM orders WHERE client_id = :client_id ORDER BY updated_at DESC, created_at DESC');
    $stmt->execute([':client_id' => (int) $client['id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($orders as $order) {
        $status = crm_normalize_order_status((string) ($order['status'] ?? 'review'));
        if ($status === 'ready') {
            $historyOrders[] = $order;
        } else {
            $currentOrders[] = $order;
        }
    }

    $stmt = $pdo->prepare('SELECT rf.* FROM request_files rf INNER JOIN orders o ON o.id = rf.order_id WHERE o.client_id = :client_id ORDER BY rf.created_at DESC');
    $stmt->execute([':client_id' => (int) $client['id']]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $exception) {
    $errors[] = 'Не удалось загрузить кабинет: ' . $exception->getMessage();
    $client = $client ?? ['name' => '', 'email' => '', 'phone' => ''];
}

$pageTitle = 'Личный кабинет клиента — РА «Жираф»';
?>
<!doctype html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $escape($pageTitle) ?></title>
    <meta name="description" content="Личный кабинет клиента РА Жираф: текущие заказы, история заказов и статусы." />
    <meta name="robots" content="noindex, follow" />
    <link rel="canonical" href="<?= $escape(seo_url('/cabinet.php')) ?>" />
    <link rel="icon" href="media/favicon.ico" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&family=Nunito:wght@700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css" />
    <script src="animations.js" defer></script>
  </head>
  <body class="cabinet-page" data-client-auth="1">
    <header class="catalog-header">
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
              <li><a href="index.php"><svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#home"></use></svg><?= $escape($homeContent['nav_home_label']) ?></a></li>
              <li><a href="services.php"><svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#catalog"></use></svg><?= $escape($homeContent['nav_services_label']) ?></a></li>
              <li><a href="cabinet.php"><svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#case"></use></svg>Кабинет</a></li>
            </ul>
            <div class="nav-actions"><a class="btn btn-nav" href="logout.php">Выйти</a></div>
          </div>
        </nav>
        <div class="catalog-hero">
          <h1>Личный кабинет</h1>
          <p class="section-subtitle">Текущие заказы, история заказов и статусы выполнения.</p>
        </div>
      </div>
    </header>

    <main>
      <section class="section crm-section">
        <div class="container">
          <?php if ($errors): ?><div class="admin-alert admin-alert--error"><?php foreach ($errors as $error): ?><p><?= $escape($error) ?></p><?php endforeach; ?></div><?php endif; ?>
          <?php if ($messages): ?><div class="admin-alert admin-alert--success"><?php foreach ($messages as $message): ?><p><?= $escape($message) ?></p><?php endforeach; ?></div><?php endif; ?>

          <div class="crm-dashboard">
            <article class="crm-metric"><span><?= $escape((string) count($currentOrders)) ?></span><p>Текущие заказы</p></article>
            <article class="crm-metric"><span><?= $escape((string) count($historyOrders)) ?></span><p>История заказов</p></article>
            <article class="crm-metric"><span><?= $escape((string) count($files)) ?></span><p>Макеты</p></article>
          </div>

          <div class="crm-grid crm-grid-2">
            <section class="crm-card">
              <h2>Профиль</h2>
              <form class="crm-form" method="post">
                <input type="hidden" name="action" value="update_profile" />
                <label for="profile-name">Имя пользователя</label>
                <input id="profile-name" type="text" name="name" value="<?= $escape((string) ($client['name'] ?? '')) ?>" required />
                <p class="cabinet-meta">Почта: <?= $escape((string) ($client['email'] ?? '')) ?></p>
                <p class="cabinet-meta">Телефон: <?= $escape((string) ($client['phone'] ?? '')) ?></p>
                <button class="btn btn-nav" type="submit">Сохранить имя</button>
              </form>
            </section>

            <section class="crm-card">
              <h2>Быстрый заказ</h2>
              <p class="section-subtitle">Выберите услугу в каталоге и нажмите «Оформить заказ» — заказ сразу появится здесь.</p>
              <a class="btn btn-contact" href="services.php">Перейти к услугам</a>
            </section>
          </div>

          <section class="crm-card cabinet-orders">
            <h2>Текущие заказы</h2>
            <?php if (!$currentOrders): ?>
              <p class="section-subtitle">Текущих заказов пока нет.</p>
            <?php else: ?>
              <div class="crm-list">
                <?php foreach ($currentOrders as $order): ?>
                  <?php $status = crm_normalize_order_status((string) ($order['status'] ?? 'review')); ?>
                  <article class="crm-list-item">
                    <div class="crm-item-head">
                      <h3><?= $escape((string) $order['title']) ?></h3>
                      <span class="admin-status-badge admin-status-badge--<?= $escape($status) ?>"><?= $escape(ORDER_STATUS_LABELS[$status]) ?></span>
                    </div>
                    <p>Цена: <?= number_format((float) $order['total_amount'], 0, ',', ' ') ?> ₽</p>
                    <p>Создан: <?= $escape($formatDateTime((string) $order['created_at'])) ?></p>
                    <form class="crm-form" method="post" enctype="multipart/form-data">
                      <input type="hidden" name="action" value="upload_order_file" />
                      <input type="hidden" name="order_id" value="<?= $escape((string) $order['id']) ?>" />
                      <label for="order-file-<?= $escape((string) $order['id']) ?>">Прикрепить макет</label>
                      <input id="order-file-<?= $escape((string) $order['id']) ?>" type="file" name="layout_file" accept=".pdf,.jpg,.jpeg,.png,.webp,.svg,.zip" required />
                      <button class="btn btn-nav" type="submit">Загрузить</button>
                    </form>
                  </article>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </section>

          <section class="crm-card cabinet-orders">
            <h2>История заказов</h2>
            <?php if (!$historyOrders): ?>
              <p class="section-subtitle">Завершённых заказов пока нет.</p>
            <?php else: ?>
              <div class="crm-list">
                <?php foreach ($historyOrders as $order): ?>
                  <article class="crm-list-item">
                    <div class="crm-item-head">
                      <h3><?= $escape((string) $order['title']) ?></h3>
                      <span class="admin-status-badge admin-status-badge--ready">Готово</span>
                    </div>
                    <p>Цена: <?= number_format((float) $order['total_amount'], 0, ',', ' ') ?> ₽</p>
                    <p>Обновлён: <?= $escape($formatDateTime((string) $order['updated_at'])) ?></p>
                  </article>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </section>
        </div>
      </section>
    </main>
    <script src="app.js" defer></script>
  </body>
</html>

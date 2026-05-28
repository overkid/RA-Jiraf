<?php

declare(strict_types=1);

require_once __DIR__ . '/api/content.php';
require_once __DIR__ . '/api/seo.php';
require_once __DIR__ . '/api/crm.php';

$homeContent = default_home_content();
$phoneInput = trim((string) ($_GET['phone'] ?? $_POST['phone'] ?? ''));
$normalizedPhone = $phoneInput !== '' ? crm_normalize_phone($phoneInput) : '';
$client = null;
$requests = [];
$orders = [];
$files = [];
$error = '';
$message = '';
$escape = static fn (?string $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$formatDateTime = static function (?string $value): string {
    if (!$value) return '';
    try { return (new DateTime($value))->format('d.m.Y H:i'); } catch (Throwable) { return $value; }
};

try {
    require_once __DIR__ . '/api/db.php';
    $pdo = db();
    crm_ensure_schema($pdo);
    $homeContent = get_home_content($pdo);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['layout_file'])) {
        $requestId = isset($_POST['request_id']) ? (int) $_POST['request_id'] : null;
        $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : null;
        $requestId = $requestId && $requestId > 0 ? $requestId : null;
        $orderId = $orderId && $orderId > 0 ? $orderId : null;
        if ($normalizedPhone === '') {
            $error = 'Укажите телефон, чтобы прикрепить файл.';
        } else {
            $stored = crm_store_uploaded_file($_FILES['layout_file'], $normalizedPhone, $requestId, $orderId, 'client');
            crm_insert_file($pdo, $stored);
            $message = 'Файл макета прикреплён.';
        }
    }

    if ($normalizedPhone !== '') {
        $stmt = $pdo->prepare('SELECT * FROM clients WHERE phone = :phone LIMIT 1');
        $stmt->execute([':phone' => $normalizedPhone]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        $stmt = $pdo->prepare('SELECT * FROM client_requests WHERE phone = :phone ORDER BY created_at DESC');
        $stmt->execute([':phone' => $normalizedPhone]);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare(
            'SELECT o.*, c.name AS client_name
             FROM orders o
             INNER JOIN clients c ON c.id = o.client_id
             WHERE c.phone = :phone
             ORDER BY o.updated_at DESC, o.created_at DESC'
        );
        $stmt->execute([':phone' => $normalizedPhone]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare('SELECT * FROM request_files WHERE client_phone = :phone ORDER BY created_at DESC');
        $stmt->execute([':phone' => $normalizedPhone]);
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $exception) {
    $error = $error ?: 'Не удалось загрузить кабинет: ' . $exception->getMessage();
}

$pageTitle = 'Личный кабинет клиента — РА «Жираф»';
$pageDescription = 'Проверка заявок, заказов, статусов и загрузка макетов для клиентов рекламного агентства «Жираф».';
?>
<!doctype html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $escape($pageTitle) ?></title>
    <meta name="description" content="<?= $escape($pageDescription) ?>" />
    <link rel="canonical" href="<?= $escape(seo_url('/cabinet.php')) ?>" />
    <link rel="icon" href="media/favicon.ico" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&family=Nunito:wght@700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css" />
    <script src="animations.js" defer></script>
  </head>
  <body class="cabinet-page">
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
          </div>
        </nav>
        <div class="catalog-hero">
          <h1>Личный кабинет</h1>
          <p class="section-subtitle">Введите телефон из заявки: увидите статусы заказов, расчёты и сможете прикрепить макеты.</p>
        </div>
      </div>
    </header>

    <main>
      <section class="section crm-section">
        <div class="container">
          <div class="crm-card crm-card--accent">
            <form class="crm-form crm-search" method="get">
              <label for="cabinet-phone">Телефон клиента</label>
              <div class="crm-inline-fields">
                <input id="cabinet-phone" type="tel" name="phone" placeholder="+79000000000" value="<?= $escape($phoneInput) ?>" required />
                <button class="btn btn-nav" type="submit">Открыть кабинет</button>
              </div>
            </form>
          </div>

          <?php if ($error): ?><div class="admin-alert admin-alert--error"><p><?= $escape($error) ?></p></div><?php endif; ?>
          <?php if ($message): ?><div class="admin-alert admin-alert--success"><p><?= $escape($message) ?></p></div><?php endif; ?>

          <?php if ($normalizedPhone !== ''): ?>
            <div class="crm-dashboard">
              <article class="crm-metric"><span><?= $escape((string) count($requests)) ?></span><p>Заявок</p></article>
              <article class="crm-metric"><span><?= $escape((string) count($orders)) ?></span><p>Заказов</p></article>
              <article class="crm-metric"><span><?= $escape((string) count($files)) ?></span><p>Файлов</p></article>
            </div>

            <div class="crm-grid crm-grid-2">
              <section class="crm-card">
                <h2>Мои заказы</h2>
                <?php if (!$orders): ?>
                  <p class="section-subtitle">Заказов пока нет. Менеджер создаст заказ после обработки заявки.</p>
                <?php else: ?>
                  <div class="crm-list">
                    <?php foreach ($orders as $order): ?>
                      <?php $status = crm_normalize_order_status((string) ($order['status'] ?? 'new')); ?>
                      <article class="crm-list-item">
                        <div class="crm-item-head">
                          <h3>Заказ №<?= $escape((string) $order['id']) ?></h3>
                          <span class="admin-status-badge admin-status-badge--<?= $escape($status) ?>"><?= $escape(ORDER_STATUS_LABELS[$status]) ?></span>
                        </div>
                        <p><strong><?= $escape((string) $order['title']) ?></strong></p>
                        <p>Сумма: <?= number_format((float) $order['total_amount'], 0, ',', ' ') ?> ₽</p>
                        <p>Обновлён: <?= $escape($formatDateTime((string) $order['updated_at'])) ?></p>
                      </article>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </section>

              <section class="crm-card">
                <h2>Заявки и макеты</h2>
                <?php if (!$requests): ?>
                  <p class="section-subtitle">По этому телефону заявок пока нет.</p>
                <?php else: ?>
                  <div class="crm-list">
                    <?php foreach ($requests as $request): ?>
                      <article class="crm-list-item">
                        <div class="crm-item-head">
                          <h3>Заявка №<?= $escape((string) $request['id']) ?></h3>
                          <span class="admin-status-badge admin-status-badge--<?= $escape((string) $request['request_status']) ?>"><?= $escape((string) $request['request_status']) ?></span>
                        </div>
                        <p><?= $escape((string) ($request['service_title'] ?: 'Индивидуальный запрос')) ?></p>
                        <?php if (!empty($request['estimated_total'])): ?><p>Оценка: <?= number_format((float) $request['estimated_total'], 0, ',', ' ') ?> ₽</p><?php endif; ?>
                        <form class="crm-form" method="post" enctype="multipart/form-data">
                          <input type="hidden" name="phone" value="<?= $escape($normalizedPhone) ?>" />
                          <input type="hidden" name="request_id" value="<?= $escape((string) $request['id']) ?>" />
                          <label for="file-request-<?= $escape((string) $request['id']) ?>">Прикрепить макет</label>
                          <input id="file-request-<?= $escape((string) $request['id']) ?>" type="file" name="layout_file" accept=".pdf,.jpg,.jpeg,.png,.webp,.svg,.zip" required />
                          <button class="btn btn-nav" type="submit">Загрузить</button>
                        </form>
                      </article>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </section>
            </div>

            <section class="crm-card">
              <h2>Прикреплённые файлы</h2>
              <?php if (!$files): ?>
                <p class="section-subtitle">Файлов пока нет.</p>
              <?php else: ?>
                <div class="crm-file-list">
                  <?php foreach ($files as $file): ?>
                    <a class="crm-file" href="<?= $escape((string) $file['stored_path']) ?>" target="_blank" rel="noopener noreferrer">
                      <strong><?= $escape((string) $file['original_name']) ?></strong>
                      <span><?= $escape($formatDateTime((string) $file['created_at'])) ?></span>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </section>
          <?php endif; ?>
        </div>
      </section>
    </main>
    <script src="app.js" defer></script>
  </body>
</html>

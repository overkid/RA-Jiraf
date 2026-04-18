<?php

declare(strict_types=1);

require __DIR__ . '/api/content.php';
require __DIR__ . '/api/seo.php';

$servicesData = [];
$homeContent = default_home_content();
$siteImages = default_site_images();

try {
    require_once __DIR__ . '/api/db.php';
    $connection = db();
    $stmt = $connection->query('SELECT id, category, title, description FROM services ORDER BY category, id');
    $servicesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $homeContent = get_home_content($connection);
    $siteImages = get_site_images($connection);
} catch (Throwable $exception) {
    $servicesData = [];
    $homeContent = default_home_content();
    $siteImages = default_site_images();
}

$pageTitle = 'Услуги РА «Жираф» — типография, сувениры, печать и наружная реклама';
$pageDescription = 'Каталог услуг рекламного агентства «Жираф»: полиграфия, сувенирная продукция, широкоформатная печать и наружная реклама.';
$canonicalUrl = seo_url('/services.php');
$ogImageUrl = seo_url('/' . ltrim((string) ($siteImages['services_card_1'] ?? 'media/img/Visitka.png'), '/'));

$servicesStructuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => 'Рекламные услуги РА «Жираф»',
    'serviceType' => 'Полиграфия, сувениры, широкоформатная печать, наружная реклама',
    'areaServed' => 'Владимирская область',
    'provider' => [
        '@type' => 'AdvertisingAgency',
        'name' => 'РА «Жираф»',
        'url' => seo_base_url(),
    ],
];
?>
<!doctype html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>" />
    <meta name="keywords" content="каталог услуг, типография, полиграфия, сувенирная продукция, широкоформатная печать, наружная реклама, Владимир" />
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />
    <meta name="theme-color" content="#ff6600" />
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>" />
    <link rel="sitemap" type="application/xml" title="Sitemap" href="/sitemap.php" />
    <meta property="og:locale" content="ru_RU" />
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="РА «Жираф»" />
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>" />
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>" />
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>" />
    <meta property="og:image" content="<?= htmlspecialchars($ogImageUrl, ENT_QUOTES, 'UTF-8') ?>" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>" />
    <meta name="twitter:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>" />
    <meta name="twitter:image" content="<?= htmlspecialchars($ogImageUrl, ENT_QUOTES, 'UTF-8') ?>" />
    <script type="application/ld+json"><?= json_encode($servicesStructuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
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
  <body class="catalog-page">
    <header class="catalog-header section">
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
                  <?= htmlspecialchars($homeContent['nav_home_label'], ENT_QUOTES, 'UTF-8') ?>
                </a>
              </li>
              <li>
                <a href="services.php">
                  <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#catalog"></use></svg>
                  <?= htmlspecialchars($homeContent['nav_services_label'], ENT_QUOTES, 'UTF-8') ?>
                </a>
              </li>
            </ul>
            <div class="nav-actions">
              <a class="nav-vk" href="https://vk.com/giraf33" target="_blank" rel="noopener noreferrer" aria-label="ВКонтакте">
                <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#vk"></use></svg>
              </a>
              <button class="btn btn-nav" type="button" data-open-manager-modal>
                <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#message"></use></svg>
                <?= htmlspecialchars($homeContent['nav_contact_button'], ENT_QUOTES, 'UTF-8') ?>
              </button>
            </div>
          </div>
        </nav>

        <div class="catalog-hero">
          <h1><?= htmlspecialchars($homeContent['catalog_title'], ENT_QUOTES, 'UTF-8') ?></h1>
          <p class="section-subtitle"><?= nl2br(htmlspecialchars($homeContent['catalog_subtitle'], ENT_QUOTES, 'UTF-8')) ?></p>
        </div>
      </div>
    </header>

    <main>
      <div data-vue-catalog data-initial-services="<?= htmlspecialchars(json_encode($servicesData, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>" hidden></div>
      <section class="services-catalog section">
        <div class="container">
          <div class="catalog-tabs" role="tablist" aria-label="Категории услуг">
            <button class="catalog-tab is-active" type="button" data-category-tab="print">Типография и полиграфия</button>
            <button class="catalog-tab" type="button" data-category-tab="souvenir">Сувенирная продукция</button>
            <button class="catalog-tab" type="button" data-category-tab="wide">Широкоформатная печать</button>
            <button class="catalog-tab" type="button" data-category-tab="outdoor">Наружная реклама</button>
          </div>

          <div class="catalog-group" data-category="print">
            <div class="catalog-grid">
              <article class="service-tile"><h3>Изготовление визиток</h3><button class="btn btn-disabled" disabled>Подробнее</button></article>
              <article class="service-tile"><h3>Печать буклетов и листовок</h3><button class="btn btn-disabled" disabled>Подробнее</button></article>
              <article class="service-tile"><h3>Печать фирменных бланков</h3><button class="btn btn-disabled" disabled>Подробнее</button></article>
              <article class="service-tile"><h3>Изготовление календарей</h3><button class="btn btn-disabled" disabled>Подробнее</button></article>
            </div>
          </div>

          <div class="catalog-group" data-category="souvenir" hidden>
            <div class="catalog-grid">
              <article class="service-tile"><h3>Нанесение логотипа на кружки</h3><button class="btn btn-disabled" disabled>Подробнее</button></article>
              <article class="service-tile"><h3>Печать на футболках</h3><button class="btn btn-disabled" disabled>Подробнее</button></article>
              <article class="service-tile"><h3>Сувенирные ручки с логотипом</h3><button class="btn btn-disabled" disabled>Подробнее</button></article>
              <article class="service-tile"><h3>Подарочные наборы для компаний</h3><button class="btn btn-disabled" disabled>Подробнее</button></article>
            </div>
          </div>

          <div class="catalog-group" data-category="wide" hidden>
            <div class="catalog-grid">
              <article class="service-tile"><h3>Изготовление рекламных баннеров</h3><button class="btn btn-disabled" disabled>Подробнее</button></article>
              <article class="service-tile"><h3>Печать наклеек для заднего и лобового стекла</h3><button class="btn btn-disabled" disabled>Подробнее</button></article>
              <article class="service-tile"><h3>Печать на холсте</h3><button class="btn btn-disabled" disabled>Подробнее</button></article>
              <article class="service-tile"><h3>Печать виниловых наклеек и стикеров</h3><button class="btn btn-disabled" disabled>Подробнее</button></article>
            </div>
          </div>

          <div class="catalog-group" data-category="outdoor" hidden>
            <div class="catalog-grid">
              <article class="service-tile"><h3>Изготовление световых коробов</h3><button class="btn btn-disabled" disabled>Подробнее</button></article>
              <article class="service-tile"><h3>Монтаж вывесок под ключ</h3><button class="btn btn-disabled" disabled>Подробнее</button></article>
              <article class="service-tile"><h3>Оформление входных групп</h3><button class="btn btn-disabled" disabled>Подробнее</button></article>
              <article class="service-tile"><h3>Брендирование фасадов и витрин</h3><button class="btn btn-disabled" disabled>Подробнее</button></article>
            </div>
          </div>
        </div>
      </section>

      <section class="catalog-help section">
        <div class="container">
          <h2><?= htmlspecialchars($homeContent['catalog_help_title'], ENT_QUOTES, 'UTF-8') ?></h2>
          <p class="section-subtitle"><?= nl2br(htmlspecialchars($homeContent['catalog_help_subtitle'], ENT_QUOTES, 'UTF-8')) ?></p>
          <button type="button" class="btn btn-contact" data-open-manager-modal>
            <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#message"></use></svg><?= htmlspecialchars($homeContent['contact_button_text'], ENT_QUOTES, 'UTF-8') ?>
          </button>
        </div>
      </section>
    </main>

    <footer class="catalog-footer">
      <div class="container footer-meta">
        <div>
          <p><?= htmlspecialchars($homeContent['footer_email'], ENT_QUOTES, 'UTF-8') ?></p>
          <p><?= htmlspecialchars($homeContent['footer_phone_1'], ENT_QUOTES, 'UTF-8') ?></p>
          <p><?= htmlspecialchars($homeContent['footer_phone_2'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="footer-logo"><img src="media/logo/Logo-Full.svg" alt="РА Жираф" /></div>
        <div>
          <p><?= htmlspecialchars($homeContent['footer_address_title'], ENT_QUOTES, 'UTF-8') ?></p>
          <p><?= htmlspecialchars($homeContent['footer_address_line_1'], ENT_QUOTES, 'UTF-8') ?></p>
          <p><?= htmlspecialchars($homeContent['footer_address_line_2'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
      </div>
    </footer>

    <div class="modal-overlay modal-overlay-service" data-service-modal aria-hidden="true">
      <section class="manager-modal service-modal" role="dialog" aria-modal="true" aria-labelledby="service-modal-title">
        <button class="modal-close" type="button" data-close-service-modal aria-label="Закрыть описание услуги">
          <span aria-hidden="true">✕</span>
        </button>
        <h2 id="service-modal-title"><?= htmlspecialchars($homeContent['service_modal_title'], ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="service-modal-category" data-service-modal-category></p>
        <div class="service-modal-description" data-service-modal-description>
          <p><?= htmlspecialchars($homeContent['service_modal_fallback_text'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <p class="service-modal-note"><?= htmlspecialchars($homeContent['service_modal_note'], ENT_QUOTES, 'UTF-8') ?></p>
        <button class="btn manager-submit" type="button" data-service-modal-contact>
          <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#message"></use></svg><?= htmlspecialchars($homeContent['service_modal_contact_button'], ENT_QUOTES, 'UTF-8') ?>
        </button>
      </section>
    </div>

    <div class="modal-overlay" data-manager-modal aria-hidden="true">
      <section class="manager-modal" role="dialog" aria-modal="true" aria-labelledby="manager-modal-title">
        <button class="modal-close" type="button" data-close-manager-modal aria-label="Закрыть форму">
          <span aria-hidden="true">✕</span>
        </button>
        <h2><?= htmlspecialchars($homeContent['manager_modal_title'], ENT_QUOTES, 'UTF-8') ?></h2>
        <p><?= htmlspecialchars($homeContent['manager_modal_text'], ENT_QUOTES, 'UTF-8') ?></p>
        <form class="manager-form">
          <div class="manager-form-fields">
            <label for="manager-name"><?= htmlspecialchars($homeContent['manager_name_label'], ENT_QUOTES, 'UTF-8') ?></label>
            <input id="manager-name" type="text" name="name" placeholder="<?= htmlspecialchars($homeContent['manager_name_placeholder'], ENT_QUOTES, 'UTF-8') ?>" autocomplete="name" required />

            <label for="manager-phone"><?= htmlspecialchars($homeContent['manager_phone_label'], ENT_QUOTES, 'UTF-8') ?></label>
            <div class="manager-field" data-phone-field>
              <span class="manager-phone-prefix" aria-hidden="true">+7</span>
              <input
                id="manager-phone"
                type="tel"
                name="phone"
                placeholder="900 000 00 00"
                autocomplete="tel"
                inputmode="tel"
                required
                aria-describedby="manager-phone-error"
              />
              <svg class="manager-field-error-icon" aria-hidden="true"><use href="media/icons/sprite.svg#error"></use></svg>
            </div>
            <p class="manager-field-error" id="manager-phone-error">Неверный формат номера</p>

            <label for="manager-service"><?= htmlspecialchars($homeContent['manager_service_label'], ENT_QUOTES, 'UTF-8') ?></label>
            <select id="manager-service" name="service">
              <option value="" selected disabled><?= htmlspecialchars($homeContent['manager_service_placeholder'], ENT_QUOTES, 'UTF-8') ?></option>
              <option value="other"><?= htmlspecialchars($homeContent['manager_service_other'], ENT_QUOTES, 'UTF-8') ?></option>
            </select>

            <label for="manager-comment"><?= htmlspecialchars($homeContent['manager_comment_label'], ENT_QUOTES, 'UTF-8') ?></label>
            <textarea id="manager-comment" name="comment" rows="3" required></textarea>
          </div>
          <div class="manager-consent">
            <input id="manager-consent" type="checkbox" name="privacy_consent" required />
            <label for="manager-consent" class="manager-consent-label">
              Я согласен на
              <a href="privacy.php" target="_blank" rel="noopener noreferrer">обработку персональных данных</a>
            </label>
          </div>
          <p class="manager-form-success" data-manager-success role="status" aria-live="polite" hidden><?= htmlspecialchars($homeContent['manager_success_text'], ENT_QUOTES, 'UTF-8') ?></p>
          <button class="btn manager-submit" type="submit">
            <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#message"></use></svg><?= htmlspecialchars($homeContent['manager_submit_button'], ENT_QUOTES, 'UTF-8') ?>
          </button>
        </form>
      </section>
    </div>

    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js" defer></script>
    <script src="app.js" defer></script>
  </body>
</html>

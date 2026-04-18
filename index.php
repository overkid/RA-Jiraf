<?php

declare(strict_types=1);

require __DIR__ . '/api/content.php';
require __DIR__ . '/api/seo.php';

$homeContent = default_home_content();
$siteImages = default_site_images();
$dbConnection = null;

try {
    require_once __DIR__ . '/api/db.php';
    $dbConnection = db();
    $homeContent = get_home_content($dbConnection);
    $siteImages = get_site_images($dbConnection);
} catch (Throwable $exception) {
    $homeContent = default_home_content();
    $siteImages = default_site_images();
}

$pageTitle = 'РА «Жираф» — рекламное агентство во Владимире';
$pageDescription = 'Рекламное агентство «Жираф»: типография, сувенирная продукция, широкоформатная печать и наружная реклама во Владимире и области.';
$canonicalUrl = seo_url('/');
$ogImagePath = (string) ($siteImages['portfolio_card_2'] ?? 'media/img/KachestvVisit.png');
$ogImageUrl = seo_url('/' . ltrim($ogImagePath, '/'));

$organizationStructuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'AdvertisingAgency',
    'name' => 'РА «Жираф»',
    'url' => seo_base_url(),
    'logo' => seo_url('/media/logo/Logo-Full.svg'),
    'telephone' => ['+7 (4922) 46-64-84', '+7 (958) 510-64-84'],
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => 'ул. Ставровская, д. 4',
        'addressLocality' => 'Владимир',
        'addressCountry' => 'RU',
    ],
    'sameAs' => ['https://vk.com/giraf33'],
];
?>
<!doctype html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>" />
    <meta name="keywords" content="рекламное агентство Владимир, типография Владимир, полиграфия, наружная реклама, широкоформатная печать, сувенирная продукция" />
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
    <script type="application/ld+json"><?= json_encode($organizationStructuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
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
  <body>
    <header class="hero">
      <div class="container">
        <nav class="top-nav">
          <a class="logo" href="#"><img src="media/logo/Logo-Full.svg" alt="РА Жираф" /></a>
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

        <div class="hero-content">
          <h1><?= htmlspecialchars($homeContent['hero_title'], ENT_QUOTES, 'UTF-8') ?></h1>
          <p class="hero-text">
            <?= nl2br(htmlspecialchars($homeContent['hero_text'], ENT_QUOTES, 'UTF-8')) ?>
          </p>
          <button class="btn btn-contact" type="button" data-open-manager-modal>
            <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#message"></use></svg><?= htmlspecialchars($homeContent['contact_button_text'], ENT_QUOTES, 'UTF-8') ?>
          </button>
        </div>
        <a href="#services" class="hero-down" aria-label="Вниз">
          <svg class="icon icon-large" aria-hidden="true"><use href="media/icons/sprite.svg#down"></use></svg>
        </a>
      </div>
    </header>

    <main>
      <section id="services" class="section services">
        <div class="container">
          <h2><?= htmlspecialchars($homeContent['services_title'], ENT_QUOTES, 'UTF-8') ?></h2>
          <p class="section-subtitle"><?= nl2br(htmlspecialchars($homeContent['services_subtitle'], ENT_QUOTES, 'UTF-8')) ?></p>
          <div class="cards grid-4">
            <article class="card">
              <div class="media"><img loading="lazy" decoding="async" src="<?= htmlspecialchars($siteImages['services_card_1'], ENT_QUOTES, 'UTF-8') ?>" alt="Изготовление визиток" /></div>
              <h3>Изготовление визиток</h3>
              <a href="services.php" class="btn btn-card"><svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#catalog"></use></svg>Подробнее</a>
            </article>
            <article class="card">
              <div class="media"><img loading="lazy" decoding="async" src="<?= htmlspecialchars($siteImages['services_card_2'], ENT_QUOTES, 'UTF-8') ?>" alt="Широкоформатная печать" /></div>
              <h3>Широкоформатная печать</h3>
              <a href="services.php" class="btn btn-card"><svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#catalog"></use></svg>Подробнее</a>
            </article>
            <article class="card">
              <div class="media"><img loading="lazy" decoding="async" src="<?= htmlspecialchars($siteImages['services_card_3'], ENT_QUOTES, 'UTF-8') ?>" alt="Информационные стенды" /></div>
              <h3>Информационные стенды</h3>
              <a href="services.php" class="btn btn-card"><svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#catalog"></use></svg>Подробнее</a>
            </article>
            <article class="card">
              <div class="media"><img loading="lazy" decoding="async" src="<?= htmlspecialchars($siteImages['services_card_4'], ENT_QUOTES, 'UTF-8') ?>" alt="Наружная реклама" /></div>
              <h3>Наружная реклама</h3>
              <a href="services.php" class="btn btn-card"><svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#catalog"></use></svg>Подробнее</a>
            </article>
          </div>
        </div>
      </section>

      <section class="section highlights">
        <div class="container">
          <h2><?= htmlspecialchars($homeContent['highlights_title'], ENT_QUOTES, 'UTF-8') ?></h2>
          <div class="feature-row">
            <div class="feature"><?= htmlspecialchars($homeContent['highlight_1'], ENT_QUOTES, 'UTF-8') ?> <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#like"></use></svg></div>
            <div class="feature"><?= htmlspecialchars($homeContent['highlight_2'], ENT_QUOTES, 'UTF-8') ?> <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#speed"></use></svg></div>
            <div class="feature"><?= htmlspecialchars($homeContent['highlight_3'], ENT_QUOTES, 'UTF-8') ?> <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#point"></use></svg></div>
          </div>
        </div>
      </section>

      <section class="section portfolio">
        <div class="container">
          <h2><?= htmlspecialchars($homeContent['portfolio_title'], ENT_QUOTES, 'UTF-8') ?></h2>
          <p class="section-subtitle"><?= nl2br(htmlspecialchars($homeContent['portfolio_subtitle'], ENT_QUOTES, 'UTF-8')) ?></p>
          <div class="portfolio-conveyor" data-portfolio-conveyor>
            <div class="portfolio-track" data-portfolio-track>
              <article class="portfolio-card">
                <div class="media"><img loading="lazy" decoding="async" src="<?= htmlspecialchars($siteImages['portfolio_card_1'], ENT_QUOTES, 'UTF-8') ?>" alt="Чашки с печатью" /></div>
                <h3>Чашки с печатью</h3>
                <p>Аккуратный и качественный перенос фирменного стиля на сувенирную продукцию</p>
              </article>
              <article class="portfolio-card">
                <div class="media"><img loading="lazy" decoding="async" src="<?= htmlspecialchars($siteImages['portfolio_card_2'], ENT_QUOTES, 'UTF-8') ?>" alt="Качественные визитки" /></div>
                <h3>Отличные визитки</h3>
                <p>Мы напечатали визитки для приёмщиков макулатуры: плотные, аккуратные</p>
              </article>
              <article class="portfolio-card">
                <div class="media"><img loading="lazy" decoding="async" src="<?= htmlspecialchars($siteImages['portfolio_card_3'], ENT_QUOTES, 'UTF-8') ?>" alt="Важные книжки" /></div>
                <h3>Важные книжки</h3>
                <p>Каждая такая книжка аккуратная, плотная и собрана с вниманием</p>
              </article>
              <article class="portfolio-card">
                <div class="media"><img loading="lazy" decoding="async" src="<?= htmlspecialchars($siteImages['portfolio_card_4'], ENT_QUOTES, 'UTF-8') ?>" alt="Объёмные стикеры" /></div>
                <h3>Объёмные стикеры</h3>
                <p>Яркий дизайн, плотные цвета и объём делают их заметными</p>
              </article>
              <article class="portfolio-card">
                <div class="media"><img loading="lazy" decoding="async" src="<?= htmlspecialchars($siteImages['portfolio_card_5'], ENT_QUOTES, 'UTF-8') ?>" alt="Приятные браслеты" /></div>
                <h3>Приятные браслеты</h3>
                <p>Сделали браслетики для Президентской академии: мягкие, лёгкие и с чётким лого</p>
              </article>
              <article class="portfolio-card">
                <div class="media"><img loading="lazy" decoding="async" src="<?= htmlspecialchars($siteImages['portfolio_card_6'], ENT_QUOTES, 'UTF-8') ?>" alt="Идеальные принты" /></div>
                <h3>Идеальные принты</h3>
                <p>Сделали нанесение на рукава свитшотов: плёнка и плоттерная резка</p>
              </article>
              <article class="portfolio-card">
                <div class="media"><img loading="lazy" decoding="async" src="<?= htmlspecialchars($siteImages['portfolio_card_7'], ENT_QUOTES, 'UTF-8') ?>" alt="Гордые дипломы" /></div>
                <h3>Гордые дипломы</h3>
                <p>Напечатали дипломы с насыщенной ровной заливкой цвета и аккуратным дизайном</p>
              </article>
              <article class="portfolio-card">
                <div class="media"><img loading="lazy" decoding="async" src="<?= htmlspecialchars($siteImages['portfolio_card_8'], ENT_QUOTES, 'UTF-8') ?>" alt="Рабочие постеры" /></div>
                <h3>Рабочие постеры</h3>
                <p>Печатаем их быстро, ярко и точно в формат — будь то распродажа, скидка или акция</p>
              </article>
            </div>
          </div>
        </div>
      </section>
    </main>

    <footer id="contact" class="footer-cta">
      <div class="container">
        <h2><?= htmlspecialchars($homeContent['footer_title'], ENT_QUOTES, 'UTF-8') ?></h2>
        <p>
          <?= nl2br(htmlspecialchars($homeContent['footer_text'], ENT_QUOTES, 'UTF-8')) ?>
        </p>
        <button type="button" class="btn btn-contact" data-open-manager-modal>
          <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#message"></use></svg><?= htmlspecialchars($homeContent['contact_button_text'], ENT_QUOTES, 'UTF-8') ?>
        </button>

        <div class="footer-meta">
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
      </div>
    </footer>

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
              &#1071; &#1089;&#1086;&#1075;&#1083;&#1072;&#1089;&#1077;&#1085; &#1085;&#1072;
              <a href="privacy.php" target="_blank" rel="noopener noreferrer">&#1086;&#1073;&#1088;&#1072;&#1073;&#1086;&#1090;&#1082;&#1091; &#1087;&#1077;&#1088;&#1089;&#1086;&#1085;&#1072;&#1083;&#1100;&#1085;&#1099;&#1093; &#1076;&#1072;&#1085;&#1085;&#1099;&#1093;</a>
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

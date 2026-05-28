<?php

declare(strict_types=1);

require __DIR__ . '/api/content.php';
require __DIR__ . '/api/seo.php';

$homeContent = default_home_content();
$siteImages = default_site_images();

try {
    require_once __DIR__ . '/api/db.php';
    $connection = db();
    $homeContent = get_home_content($connection);
    $siteImages = get_site_images($connection);
} catch (Throwable $exception) {
    $homeContent = default_home_content();
    $siteImages = default_site_images();
}

$reviews = [
    [
        'name' => 'Александр Иванов',
        'text' => 'Отличный сервис! Ребята быстро разобрались с требованиями и выполнили заказ на высочайшем уровне. Очень доволен результатом!'
    ],
    [
        'name' => 'Екатерина Петрова',
        'text' => 'Профессиональный подход к работе, вежливый персонал и качественный результат. Обязательно вернусь к вам ещё!'
    ],
    [
        'name' => 'Сергей Сидоров',
        'text' => 'Спасибо за оперативность и внимание к деталям. Наши визитки выглядят просто великолепно. Рекомендую всем!'
    ],
    [
        'name' => 'Мария Куликова',
        'text' => 'Отличная типография! Печать качественная, цены разумные. Работали над брошюрами - результат превзошёл ожидания.'
    ],
    [
        'name' => 'Дмитрий Морозов',
        'text' => 'Благодарю команду за творческий подход. Наш баннер выглядит потрясающе! Это именно то, что нам было нужно.'
    ],
    [
        'name' => 'Анна Волкова',
        'text' => 'Сувениры с логотипом компании выглядят безупречно. Коллеги в восторге! Спасибо за профессионализм.'
    ]
];

$pageTitle = 'Отзывы РА «Жираф» — отзывы клиентов о типографии и услугах';
$pageDescription = 'Отзывы довольных клиентов о работе рекламного агентства «Жираф»: типография, сувенирная продукция, печать и наружная реклама.';
$canonicalUrl = seo_url('/reviews.php');
$ogImageUrl = seo_url('/' . ltrim((string) ($siteImages['services_card_1'] ?? 'media/img/Visitka.png'), '/'));

$reviewsStructuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'LocalBusiness',
    'name' => 'РА «Жираф»',
    'url' => seo_base_url(),
    'aggregateRating' => [
        '@type' => 'AggregateRating',
        'ratingValue' => '5',
        'ratingCount' => count($reviews),
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
    <meta name="keywords" content="отзывы, рекламное агентство, типография, полиграфия, Владимир" />
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
    <script type="application/ld+json"><?= json_encode($reviewsStructuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
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
              <li>
                <a href="reviews.php">
                  <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#heart"></use></svg>
                  Отзывы
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
          <h1>Отзывы клиентов</h1>
          <p class="section-subtitle">Что о нас говорят наши довольные клиенты</p>
        </div>
      </div>
    </header>

    <main>
      <section class="reviews-catalog section">
        <div class="container">
          <div class="review-grid">
            <?php foreach ($reviews as $review): ?>
              <article class="review-tile">
                <div class="review-tile-header">
                  <h3 class="review-tile-name"><?= htmlspecialchars($review['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                  <div class="review-tile-stars" aria-label="5 звезд">
                    <img class="review-tile-star" src="media/icons/star.svg" alt="" />
                    <img class="review-tile-star" src="media/icons/star.svg" alt="" />
                    <img class="review-tile-star" src="media/icons/star.svg" alt="" />
                    <img class="review-tile-star" src="media/icons/star.svg" alt="" />
                    <img class="review-tile-star" src="media/icons/star.svg" alt="" />
                  </div>
                </div>
                <p class="review-tile-text"><?= htmlspecialchars($review['text'], ENT_QUOTES, 'UTF-8') ?></p>
              </article>
            <?php endforeach; ?>
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

    <script src="app.js" defer></script>
  </body>
</html>

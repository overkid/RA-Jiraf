<?php

declare(strict_types=1);

require __DIR__ . '/api/content.php';
require __DIR__ . '/api/seo.php';

$reviews = [];
$homeContent = default_home_content();

try {
    require_once __DIR__ . '/api/db.php';
    $connection = db();
    $stmt = $connection->query('SELECT id, name, rating, review_text, created_at FROM client_reviews WHERE review_status = "approved" ORDER BY created_at DESC');
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $homeContent = get_home_content($connection);
} catch (Throwable $exception) {
    $reviews = [];
    $homeContent = default_home_content();
}

$pageTitle = 'Отзывы о РА «Жираф» — благодарность клиентов рекламному агентству';
$pageDescription = 'Отзывы реальных клиентов о работе рекламного агентства «Жираф». Типография, полиграфия, сувениры, наружная реклама.';
$canonicalUrl = seo_url('/reviews.php');

$reviewsStructuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'LocalBusiness',
    'name' => 'РА «Жираф»',
    'url' => seo_base_url(),
    'description' => 'Рекламное агентство полного цикла',
    'aggregateRating' => [
        '@type' => 'AggregateRating',
        'ratingValue' => count($reviews) > 0 ? (array_sum(array_column($reviews, 'rating')) / count($reviews)) : 0,
        'reviewCount' => count($reviews),
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
    <meta name="keywords" content="отзывы, рекламное агентство, типография, полиграфия, сувениры, Владимир" />
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
                  <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#like"></use></svg>
                  <?= htmlspecialchars($homeContent['nav_reviews_label'], ENT_QUOTES, 'UTF-8') ?>
                </a>
              </li>
            </ul>
            <div class="nav-actions">
              <a class="nav-vk" href="https://vk.com/giraf33" target="_blank" rel="noopener noreferrer" aria-label="ВКонтакте">
                <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#vk"></use></svg>
              </a>
              <button class="btn btn-nav" type="button" data-open-reviews-modal>
                <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#message"></use></svg>
                <?= htmlspecialchars($homeContent['reviews_cta_button'], ENT_QUOTES, 'UTF-8') ?>
              </button>
            </div>
          </div>
        </nav>

        <div class="catalog-hero">
          <h1><?= htmlspecialchars($homeContent['reviews_title'], ENT_QUOTES, 'UTF-8') ?></h1>
          <p class="section-subtitle"><?= nl2br(htmlspecialchars($homeContent['reviews_subtitle'], ENT_QUOTES, 'UTF-8')) ?></p>
        </div>
      </div>
    </header>

    <main>
      <section class="reviews-section section">
        <div class="container">
          <div class="reviews-header">
            <button type="button" class="btn btn-cta" data-open-reviews-modal>
              <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#message"></use></svg>
              <?= htmlspecialchars($homeContent['reviews_cta_button'], ENT_QUOTES, 'UTF-8') ?>
            </button>
          </div>

          <?php if (count($reviews) > 0): ?>
            <div class="reviews-grid">
              <?php foreach ($reviews as $review): ?>
                <article class="review-card">
                  <div class="review-rating">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                      <svg class="review-star <?= $i < (int)($review['rating'] ?? 0) ? 'is-filled' : '' ?>" aria-hidden="true"><use href="media/icons/star.svg"></use></svg>
                    <?php endfor; ?>
                  </div>
                  <p class="review-text"><?= htmlspecialchars($review['review_text'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                  <div class="review-meta">
                    <span class="review-author"><?= htmlspecialchars($review['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="review-date"><?= date('d.m.Y', strtotime($review['created_at'] ?? 'now')) ?></span>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="reviews-empty"><?= htmlspecialchars($homeContent['reviews_empty_message'], ENT_QUOTES, 'UTF-8') ?></p>
          <?php endif; ?>
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
        <div class="footer-map">
          <iframe
              src="https://yandex.ru/map-widget/v1/?um=constructor%3Ac62adae518cfdbeb0f97f16a1ceb395dff6e72ab40b8c5af261106d45bf89b65&amp;source=constructor"
              width="100%"
              height="400"
              frameborder="0">
          </iframe>
        </div>
      </div>
    </footer>

    <div class="modal-overlay" data-reviews-modal aria-hidden="true">
      <section class="manager-modal" role="dialog" aria-modal="true" aria-labelledby="reviews-modal-title">
        <button class="modal-close" type="button" data-close-reviews-modal aria-label="Закрыть форму">
          <span aria-hidden="true">✕</span>
        </button>
        <h2 id="reviews-modal-title"><?= htmlspecialchars($homeContent['reviews_cta_button'], ENT_QUOTES, 'UTF-8') ?></h2>
        <form class="manager-form" data-reviews-form>
          <div class="manager-form-fields">
            <label for="reviews-name">Ваше имя</label>
            <input id="reviews-name" type="text" name="name" placeholder="Иван Петров" required maxlength="100" />

            <label for="reviews-email">Email (опционально)</label>
            <input id="reviews-email" type="email" name="email" placeholder="your@email.com" maxlength="255" />

            <label>Оценка агентства</label>
            <div class="review-stars" data-review-stars>
              <input type="radio" id="star1" name="rating" value="1" required />
              <label for="star1" title="1 звезда">★</label>
              <input type="radio" id="star2" name="rating" value="2" />
              <label for="star2" title="2 звезды">★</label>
              <input type="radio" id="star3" name="rating" value="3" />
              <label for="star3" title="3 звезды">★</label>
              <input type="radio" id="star4" name="rating" value="4" />
              <label for="star4" title="4 звезды">★</label>
              <input type="radio" id="star5" name="rating" value="5" />
              <label for="star5" title="5 звёзд">★</label>
            </div>

            <label for="reviews-text">Ваш отзыв (опционально)</label>
            <textarea id="reviews-text" name="review_text" rows="5" placeholder="Поделитесь вашим опытом..." maxlength="1000"></textarea>
            <div class="char-counter"><span data-char-count>0</span>/1000</div>
          </div>
          <div class="manager-consent">
            <input id="reviews-consent" type="checkbox" name="privacy_consent" required />
            <label for="reviews-consent" class="manager-consent-label">
              Я согласен на
              <a href="privacy.php" target="_blank" rel="noopener noreferrer">обработку персональных данных</a>
            </label>
          </div>
          <p class="manager-form-success" data-reviews-success role="status" aria-live="polite" hidden><?= htmlspecialchars($homeContent['reviews_submit_success'], ENT_QUOTES, 'UTF-8') ?></p>
          <button class="btn manager-submit" type="submit">
            <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#message"></use></svg>Отправить отзыв
          </button>
        </form>
      </section>
    </div>

    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js" defer></script>
    <script src="app.js" defer></script>
  </body>
</html>

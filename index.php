<?php

declare(strict_types=1);

require __DIR__ . '/api/content.php';

$homeContent = default_home_content();

try {
    require_once __DIR__ . '/api/db.php';
    $homeContent = get_home_content(db());
} catch (Throwable $exception) {
    $homeContent = default_home_content();
}
?>
<!doctype html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>РА «Жираф» — Главная</title>
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
                  Главная
                </a>
              </li>
              <li>
                <a href="services.php">
                  <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#catalog"></use></svg>
                  Услуги
                </a>
              </li>
            </ul>
            <div class="nav-actions">
              <a class="nav-vk" href="#" aria-label="ВКонтакте">
                <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#vk"></use></svg>
              </a>
              <button class="btn btn-nav" type="button" data-open-manager-modal>
                <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#message"></use></svg>
                Написать нам
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
            <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#message"></use></svg>Связаться с нами
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
              <div class="media"><img src="media/img/Visitka.png" alt="Изготовление визиток" /></div>
              <h3>Изготовление визиток</h3>
              <a href="#" class="btn btn-card"><svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#catalog"></use></svg>Подробнее</a>
            </article>
            <article class="card">
              <div class="media"><img src="media/img/ShirPechat.png" alt="Широкоформатная печать" /></div>
              <h3>Широкоформатная печать</h3>
              <a href="#" class="btn btn-card"><svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#catalog"></use></svg>Подробнее</a>
            </article>
            <article class="card">
              <div class="media"><img src="media/img/Stendi.png" alt="Информационные стенды" /></div>
              <h3>Информационные стенды</h3>
              <a href="#" class="btn btn-card"><svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#catalog"></use></svg>Подробнее</a>
            </article>
            <article class="card">
              <div class="media"><img src="media/img/Reklama.png" alt="Наружная реклама" /></div>
              <h3>Наружная реклама</h3>
              <a href="#" class="btn btn-card"><svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#catalog"></use></svg>Подробнее</a>
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
          <div class="cards grid-4">
            <article class="portfolio-card">
              <div class="media"><img src="media/img/Stakan.png" alt="Чашки с печатью" /></div>
              <h3>Чашки с печатью</h3>
              <p>Аккуратный и качественный перенос фирменного стиля на сувенирную продукцию.</p>
            </article>
            <article class="portfolio-card">
              <div class="media"><img src="media/img/KachestvVisit.png" alt="Качественные визитки" /></div>
              <h3>Отличные визитки</h3>
              <p>Мы напечатали визитки для приёмщиков макулатуры: плотные, аккуратные</p>
            </article>
            <article class="portfolio-card">
              <div class="media"><img src="media/img/Knigi.png" alt="Важные книжки" /></div>
              <h3>Важные книжки</h3>
              <p>Каждая такая книжка аккуратная, плотная и собрана с вниманием</p>
            </article>
            <article class="portfolio-card">
              <div class="media"><img src="media/img/Stickers.png" alt="Объёмные стикеры" /></div>
              <h3>Объёмные стикеры</h3>
              <p>Яркий дизайн, плотные цвета и объём делают их заметными</p>
            </article>
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
          <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#message"></use></svg>Связаться с нами
        </button>

        <div class="footer-meta">
          <div>
            <p>E-mail: giraf33@mail.ru</p>
            <p>8 (492) 46-64-84</p>
            <p>8 (958) 510-64-84</p>
          </div>
          <div class="footer-logo"><img src="media/logo/Logo-Full.svg" alt="РА Жираф" /></div>
          <div>
            <p>Офис находится по адресу:</p>
            <p>г. Владимир, ул. Ставровская, д. 4</p>
            <p>ост. 1001 мелочь, парковка рядом с домом</p>
          </div>
        </div>
      </div>
    </footer>

    <div class="modal-overlay" data-manager-modal aria-hidden="true">
      <section class="manager-modal" role="dialog" aria-modal="true" aria-labelledby="manager-modal-title">
        <button class="modal-close" type="button" data-close-manager-modal aria-label="Закрыть форму">
          <span aria-hidden="true">✕</span>
        </button>
        <h2>Заявка менеджеру</h2>
        <p>Мы свяжемся с вами для уточнения заказа и ответим на все ваши вопросы</p>
        <form class="manager-form">
          <label for="manager-name">Представьтесь, пожалуйста</label>
          <input id="manager-name" type="text" name="name" placeholder="Ваше имя" autocomplete="name" required />

          <label for="manager-phone">Ваш номер телефона</label>
          <div class="manager-field" data-phone-field>
            <input
              id="manager-phone"
              type="tel"
              name="phone"
              placeholder="+7 900 000 00 00"
              autocomplete="tel"
              inputmode="tel"
              required
              aria-describedby="manager-phone-error"
            />
            <svg class="manager-field-error-icon" aria-hidden="true"><use href="media/icons/sprite.svg#error"></use></svg>
          </div>
          <p class="manager-field-error" id="manager-phone-error">Неверный формат номера</p>

          <label for="manager-comment">Комментарий к заявке или вопрос</label>
          <textarea id="manager-comment" name="comment" rows="3"></textarea>

          <button class="btn manager-submit" type="submit">
            <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#message"></use></svg>Отправить
          </button>
        </form>
      </section>
    </div>

    
      <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js" defer></script>
    <script src="app.js" defer></script>
  </body>
</html>






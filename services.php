<?php
$servicesData = [];
try {
    require_once __DIR__ . '/api/db.php';
    $stmt = db()->query('SELECT category, title FROM services ORDER BY category, id');
    $servicesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $exception) {
    $servicesData = [];
}
?>
<!doctype html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>РА «Жираф» — Каталог услуг</title>
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
            <span class="sr-only">����</span>
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

        <div class="catalog-hero">
          <h1>Наши основные услуги</h1>
          <p class="section-subtitle">Услуги рекламного агентства покрывают почти все возможные потребности</p>
        </div>
      </div>
    </header>

    <main>
      <div data-vue-catalog data-initial-services="<?= htmlspecialchars(json_encode($servicesData, JSON_UNESCAPED_UNICODE), ENT_QUOTES, "UTF-8") ?>" hidden></div>
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
          <h2>Не нашли нужную услугу?</h2>
          <p class="section-subtitle">Свяжитесь с нами для уточнения</p>
          <button type="button" class="btn btn-contact" data-open-manager-modal>
            <svg class="icon" aria-hidden="true"><use href="media/icons/sprite.svg#message"></use></svg>Связаться с нами
          </button>
        </div>
      </section>
    </main>

    <footer class="catalog-footer">
      <div class="container footer-meta">
        <div>
          <p>E-mail: giraf33@mail.ru</p>
          <p>8 (4922) 46-64-84</p>
          <p>8 (958) 510-64-84</p>
        </div>
        <div class="footer-logo"><img src="media/logo/Logo-Full.svg" alt="РА Жираф" /></div>
        <div>
          <p>Офис находится по адресу:</p>
          <p>г. Владимир, ул. Ставровская, д. 4</p>
          <p>ост. 1001 мелочь, парковка рядом с домом</p>
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




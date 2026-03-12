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
          <ul class="menu">
            <li><a href="index.php"><img class="icon" src="media/icons/Home.svg" alt="" />Главная</a></li>
            <li><a href="services.php"><img class="icon" src="media/icons/Catalog.svg" alt="" />Услуги</a></li>
          </ul>
          <button class="btn btn-nav" type="button" data-open-manager-modal>
            <img class="icon" src="media/icons/Message.svg" alt="" />
            Написать нам
          </button>
        </nav>

        <div class="hero-content">
          <h1>Рекламное агентство полного цикла</h1>
          <p class="hero-text">
            Мы предлагаем свои производственные и рекламные услуги на территории всей Владимирской области
          </p>
          <button class="btn btn-contact" type="button" data-open-manager-modal>
            <img class="icon" src="media/icons/Message.svg" alt="" />Связаться с нами
          </button>
        </div>
        <a href="#services" class="hero-down"><img src="media/icons/Down.svg" alt="Вниз" /></a>
      </div>
    </header>

    <main>
      <section id="services" class="section services">
        <div class="container">
          <h2>Полный спектр услуг</h2>
          <p class="section-subtitle">Услуги рекламного агентства покрывают почти все возможные потребности</p>
          <div class="cards grid-4">
            <article class="card">
              <div class="media"><img src="media/img/Visitka.png" alt="Изготовление визиток" /></div>
              <h3>Изготовление визиток</h3>
              <a href="#" class="btn btn-card"><img class="icon" src="media/icons/Catalog.svg" alt="" />Подробнее</a>
            </article>
            <article class="card">
              <div class="media"><img src="media/img/ShirPechat.png" alt="Широкоформатная печать" /></div>
              <h3>Широкоформатная печать</h3>
              <a href="#" class="btn btn-card"><img class="icon" src="media/icons/Catalog.svg" alt="" />Подробнее</a>
            </article>
            <article class="card">
              <div class="media"><img src="media/img/Stendi.png" alt="Информационные стенды" /></div>
              <h3>Информационные стенды</h3>
              <a href="#" class="btn btn-card"><img class="icon" src="media/icons/Catalog.svg" alt="" />Подробнее</a>
            </article>
            <article class="card">
              <div class="media"><img src="media/img/Reklama.png" alt="Наружная реклама" /></div>
              <h3>Наружная реклама</h3>
              <a href="#" class="btn btn-card"><img class="icon" src="media/icons/Catalog.svg" alt="" />Подробнее</a>
            </article>
          </div>
        </div>
      </section>

      <section class="section highlights">
        <div class="container">
          <h2>Действуем в интересах клиента</h2>
          <div class="feature-row">
            <div class="feature">ВСЕГДА НАЦЕЛЕНЫ НА&nbsp;КАЧЕСТВО <img class="icon" src="media/icons/Like.svg" alt="" /></div>
            <div class="feature">ОПЕРАТИВНОЕ ИЗГОТОВЛЕНИЕ <img class="icon" src="media/icons/Speed.svg" alt="" /></div>
            <div class="feature">НАХОДИМСЯ ПРЯМО В&nbsp;ЦЕНТРЕ ГОРОДА <img class="icon" src="media/icons/Point.svg" alt="" /></div>
          </div>
        </div>
      </section>

      <section class="section portfolio">
        <div class="container">
          <h2>Вот что мы сделали</h2>
          <p class="section-subtitle">Нашим ориентиром всегда было и остаётся качество</p>
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
        <h2>Мы готовы решить вашу проблему</h2>
        <p>
          Вам не обязательно ехать в офис рекламного агентства — можно оформить заказ дистанционно по удобному
          каналу связи
        </p>
        <button type="button" class="btn btn-contact" data-open-manager-modal>
          <img class="icon" src="media/icons/Message.svg" alt="" />Связаться с нами
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
            <img class="manager-field-error-icon" src="media/icons/Error.svg" alt="" aria-hidden="true" />
          </div>
          <p class="manager-field-error" id="manager-phone-error">Неверный формат номера</p>

          <label for="manager-comment">Комментарий к заявке или вопрос</label>
          <textarea id="manager-comment" name="comment" rows="3"></textarea>

          <button class="btn manager-submit" type="submit">
            <img class="icon" src="media/icons/Message.svg" alt="" />Отправить
          </button>
        </form>
      </section>
    </div>

    
      <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js" defer></script>
    <script src="app.js" defer></script>
  </body>
</html>

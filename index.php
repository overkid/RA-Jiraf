<?php
?><!doctype html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>РА «Жираф» — Vue + PHP + MySQL</title>
    <link rel="icon" href="media/favicon.ico" type="image/x-icon" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&family=Nunito:wght@700;800&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="styles.css" />
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js" defer></script>
    <script src="animations.js" defer></script>
    <script src="app.js" defer></script>
  </head>
  <body>
    <div id="app">
      <header class="hero">
        <div class="container">
          <nav class="top-nav">
            <a class="logo" href="#"><img src="media/logo/Logo-Full.svg" alt="РА Жираф" /></a>
            <ul class="menu">
              <li><a href="#"><img class="icon" src="media/icons/Home.svg" alt="" />Главная</a></li>
              <li><a href="#services"><img class="icon" src="media/icons/Catalog.svg" alt="" />Услуги</a></li>
            </ul>
            <button class="btn btn-nav" type="button" @click="openModal">
              <img class="icon" src="media/icons/Message.svg" alt="" />
              Написать нам
            </button>
          </nav>

          <div class="hero-content">
            <h1>Рекламное агентство полного цикла</h1>
            <p class="hero-text">Мы предлагаем свои производственные и рекламные услуги на территории всей Владимирской области</p>
            <button class="btn btn-contact" type="button" @click="openModal">
              <img class="icon" src="media/icons/Message.svg" alt="" />Связаться с нами
            </button>
          </div>
          <a href="#services" class="hero-down"><img src="media/icons/Down.svg" alt="Вниз" /></a>
        </div>
      </header>

      <main>
        <section id="services" class="section services-catalog">
          <div class="container">
            <h2>Каталог услуг из MySQL</h2>
            <p class="section-subtitle">Данные загружаются с PHP API</p>

            <div class="catalog-tabs" role="tablist" aria-label="Категории услуг">
              <button
                v-for="category in categories"
                :key="category"
                class="catalog-tab"
                :class="{ 'is-active': activeCategory === category }"
                type="button"
                @click="activeCategory = category"
              >
                {{ category }}
              </button>
            </div>

            <div class="catalog-group" v-if="activeServices.length">
              <div class="catalog-grid">
                <article class="service-tile" v-for="service in activeServices" :key="service.id">
                  <h3>{{ service.title }}</h3>
                  <p>{{ service.description }}</p>
                </article>
              </div>
            </div>
            <p v-else class="section-subtitle">Пока нет услуг в выбранной категории.</p>
            <p class="section-subtitle" v-if="errorMessage">{{ errorMessage }}</p>
          </div>
        </section>
      </main>

      <footer class="footer section">
        <div class="container footer-grid">
          <div>
            <p>📞 +7 (4922) 53-76-45</p>
            <p>✉️ jirafreklama@mail.ru</p>
          </div>
          <div class="footer-logo"><img src="media/logo/Logo-Full.svg" alt="РА Жираф" /></div>
          <div>
            <p>г. Владимир, ул. Ставровская, д. 4</p>
          </div>
        </div>
      </footer>

      <div class="modal-overlay" :class="{ 'is-open': isModalOpen }" :aria-hidden="(!isModalOpen).toString()" @click.self="closeModal">
        <section class="manager-modal" role="dialog" aria-modal="true" aria-labelledby="manager-modal-title">
          <button class="modal-close" type="button" @click="closeModal" aria-label="Закрыть форму">
            <span aria-hidden="true">✕</span>
          </button>
          <h2 id="manager-modal-title">Заявка менеджеру</h2>
          <p>Мы свяжемся с вами для уточнения заказа и ответим на все ваши вопросы</p>

          <form class="manager-form" @submit.prevent="submitRequest">
            <label for="manager-name">Представьтесь, пожалуйста</label>
            <input id="manager-name" type="text" v-model="form.name" required />

            <label for="manager-phone">Ваш номер телефона</label>
            <input id="manager-phone" type="tel" v-model="form.phone" placeholder="+7 900 000 00 00" required />

            <label for="manager-comment">Комментарий к заявке или вопрос</label>
            <textarea id="manager-comment" rows="3" v-model="form.comment"></textarea>

            <button class="btn manager-submit" type="submit">
              <img class="icon" src="media/icons/Message.svg" alt="" />Отправить
            </button>
            <p class="section-subtitle" v-if="formStatus">{{ formStatus }}</p>
          </form>
        </section>
      </div>
    </div>
  </body>
</html>

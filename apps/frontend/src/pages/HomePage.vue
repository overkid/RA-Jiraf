<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../composables/api';

const contacts = ref(null);
const catalog = ref([]);

const serviceImages = {
  'Изготовление визиток': '/media/img/Visitka.png',
  'Широкоформатная печать': '/media/img/ShirPechat.png',
  'Информационные стенды': '/media/img/Stendi.png',
  'Наружная реклама': '/media/img/Reklama.png'
};

onMounted(async () => {
  contacts.value = await api.getContacts();
  catalog.value = await api.getCatalog();
});
</script>

<template>
  <header class="hero">
    <div class="container">
      <nav class="top-nav">
        <router-link class="logo" to="/"><img src="/media/logo/Logo-Full.svg" alt="РА Жираф" /></router-link>
        <ul class="menu">
          <li><router-link to="/"><img class="icon" src="/media/icons/Home.svg" alt="" />Главная</router-link></li>
          <li><router-link to="/services"><img class="icon" src="/media/icons/Catalog.svg" alt="" />Услуги</router-link></li>
        </ul>
      </nav>
      <div class="hero-content">
        <h1>Рекламное агентство полного цикла</h1>
        <p class="hero-text">Мы предлагаем свои производственные и рекламные услуги на территории всей Владимирской области</p>
      </div>
    </div>
  </header>

  <main>
    <section class="section services">
      <div class="container">
        <h2>Полный спектр услуг</h2>
        <p class="section-subtitle">Услуги рекламного агентства покрывают почти все возможные потребности</p>
        <div class="cards grid-4">
          <article class="card" v-for="item in catalog.flatMap((c) => c.services).slice(0,4)" :key="item.id">
            <div class="media"><img :src="serviceImages[item.title] || '/media/img/Visitka.png'" :alt="item.title" /></div>
            <h3>{{ item.title }}</h3>
            <router-link to="/services" class="btn btn-card"><img class="icon" src="/media/icons/Catalog.svg" alt="" />Подробнее</router-link>
          </article>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer-cta" v-if="contacts">
    <div class="container">
      <h2>Мы готовы решить вашу проблему</h2>
      <div class="footer-meta">
        <div>
          <p>E-mail: {{ contacts.email }}</p>
          <p>{{ contacts.phone_main }}</p>
          <p>{{ contacts.phone_alt }}</p>
        </div>
        <div class="footer-logo"><img src="/media/logo/Logo-Full.svg" alt="РА Жираф" /></div>
        <div>
          <p>{{ contacts.address_line1 }}</p>
          <p>{{ contacts.address_line2 }}</p>
          <p>{{ contacts.address_line3 }}</p>
        </div>
      </div>
    </div>
  </footer>
</template>

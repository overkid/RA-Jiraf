<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { api } from '../composables/api';

const catalog = ref([]);
const activeTab = ref('print');
const contacts = ref(null);

onMounted(async () => {
  document.body.classList.add('catalog-page');
  catalog.value = await api.getCatalog();
  contacts.value = await api.getContacts();
});

onUnmounted(() => {
  document.body.classList.remove('catalog-page');
});
</script>

<template>
  <header class="catalog-header section">
    <div class="container">
      <nav class="top-nav top-nav-catalog">
        <router-link class="logo" to="/"><img src="/media/logo/Logo-Full.svg" alt="РА Жираф" /></router-link>
        <ul class="menu">
          <li><router-link to="/"><img class="icon" src="/media/icons/Home.svg" alt="" />Главная</router-link></li>
          <li><router-link to="/services"><img class="icon" src="/media/icons/Catalog.svg" alt="" />Услуги</router-link></li>
        </ul>
      </nav>
      <div class="catalog-hero">
        <h1>Наши основные услуги</h1>
        <p class="section-subtitle">Услуги рекламного агентства покрывают почти все возможные потребности</p>
      </div>
    </div>
  </header>

  <main>
    <section class="services-catalog section">
      <div class="container">
        <div class="catalog-tabs" role="tablist" aria-label="Категории услуг">
          <button
            v-for="category in catalog"
            :key="category.id"
            class="catalog-tab"
            :class="{ 'is-active': activeTab === category.slug }"
            @click="activeTab = category.slug"
          >
            {{ category.title }}
          </button>
        </div>

        <div class="catalog-group" v-for="category in catalog" :key="category.id" :hidden="activeTab !== category.slug">
          <div class="catalog-grid">
            <article class="service-tile" v-for="service in category.services" :key="service.id">
              <h3>{{ service.title }}</h3>
              <button class="btn btn-disabled" disabled>Подробнее</button>
            </article>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="catalog-footer" v-if="contacts">
    <div class="container footer-meta">
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
  </footer>
</template>

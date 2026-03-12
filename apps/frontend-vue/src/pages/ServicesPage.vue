<script setup>
import { onMounted, ref } from 'vue'
import { api } from '../api'

const categories = ref([])

onMounted(async () => {
  categories.value = await api('/api/public/services')
})
</script>

<template>
  <section>
    <h2>Каталог услуг</h2>
    <div v-for="cat in categories" :key="cat.id" class="card">
      <h3>{{ cat.name }}</h3>
      <div v-for="srv in cat.services" :key="srv.id" class="service-item">
        <img v-if="srv.image_path" :src="srv.image_path" alt="service" />
        <div>
          <h4>{{ srv.title }}</h4>
          <p>{{ srv.short_description }}</p>
          <p v-if="srv.price_from"><b>Цена от:</b> {{ srv.price_from }} ₽</p>
        </div>
      </div>
    </div>
  </section>
</template>

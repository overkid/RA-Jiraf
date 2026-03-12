<script setup>
import { onMounted, ref } from 'vue'
import { api } from '../api'

const categories = ref([])

const fallbackCategories = [
  {
    id: 1,
    name: 'Полиграфия',
    services: [
      { id: 1, title: 'Визитки', short_description: 'Печать визиток с вашим дизайном', price_from: 500 },
      { id: 2, title: 'Широкоформатная печать', short_description: 'Баннеры, постеры, плакаты', price_from: 1500 }
    ]
  },
  {
    id: 2,
    name: 'Сувенирная продукция',
    services: [
      { id: 3, title: 'Печать на кружках', short_description: 'Именные кружки для бизнеса и подарков', price_from: 700 }
    ]
  }
]

onMounted(async () => {
  try {
    categories.value = await api('/api/public/services')
  } catch {
    categories.value = fallbackCategories
  }
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

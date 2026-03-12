<script setup>
import { onMounted, ref } from 'vue'
import { api } from '../api'

const contact = ref(null)
const form = ref({ name: '', phone: '', email: '', message: '' })
const message = ref('')

const fallbackContact = {
  company_name: 'RA Jiraf',
  phone: '+7 (900) 000-00-00',
  email: 'info@rajiraf.ru',
  address: 'г. Омск, ул. Примерная, 1',
  work_hours: 'Пн-Пт 09:00-18:00'
}

onMounted(async () => {
  try {
    contact.value = await api('/api/public/contacts')
  } catch {
    contact.value = fallbackContact
  }
})

async function sendRequest() {
  try {
    await api('/api/public/requests', {
      method: 'POST',
      body: JSON.stringify(form.value)
    })
    message.value = 'Заявка отправлена!'
  } catch {
    message.value = 'Backend не запущен: форма работает в демо-режиме.'
  }
  form.value = { name: '', phone: '', email: '', message: '' }
}
</script>

<template>
  <section>
    <h2>Рекламное агентство полного цикла</h2>
    <p>Перенесенная версия сайта на Vue + FastAPI.</p>
  </section>

  <section v-if="contact" class="card">
    <h3>Контакты</h3>
    <p><b>Компания:</b> {{ contact.company_name }}</p>
    <p><b>Телефон:</b> {{ contact.phone }}</p>
    <p><b>Email:</b> {{ contact.email }}</p>
    <p><b>Адрес:</b> {{ contact.address }}</p>
    <p><b>Время работы:</b> {{ contact.work_hours }}</p>
  </section>

  <section class="card">
    <h3>Оставить заявку</h3>
    <form @submit.prevent="sendRequest" class="form-grid">
      <input v-model="form.name" placeholder="Ваше имя" required />
      <input v-model="form.phone" placeholder="Телефон" required />
      <input v-model="form.email" placeholder="Email" />
      <textarea v-model="form.message" placeholder="Сообщение" required />
      <button type="submit">Отправить</button>
    </form>
    <p v-if="message">{{ message }}</p>
  </section>
</template>

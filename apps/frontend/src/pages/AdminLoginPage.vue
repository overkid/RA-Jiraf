<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { api } from '../composables/api';

const login = ref('');
const password = ref('');
const error = ref('');
const router = useRouter();

const submit = async () => {
  try {
    error.value = '';
    const { token } = await api.login(login.value, password.value);
    localStorage.setItem('adminToken', token);
    router.push('/admin');
  } catch (e) {
    error.value = e.message;
  }
};
</script>

<template>
  <main class="section">
    <div class="container" style="max-width: 480px;">
      <h1>Вход в админку</h1>
      <p class="section-subtitle">Прямая ссылка: /admin/login</p>
      <form class="manager-form" @submit.prevent="submit">
        <label>Логин</label>
        <input v-model="login" type="text" required />
        <label>Пароль</label>
        <input v-model="password" type="password" required />
        <p v-if="error" style="color: #dc2626;">{{ error }}</p>
        <button class="btn manager-submit" type="submit">Войти</button>
      </form>
    </div>
  </main>
</template>

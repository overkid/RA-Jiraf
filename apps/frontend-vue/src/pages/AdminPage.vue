<script setup>
import { computed, onMounted, ref } from 'vue'
import { api, authHeader } from '../api'

const login = ref('admin')
const password = ref('admin123')
const error = ref('')
const contacts = ref({ company_name: '', phone: '', email: '', address: '', work_hours: '' })
const categories = ref([])
const requests = ref([])

const newCategory = ref({ name: '', sort_order: 0, is_active: true })
const newService = ref({ category_id: '', title: '', short_description: '', price_from: '', image_path: '', is_active: true })

const isAuth = computed(() => !!localStorage.getItem('adminToken'))

async function doLogin() {
  error.value = ''
  try {
    const data = await api('/api/admin/login', {
      method: 'POST',
      body: JSON.stringify({ login: login.value, password: password.value })
    })
    localStorage.setItem('adminToken', data.access_token)
    await loadAdminData()
  } catch {
    error.value = 'Неверный логин или пароль'
  }
}

async function loadAdminData() {
  contacts.value = await api('/api/admin/contacts', { headers: authHeader() })
  categories.value = await api('/api/admin/categories', { headers: authHeader() })
  requests.value = await api('/api/admin/requests', { headers: authHeader() })
}

async function saveContacts() {
  await api('/api/admin/contacts', {
    method: 'PUT',
    headers: authHeader(),
    body: JSON.stringify(contacts.value)
  })
  await loadAdminData()
}

async function addCategory() {
  await api('/api/admin/categories', {
    method: 'POST',
    headers: authHeader(),
    body: JSON.stringify({ ...newCategory.value, sort_order: Number(newCategory.value.sort_order || 0) })
  })
  newCategory.value = { name: '', sort_order: 0, is_active: true }
  await loadAdminData()
}

async function saveCategory(cat) {
  await api(`/api/admin/categories/${cat.id}`, {
    method: 'PUT',
    headers: authHeader(),
    body: JSON.stringify({ name: cat.name, sort_order: Number(cat.sort_order || 0), is_active: !!cat.is_active })
  })
  await loadAdminData()
}

async function addService() {
  await api('/api/admin/services', {
    method: 'POST',
    headers: authHeader(),
    body: JSON.stringify({
      ...newService.value,
      category_id: Number(newService.value.category_id),
      price_from: newService.value.price_from ? Number(newService.value.price_from) : null
    })
  })
  newService.value = { category_id: '', title: '', short_description: '', price_from: '', image_path: '', is_active: true }
  await loadAdminData()
}

async function saveService(srv, categoryId) {
  await api(`/api/admin/services/${srv.id}`, {
    method: 'PUT',
    headers: authHeader(),
    body: JSON.stringify({
      category_id: categoryId,
      title: srv.title,
      short_description: srv.short_description,
      price_from: srv.price_from ? Number(srv.price_from) : null,
      image_path: srv.image_path || null,
      is_active: !!srv.is_active
    })
  })
  await loadAdminData()
}

async function deleteService(srvId) {
  await api(`/api/admin/services/${srvId}`, {
    method: 'DELETE',
    headers: authHeader()
  })
  await loadAdminData()
}

async function updateRequest(req) {
  await api(`/api/admin/requests/${req.id}`, {
    method: 'PATCH',
    headers: authHeader(),
    body: JSON.stringify({ status: req.status, manager_comment: req.manager_comment || '' })
  })
  await loadAdminData()
}

onMounted(async () => {
  if (isAuth.value) await loadAdminData()
})
</script>

<template>
  <section>
    <h2>Админка</h2>

    <div v-if="!isAuth" class="card">
      <h3>Вход</h3>
      <form class="form-grid" @submit.prevent="doLogin">
        <input v-model="login" placeholder="Логин" required />
        <input v-model="password" type="password" placeholder="Пароль" required />
        <button type="submit">Войти</button>
      </form>
      <p v-if="error">{{ error }}</p>
    </div>

    <div v-else>
      <div class="card">
        <h3>Контактная информация</h3>
        <form class="form-grid" @submit.prevent="saveContacts">
          <input v-model="contacts.company_name" placeholder="Название компании" />
          <input v-model="contacts.phone" placeholder="Телефон" />
          <input v-model="contacts.email" placeholder="Email" />
          <input v-model="contacts.address" placeholder="Адрес" />
          <input v-model="contacts.work_hours" placeholder="Часы работы" />
          <button type="submit">Сохранить контакты</button>
        </form>
      </div>

      <div class="card">
        <h3>Категории услуг</h3>
        <form class="form-grid" @submit.prevent="addCategory">
          <input v-model="newCategory.name" placeholder="Название категории" required />
          <input v-model="newCategory.sort_order" type="number" placeholder="Порядок" />
          <label><input v-model="newCategory.is_active" type="checkbox" /> активна</label>
          <button type="submit">Добавить категорию</button>
        </form>

        <div v-for="cat in categories" :key="cat.id" class="card">
          <input v-model="cat.name" />
          <input v-model="cat.sort_order" type="number" />
          <label><input v-model="cat.is_active" type="checkbox" /> активна</label>
          <button @click="saveCategory(cat)">Сохранить категорию</button>

          <h4>Услуги в категории</h4>
          <div v-for="srv in cat.services" :key="srv.id" class="request-item">
            <input v-model="srv.title" placeholder="Название услуги" />
            <input v-model="srv.short_description" placeholder="Краткое описание" />
            <input v-model="srv.price_from" type="number" placeholder="Цена от" />
            <input v-model="srv.image_path" placeholder="Путь к картинке" />
            <button @click="saveService(srv, cat.id)">Сохранить услугу</button>
            <button @click="deleteService(srv.id)">Удалить услугу</button>
          </div>
        </div>

        <h4>Добавить услугу</h4>
        <form class="form-grid" @submit.prevent="addService">
          <select v-model="newService.category_id" required>
            <option disabled value="">Выберите категорию</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
          <input v-model="newService.title" placeholder="Название услуги" required />
          <input v-model="newService.short_description" placeholder="Краткое описание" required />
          <input v-model="newService.price_from" type="number" placeholder="Цена от" />
          <input v-model="newService.image_path" placeholder="Путь к картинке" />
          <label><input v-model="newService.is_active" type="checkbox" /> активна</label>
          <button type="submit">Добавить услугу</button>
        </form>
      </div>

      <div class="card">
        <h3>Заявки пользователей</h3>
        <div v-for="req in requests" :key="req.id" class="request-item">
          <p><b>{{ req.name }}</b> ({{ req.phone }})</p>
          <p>{{ req.message }}</p>
          <select v-model="req.status">
            <option value="new">new</option>
            <option value="in_progress">in_progress</option>
            <option value="done">done</option>
            <option value="archived">archived</option>
          </select>
          <input v-model="req.manager_comment" placeholder="Комментарий" />
          <button @click="updateRequest(req)">Обновить</button>
        </div>
      </div>
    </div>
  </section>
</template>

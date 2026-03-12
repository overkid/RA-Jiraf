<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { api } from '../composables/api';

const router = useRouter();
const catalog = ref([]);
const contacts = ref(null);
const message = ref('');

const loadData = async () => {
  try {
    catalog.value = await api.getCatalog(true);
    contacts.value = await api.getContacts();
  } catch {
    router.push('/admin/login');
  }
};

const saveService = async (service) => {
  await api.updateService(service.id, {
    title: service.title,
    description: service.description,
    categoryId: service.category_id,
    isActive: service.is_active
  });
  message.value = 'Услуга сохранена';
};

const saveContacts = async () => {
  await api.updateContacts({
    email: contacts.value.email,
    phoneMain: contacts.value.phone_main,
    phoneAlt: contacts.value.phone_alt,
    addressLine1: contacts.value.address_line1,
    addressLine2: contacts.value.address_line2,
    addressLine3: contacts.value.address_line3
  });
  message.value = 'Контакты сохранены';
};

onMounted(loadData);
</script>

<template>
  <main class="section">
    <div class="container">
      <h1>Админка</h1>
      <p class="section-subtitle">Редактирование услуг и контактов</p>
      <p v-if="message" style="color: #15803d;">{{ message }}</p>

      <section v-if="catalog.length">
        <h2>Услуги каталога</h2>
        <div v-for="category in catalog" :key="category.id" style="margin-bottom: 24px;">
          <h3>{{ category.title }}</h3>
          <article v-for="service in category.services" :key="service.id" class="service-tile" style="margin-bottom: 12px;">
            <input v-model="service.title" type="text" style="width: 100%; margin-bottom: 8px;" />
            <textarea v-model="service.description" rows="2" style="width: 100%; margin-bottom: 8px;"></textarea>
            <label><input v-model="service.is_active" :true-value="1" :false-value="0" type="checkbox" /> Активна</label>
            <div><button class="btn btn-card" @click="saveService(service)">Сохранить услугу</button></div>
          </article>
        </div>
      </section>

      <section v-if="contacts">
        <h2>Контактные данные</h2>
        <input v-model="contacts.email" type="text" style="width: 100%; margin-bottom: 8px;" />
        <input v-model="contacts.phone_main" type="text" style="width: 100%; margin-bottom: 8px;" />
        <input v-model="contacts.phone_alt" type="text" style="width: 100%; margin-bottom: 8px;" />
        <input v-model="contacts.address_line1" type="text" style="width: 100%; margin-bottom: 8px;" />
        <input v-model="contacts.address_line2" type="text" style="width: 100%; margin-bottom: 8px;" />
        <input v-model="contacts.address_line3" type="text" style="width: 100%; margin-bottom: 8px;" />
        <button class="btn btn-contact" @click="saveContacts">Сохранить контакты</button>
      </section>
    </div>
  </main>
</template>

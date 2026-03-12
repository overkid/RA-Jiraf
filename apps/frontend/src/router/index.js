import { createRouter, createWebHistory } from 'vue-router';
import HomePage from '../pages/HomePage.vue';
import ServicesPage from '../pages/ServicesPage.vue';
import AdminLoginPage from '../pages/AdminLoginPage.vue';
import AdminPanelPage from '../pages/AdminPanelPage.vue';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: HomePage },
    { path: '/services', component: ServicesPage },
    { path: '/admin/login', component: AdminLoginPage },
    { path: '/admin', component: AdminPanelPage }
  ]
});

export default router;

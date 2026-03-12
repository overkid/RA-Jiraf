const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:3001';

export const api = {
  async getCatalog(admin = false) {
    const token = localStorage.getItem('adminToken');
    const url = admin ? '/api/admin/catalog' : '/api/catalog';
    const response = await fetch(`${API_URL}${url}`, {
      headers: token ? { Authorization: `Bearer ${token}` } : {}
    });
    if (!response.ok) throw new Error('Ошибка загрузки каталога');
    return response.json();
  },
  async getContacts() {
    const response = await fetch(`${API_URL}/api/contacts`);
    if (!response.ok) throw new Error('Ошибка загрузки контактов');
    return response.json();
  },
  async login(login, password) {
    const response = await fetch(`${API_URL}/api/admin/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ login, password })
    });
    if (!response.ok) throw new Error('Неверный логин или пароль');
    return response.json();
  },
  async updateService(id, payload) {
    const token = localStorage.getItem('adminToken');
    const response = await fetch(`${API_URL}/api/admin/services/${id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`
      },
      body: JSON.stringify(payload)
    });
    if (!response.ok) throw new Error('Ошибка обновления услуги');
  },
  async updateContacts(payload) {
    const token = localStorage.getItem('adminToken');
    const response = await fetch(`${API_URL}/api/admin/contacts`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`
      },
      body: JSON.stringify(payload)
    });
    if (!response.ok) throw new Error('Ошибка обновления контактов');
  }
};

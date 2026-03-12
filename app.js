window.addEventListener('DOMContentLoaded', () => {
  const { createApp } = Vue;

  createApp({
    data() {
      return {
        services: [],
        activeCategory: '',
        isModalOpen: false,
        errorMessage: '',
        formStatus: '',
        form: {
          name: '',
          phone: '',
          comment: ''
        }
      };
    },
    computed: {
      categories() {
        return [...new Set(this.services.map((service) => service.category))];
      },
      activeServices() {
        return this.services.filter((service) => service.category === this.activeCategory);
      }
    },
    methods: {
      async loadServices() {
        try {
          const response = await fetch('api/services.php');
          if (!response.ok) {
            throw new Error('Не удалось загрузить список услуг');
          }

          const payload = await response.json();
          this.services = payload.services ?? [];
          this.activeCategory = this.categories[0] ?? '';
        } catch (error) {
          this.errorMessage = `${error.message}. Проверьте MySQL и config/database.php`;
        }
      },
      openModal() {
        this.isModalOpen = true;
        document.body.classList.add('modal-open');
      },
      closeModal() {
        this.isModalOpen = false;
        document.body.classList.remove('modal-open');
      },
      async submitRequest() {
        this.formStatus = '';

        try {
          const response = await fetch('api/requests.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(this.form)
          });

          const payload = await response.json();
          if (!response.ok) {
            throw new Error(payload.message || 'Ошибка отправки заявки');
          }

          this.formStatus = 'Заявка отправлена ✅';
          this.form = { name: '', phone: '', comment: '' };
        } catch (error) {
          this.formStatus = `Ошибка: ${error.message}`;
        }
      }
    },
    mounted() {
      this.loadServices();
    }
  }).mount('#app');
});

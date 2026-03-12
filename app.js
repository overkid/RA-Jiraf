(() => {
  const phoneDigitsCount = 10;
  const phonePrefix = '+7';

  const formatPhoneValue = (rawValue) => {
    const digitsOnly = String(rawValue || '').replace(/\D/g, '');
    const localNumber = digitsOnly.replace(/^7|^8/, '').slice(0, phoneDigitsCount);

    let formattedValue = phonePrefix;

    if (localNumber.length > 0) formattedValue += ` ${localNumber.slice(0, 3)}`;
    if (localNumber.length > 3) formattedValue += ` ${localNumber.slice(3, 6)}`;
    if (localNumber.length > 6) formattedValue += ` ${localNumber.slice(6, 8)}`;
    if (localNumber.length > 8) formattedValue += ` ${localNumber.slice(8, 10)}`;

    return { formattedValue, localDigitsLength: localNumber.length };
  };

  const setupCatalogTabs = () => {
    const categoryTabs = document.querySelectorAll('[data-category-tab]');
    const categoryGroups = document.querySelectorAll('[data-category]');

    if (!categoryTabs.length || !categoryGroups.length) return;

    categoryTabs.forEach((tab) => {
      tab.addEventListener('click', () => {
        const selectedCategory = tab.dataset.categoryTab;

        categoryTabs.forEach((item) => item.classList.toggle('is-active', item === tab));
        categoryGroups.forEach((group) => {
          group.hidden = group.dataset.category !== selectedCategory;
        });
      });
    });
  };

  const setupManagerModal = () => {
    const modalOverlay = document.querySelector('[data-manager-modal]');
    const openModalButtons = document.querySelectorAll('[data-open-manager-modal]');
    const closeModalButton = document.querySelector('[data-close-manager-modal]');
    const managerForm = document.querySelector('.manager-form');
    const phoneInput = document.querySelector('#manager-phone');
    const phoneField = document.querySelector('[data-phone-field]');

    if (!modalOverlay || !closeModalButton || !managerForm || !phoneInput || !phoneField) return;

    const setPhoneErrorState = (hasError) => {
      phoneField.classList.toggle('is-error', hasError);
      phoneInput.setAttribute('aria-invalid', String(hasError));
    };

    const validatePhone = () => {
      const { localDigitsLength } = formatPhoneValue(phoneInput.value);
      const isValid = localDigitsLength === phoneDigitsCount;
      setPhoneErrorState(!isValid);
      return isValid;
    };

    const normalizePhoneInput = (value) => {
      const { formattedValue } = formatPhoneValue(value);
      phoneInput.value = formattedValue;
    };

    const openModal = () => {
      modalOverlay.classList.add('is-open');
      document.body.classList.add('modal-open');
      modalOverlay.setAttribute('aria-hidden', 'false');
    };

    const closeModal = () => {
      modalOverlay.classList.remove('is-open');
      document.body.classList.remove('modal-open');
      modalOverlay.setAttribute('aria-hidden', 'true');
    };

    normalizePhoneInput(phoneInput.value);

    phoneInput.addEventListener('focus', () => normalizePhoneInput(phoneInput.value));
    phoneInput.addEventListener('input', (event) => {
      normalizePhoneInput(event.target.value);
      setPhoneErrorState(false);
    });
    phoneInput.addEventListener('blur', validatePhone);

    openModalButtons.forEach((button) => button.addEventListener('click', openModal));
    closeModalButton.addEventListener('click', closeModal);

    modalOverlay.addEventListener('click', (event) => {
      if (event.target === modalOverlay) closeModal();
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && modalOverlay.classList.contains('is-open')) {
        closeModal();
      }
    });

    managerForm.addEventListener('submit', async (event) => {
      event.preventDefault();

      if (!validatePhone()) {
        phoneInput.focus();
        return;
      }

      const formData = new FormData(managerForm);
      const payload = {
        name: String(formData.get('name') || '').trim(),
        phone: String(formData.get('phone') || '').trim(),
        comment: String(formData.get('comment') || '').trim()
      };

      const submitButton = managerForm.querySelector('button[type="submit"]');
      const oldButtonText = submitButton ? submitButton.textContent : '';

      if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = 'Отправляем...';
      }

      try {
        const response = await fetch('api/requests.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });

        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
          throw new Error(data.message || 'Не удалось отправить заявку');
        }

        managerForm.reset();
        normalizePhoneInput('');
        setPhoneErrorState(false);
        closeModal();
      } catch (error) {
        window.alert(error.message || 'Ошибка отправки заявки');
      } finally {
        if (submitButton) {
          submitButton.disabled = false;
          submitButton.textContent = oldButtonText;
        }
      }
    });
  };

  const setupVueCatalogSync = () => {
    if (!window.Vue) return;

    const container = document.querySelector('[data-vue-catalog]');
    if (!container) return;

    const initialData = JSON.parse(container.getAttribute('data-initial-services') || '[]');

    const { createApp } = window.Vue;
    createApp({
      data() {
        return { services: initialData };
      },
      mounted() {
        fetch('api/services.php')
          .then((response) => (response.ok ? response.json() : null))
          .then((payload) => {
            if (!payload || !Array.isArray(payload.services) || !payload.services.length) return;
            this.services = payload.services;
            window.dispatchEvent(new CustomEvent('catalog:services-updated', { detail: this.services }));
          })
          .catch(() => {});
      }
    }).mount(container);
  };

  const syncCatalogDomFromServices = (services) => {
    const groups = document.querySelectorAll('[data-category] .catalog-grid');
    if (!groups.length || !Array.isArray(services) || !services.length) return;

    const map = {
      print: 'Типография и полиграфия',
      souvenir: 'Сувенирная продукция',
      wide: 'Широкоформатная печать',
      outdoor: 'Наружная реклама'
    };

    Object.entries(map).forEach(([key, title]) => {
      const grid = document.querySelector(`[data-category="${key}"] .catalog-grid`);
      if (!grid) return;

      const items = services.filter((service) => service.category === title);
      if (!items.length) return;

      grid.innerHTML = items
        .map(
          (service) =>
            `<article class="service-tile"><h3>${service.title}</h3><button class="btn btn-disabled" disabled>Подробнее</button></article>`
        )
        .join('');
    });
  };

  document.addEventListener('DOMContentLoaded', () => {
    setupCatalogTabs();
    setupManagerModal();
    setupVueCatalogSync();

    const initialServices = [];
    const reverseMap = {
      print: 'Типография и полиграфия',
      souvenir: 'Сувенирная продукция',
      wide: 'Широкоформатная печать',
      outdoor: 'Наружная реклама'
    };

    document.querySelectorAll('[data-category]').forEach((group) => {
      const key = group.getAttribute('data-category');
      const category = reverseMap[key] || '';
      group.querySelectorAll('.service-tile h3').forEach((titleNode) => {
        initialServices.push({ category, title: titleNode.textContent?.trim() || '' });
      });
    });

    syncCatalogDomFromServices(initialServices);

    window.addEventListener('catalog:services-updated', (event) => {
      syncCatalogDomFromServices(event.detail || []);
    });
  });
})();

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

  const setupMobileNav = () => {
    const nav = document.querySelector('.top-nav');
    const toggle = document.querySelector('[data-nav-toggle]');
    const panel = document.querySelector('[data-nav-panel]');

    if (!nav || !toggle || !panel) return;

    const media = window.matchMedia('(max-width: 768px)');

    const setNavState = (isOpen) => {
      nav.classList.toggle('is-open', isOpen);
      toggle.setAttribute('aria-expanded', String(isOpen));
      panel.setAttribute('aria-hidden', String(!isOpen));
    };

    const closeNav = () => setNavState(false);

    toggle.addEventListener('click', () => {
      if (!media.matches) return;
      const isOpen = nav.classList.contains('is-open');
      setNavState(!isOpen);
    });

    panel.addEventListener('click', (event) => {
      if (!media.matches) return;
      const target = event.target;
      if (!target) return;

      if (target.closest('a') || target.closest('button')) {
        closeNav();
      }
    });

    document.addEventListener('click', (event) => {
      if (!media.matches) return;
      if (!nav.classList.contains('is-open')) return;
      if (!nav.contains(event.target)) closeNav();
    });

    document.addEventListener('keydown', (event) => {
      if (!media.matches) return;
      if (event.key === 'Escape') closeNav();
    });

    const syncMode = () => {
      if (media.matches) {
        closeNav();
        return;
      }

      nav.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
      panel.setAttribute('aria-hidden', 'false');
    };

    if (media.addEventListener) {
      media.addEventListener('change', syncMode);
    } else if (media.addListener) {
      media.addListener(syncMode);
    }

    syncMode();
  };

  const setupNavContrast = () => {
    const nav = document.querySelector('.top-nav');
    if (!nav) return;

    const hero = document.querySelector('.hero, .catalog-header, .admin-header');
    if (!hero) {
      nav.classList.add('is-contrast');
      return;
    }

    let ticking = false;
    const update = () => {
      const navHeight = nav.offsetHeight || 0;
      const heroBottom = hero.getBoundingClientRect().bottom;
      nav.classList.toggle('is-contrast', heroBottom <= navHeight + 8);
    };

    const onScroll = () => {
      if (ticking) return;
      ticking = true;
      window.requestAnimationFrame(() => {
        update();
        ticking = false;
      });
    };

    update();
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
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
    const formFields = managerForm ? managerForm.querySelector('.manager-form-fields') : null;
    const successMessage = managerForm ? managerForm.querySelector('[data-manager-success]') : null;
    const submitButton = managerForm ? managerForm.querySelector('.manager-submit') : null;
    const submitButtonDefaultHtml = submitButton ? submitButton.innerHTML : '';
    let pendingReset = false;

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

    const resetSuccessState = () => {
      managerForm.classList.remove('is-success');

      if (formFields) {
        formFields.setAttribute('aria-hidden', 'false');
      }

      if (successMessage) {
        successMessage.hidden = true;
        successMessage.setAttribute('aria-hidden', 'true');
      }

      if (submitButton) {
        submitButton.type = 'submit';
        submitButton.innerHTML = submitButtonDefaultHtml;
      }
    };

    const showSuccessState = () => {
      managerForm.classList.add('is-success');

      if (formFields) {
        formFields.setAttribute('aria-hidden', 'true');
      }

      if (successMessage) {
        successMessage.hidden = false;
        successMessage.setAttribute('aria-hidden', 'false');
      }

      if (submitButton) {
        submitButton.type = 'button';
        submitButton.textContent = 'Хорошо!';
      }
    };

    const resetFormValues = () => {
      managerForm.reset();
      normalizePhoneInput('');
      setPhoneErrorState(false);
    };

    const openModal = () => {
      resetSuccessState();
      modalOverlay.classList.add('is-open');
      document.body.classList.add('modal-open');
      modalOverlay.setAttribute('aria-hidden', 'false');
    };

    const closeModal = () => {
      modalOverlay.classList.remove('is-open');
      document.body.classList.remove('modal-open');
      modalOverlay.setAttribute('aria-hidden', 'true');

      if (managerForm.classList.contains('is-success')) {
        window.setTimeout(() => {
          resetSuccessState();
          if (pendingReset) {
            resetFormValues();
            pendingReset = false;
          }
        }, 350);
      } else {
        resetSuccessState();
      }
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

    if (submitButton) {
      submitButton.addEventListener('click', () => {
        if (managerForm.classList.contains('is-success')) {
          closeModal();
        }
      });
    }

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

      let requestSucceeded = false;

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

        showSuccessState();
        requestSucceeded = true;
        pendingReset = true;
      } catch (error) {
        window.alert(error.message || 'Ошибка отправки заявки');
      } finally {
        if (submitButton) {
          submitButton.disabled = false;
          if (!requestSucceeded) {
            submitButton.innerHTML = submitButtonDefaultHtml;
            submitButton.type = 'submit';
          }
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
    setupMobileNav();
    setupNavContrast();
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

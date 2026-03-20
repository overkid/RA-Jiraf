(() => {
  const phoneDigitsCount = 10;
  const phonePrefix = '+7';
  const serviceOtherValue = 'other';

  const categoryMap = {
    print: 'Типография и полиграфия',
    souvenir: 'Сувенирная продукция',
    wide: 'Широкоформатная печать',
    outdoor: 'Наружная реклама'
  };

  const fallbackServiceTitles = [
    'Изготовление визиток',
    'Печать буклетов и листовок',
    'Печать фирменных бланков',
    'Изготовление календарей',
    'Нанесение логотипа на кружки',
    'Печать на футболках',
    'Сувенирные ручки с логотипом',
    'Подарочные наборы для компаний',
    'Изготовление рекламных баннеров',
    'Печать наклеек для заднего и лобового стекла',
    'Печать на холсте',
    'Печать виниловых наклеек и стикеров',
    'Изготовление световых коробов',
    'Монтаж вывесок под ключ',
    'Оформление входных групп',
    'Брендирование фасадов и витрин'
  ];

  const safeText = (value) => String(value || '').trim();

  const pickFirstSentence = (value) => {
    const sentence = safeText(value)
      .split(/[.!?]+/)
      .map((part) => safeText(part))
      .find(Boolean);
    return sentence || '';
  };

  const toSentence = (value) => {
    const cleaned = safeText(value).replace(/[.!?]+$/g, '');
    return cleaned ? `${cleaned}.` : '';
  };

  const buildServiceDescription = ({ title, category, description }) => {
    const serviceTitle = safeText(title) || 'Услуга';
    const serviceCategory = safeText(category) || 'Рекламные услуги';
    const fromDatabase = toSentence(pickFirstSentence(description));

    const sentence1 = `Услуга «${serviceTitle}» относится к направлению «${serviceCategory}» и настраивается под конкретную задачу вашего бизнеса.`;
    const sentence2 = fromDatabase || `Мы подбираем решение под формат размещения, фирменный стиль и целевую аудиторию, чтобы результат работал на вашу цель.`;
    const sentence3 = 'Перед запуском согласовываем материалы, размер, тираж и сроки, чтобы вы заранее понимали итоговый вид и бюджет проекта.';
    const sentence4 = 'Если макет еще не готов, менеджер поможет с подготовкой и предложит оптимальный вариант производства без лишних затрат.';

    return [sentence1, sentence2, sentence3, sentence4].join(' ');
  };

  const escapeHtml = (value) =>
    String(value || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');

  const getUniqueServiceTitles = (services) => {
    if (!Array.isArray(services) || !services.length) return [];

    const titles = [];
    const seen = new Set();

    services.forEach((service) => {
      const title = safeText(service?.title);
      if (!title || seen.has(title) || title === serviceOtherValue) return;
      seen.add(title);
      titles.push(title);
    });

    return titles;
  };

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
      if (event.target.closest('a') || event.target.closest('button')) {
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
    const serviceSelect = document.querySelector('#manager-service');
    const formFields = managerForm ? managerForm.querySelector('.manager-form-fields') : null;
    const successMessage = managerForm ? managerForm.querySelector('[data-manager-success]') : null;
    const submitButton = managerForm ? managerForm.querySelector('.manager-submit') : null;
    const submitButtonDefaultHtml = submitButton ? submitButton.innerHTML : '';
    let pendingReset = false;

    if (!modalOverlay || !closeModalButton || !managerForm || !phoneInput || !phoneField) return null;

    const ensureServicePlaceholder = () => {
      if (!serviceSelect) return;

      let placeholder = serviceSelect.querySelector('option[value=""]');
      if (!placeholder) {
        placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Выберите услугу';
        placeholder.disabled = true;
        serviceSelect.insertBefore(placeholder, serviceSelect.firstChild);
      }
    };

    const getServiceOptionByValue = (value) => {
      if (!serviceSelect) return null;
      const normalized = safeText(value);
      if (!normalized) return serviceSelect.querySelector('option[value=""]');

      return Array.from(serviceSelect.options).find((option) => option.value === normalized) || null;
    };

    const clearDynamicServiceOptions = () => {
      if (!serviceSelect) return;
      serviceSelect.querySelectorAll('option[data-service-option="dynamic"]').forEach((option) => option.remove());
    };

    const setServiceOptions = (titles) => {
      if (!serviceSelect) return;

      ensureServicePlaceholder();
      const currentValue = serviceSelect.value;
      const uniqueTitles = Array.from(new Set((titles || []).map((title) => safeText(title)).filter(Boolean)));
      const otherOption = serviceSelect.querySelector(`option[value="${serviceOtherValue}"]`);

      clearDynamicServiceOptions();

      uniqueTitles.forEach((title) => {
        if (title === serviceOtherValue) return;

        const option = document.createElement('option');
        option.value = title;
        option.textContent = title;
        option.setAttribute('data-service-option', 'dynamic');

        if (otherOption) {
          serviceSelect.insertBefore(option, otherOption);
        } else {
          serviceSelect.appendChild(option);
        }
      });

      const hasCurrentValue = Boolean(getServiceOptionByValue(currentValue));
      if (hasCurrentValue) {
        serviceSelect.value = currentValue;
      } else {
        serviceSelect.value = '';
      }
    };

    const setSelectedService = (serviceTitle) => {
      if (!serviceSelect) return;

      ensureServicePlaceholder();
      const normalizedTitle = safeText(serviceTitle);

      if (!normalizedTitle) {
        serviceSelect.value = '';
        return;
      }

      let option = getServiceOptionByValue(normalizedTitle);
      if (!option) {
        const otherOption = serviceSelect.querySelector(`option[value="${serviceOtherValue}"]`);
        option = document.createElement('option');
        option.value = normalizedTitle;
        option.textContent = normalizedTitle;
        option.setAttribute('data-service-option', 'dynamic');

        if (otherOption) {
          serviceSelect.insertBefore(option, otherOption);
        } else {
          serviceSelect.appendChild(option);
        }
      }

      serviceSelect.value = normalizedTitle;
    };

    const getServicePayload = () => {
      if (!serviceSelect) {
        return { serviceTitle: '', serviceIsOther: false };
      }

      const value = safeText(serviceSelect.value);
      if (!value) {
        return { serviceTitle: '', serviceIsOther: false };
      }

      if (value === serviceOtherValue) {
        return { serviceTitle: '', serviceIsOther: true };
      }

      return { serviceTitle: value, serviceIsOther: false };
    };

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
      if (serviceSelect) {
        serviceSelect.value = '';
      }
    };

    const openModal = (options = {}) => {
      resetSuccessState();
      if (options.serviceTitle) {
        setSelectedService(options.serviceTitle);
      } else if (serviceSelect) {
        serviceSelect.value = '';
      }

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

    ensureServicePlaceholder();
    normalizePhoneInput(phoneInput.value);

    phoneInput.addEventListener('focus', () => normalizePhoneInput(phoneInput.value));
    phoneInput.addEventListener('input', (event) => {
      normalizePhoneInput(event.target.value);
      setPhoneErrorState(false);
    });
    phoneInput.addEventListener('blur', validatePhone);

    openModalButtons.forEach((button) => {
      button.addEventListener('click', () => {
        openModal({ serviceTitle: safeText(button.getAttribute('data-service-title')) });
      });
    });

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
      const { serviceTitle, serviceIsOther } = getServicePayload();
      const payload = {
        name: safeText(formData.get('name')),
        phone: safeText(formData.get('phone')),
        comment: safeText(formData.get('comment')),
        service_title: serviceTitle,
        service_is_other: serviceIsOther
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

    return {
      openModal,
      closeModal,
      setServiceOptions,
      setSelectedService
    };
  };

  const setupServiceDetailsModal = (managerModalApi) => {
    const modalOverlay = document.querySelector('[data-service-modal]');
    const closeModalButton = document.querySelector('[data-close-service-modal]');
    const contactButton = document.querySelector('[data-service-modal-contact]');
    const titleNode = document.querySelector('#service-modal-title');
    const categoryNode = document.querySelector('[data-service-modal-category]');
    const descriptionNode = document.querySelector('[data-service-modal-description]');

    if (!modalOverlay || !closeModalButton || !contactButton || !titleNode || !descriptionNode) return;

    let activeServiceTitle = '';
    let activeServiceCategory = '';

    const renderDescriptionParagraphs = (text) => {
      const normalizedText = safeText(text);
      const sentences = normalizedText
        .split(/(?<=[.!?])\s+/)
        .map((sentence) => safeText(sentence))
        .filter(Boolean);

      const content = sentences.length ? sentences : [normalizedText];
      descriptionNode.innerHTML = content.map((sentence) => `<p>${escapeHtml(sentence)}</p>`).join('');
    };

    const openModal = ({ title, category, description }) => {
      activeServiceTitle = safeText(title);
      activeServiceCategory = safeText(category);
      titleNode.textContent = activeServiceTitle || 'Услуга';

      if (categoryNode) {
        categoryNode.textContent = activeServiceCategory ? `Категория: ${activeServiceCategory}` : '';
        categoryNode.hidden = !activeServiceCategory;
      }

      const modalDescription = safeText(description) || buildServiceDescription({
        title: activeServiceTitle,
        category: activeServiceCategory,
        description: ''
      });
      renderDescriptionParagraphs(modalDescription);

      modalOverlay.classList.add('is-open');
      document.body.classList.add('modal-open');
      modalOverlay.setAttribute('aria-hidden', 'false');
    };

    const closeModal = () => {
      modalOverlay.classList.remove('is-open');
      modalOverlay.setAttribute('aria-hidden', 'true');

      const managerOverlay = document.querySelector('[data-manager-modal]');
      if (!managerOverlay || !managerOverlay.classList.contains('is-open')) {
        document.body.classList.remove('modal-open');
      }
    };

    document.addEventListener('click', (event) => {
      const trigger = event.target.closest('[data-open-service-details]');
      if (!trigger) return;

      event.preventDefault();
      const tile = trigger.closest('.service-tile');
      const title = safeText(trigger.getAttribute('data-service-title')) || safeText(tile?.querySelector('h3')?.textContent);
      const category = safeText(trigger.getAttribute('data-service-category'));
      const description = safeText(trigger.getAttribute('data-service-description'));
      openModal({ title, category, description });
    });

    closeModalButton.addEventListener('click', closeModal);

    modalOverlay.addEventListener('click', (event) => {
      if (event.target === modalOverlay) closeModal();
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && modalOverlay.classList.contains('is-open')) {
        closeModal();
      }
    });

    contactButton.addEventListener('click', () => {
      closeModal();
      if (managerModalApi) {
        managerModalApi.openModal({ serviceTitle: activeServiceTitle });
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

    Object.entries(categoryMap).forEach(([key, categoryTitle]) => {
      const grid = document.querySelector(`[data-category="${key}"] .catalog-grid`);
      if (!grid) return;

      const items = services.filter((service) => safeText(service?.category) === categoryTitle);
      if (!items.length) return;

      grid.innerHTML = items
        .map((service) => {
          const title = safeText(service?.title);
          const category = safeText(service?.category) || categoryTitle;
          const description = buildServiceDescription({
            title,
            category,
            description: safeText(service?.description)
          });

          return (
            `<article class="service-tile">` +
            `<h3>${escapeHtml(title)}</h3>` +
            `<button class="btn btn-disabled" type="button" data-open-service-details data-service-title="${escapeHtml(title)}" data-service-category="${escapeHtml(category)}" data-service-description="${escapeHtml(description)}">Подробнее</button>` +
            `</article>`
          );
        })
        .join('');
    });
  };

  const getInitialCatalogServicesFromDataAttribute = () => {
    const container = document.querySelector('[data-vue-catalog]');
    if (!container) return [];

    try {
      const payload = JSON.parse(container.getAttribute('data-initial-services') || '[]');
      if (!Array.isArray(payload)) return [];

      return payload
        .map((item) => ({
          category: safeText(item?.category),
          title: safeText(item?.title),
          description: safeText(item?.description)
        }))
        .filter((item) => item.title && item.category);
    } catch (error) {
      return [];
    }
  };

  const collectServicesFromCatalogDom = () => {
    const collected = [];

    document.querySelectorAll('[data-category]').forEach((group) => {
      const categoryKey = safeText(group.getAttribute('data-category'));
      const categoryTitle = categoryMap[categoryKey] || '';

      group.querySelectorAll('.service-tile').forEach((tile) => {
        const title = safeText(tile.querySelector('h3')?.textContent);
        const button = tile.querySelector('[data-open-service-details]');
        const description = safeText(button?.getAttribute('data-service-description'));

        if (title && categoryTitle) {
          collected.push({ category: categoryTitle, title, description });
        }
      });
    });

    return collected;
  };

  const loadServicesForSelectFromApi = (managerModalApi) => {
    if (!managerModalApi) return;

    fetch('api/services.php')
      .then((response) => (response.ok ? response.json() : null))
      .then((payload) => {
        if (!payload || !Array.isArray(payload.services) || !payload.services.length) return;
        managerModalApi.setServiceOptions(getUniqueServiceTitles(payload.services));
      })
      .catch(() => {});
  };

  document.addEventListener('DOMContentLoaded', () => {
    setupMobileNav();
    setupNavContrast();
    setupCatalogTabs();

    const managerModalApi = setupManagerModal();
    setupServiceDetailsModal(managerModalApi);
    setupVueCatalogSync();

    const initialCatalogServices = getInitialCatalogServicesFromDataAttribute();
    if (initialCatalogServices.length) {
      syncCatalogDomFromServices(initialCatalogServices);
    } else {
      const fallbackCatalogServices = collectServicesFromCatalogDom();
      if (fallbackCatalogServices.length) {
        syncCatalogDomFromServices(fallbackCatalogServices);
      }
    }

    if (managerModalApi) {
      const baseServicesForSelect = initialCatalogServices.length ? initialCatalogServices : collectServicesFromCatalogDom();
      const serviceTitles = getUniqueServiceTitles(baseServicesForSelect);
      managerModalApi.setServiceOptions(serviceTitles.length ? serviceTitles : fallbackServiceTitles);

      loadServicesForSelectFromApi(managerModalApi);
    }

    window.addEventListener('catalog:services-updated', (event) => {
      const updatedServices = Array.isArray(event.detail) ? event.detail : [];
      syncCatalogDomFromServices(updatedServices);

      if (managerModalApi) {
        managerModalApi.setServiceOptions(getUniqueServiceTitles(updatedServices));
      }
    });
  });
})();

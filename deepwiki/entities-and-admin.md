# Сущности и админка (FastAPI + Vue + PostgreSQL)

## 1) Сущности: из чего состоят

### 1. `AdminUser`
**Назначение:** доступ в админку.

Поля:
- `id` (UUID / int, PK)
- `login` (string, unique)
- `password_hash` (string)
- `is_active` (bool)
- `created_at` (datetime)
- `updated_at` (datetime)

---

### 2. `ContactInfo`
**Назначение:** контакты на сайте (шапка, футер, раздел «Контакты»).

Поля:
- `id` (PK)
- `company_name` (string)
- `phone` (string)
- `email` (string)
- `address` (string)
- `work_hours` (string)
- `telegram_url` (string, nullable)
- `vk_url` (string, nullable)
- `whatsapp_url` (string, nullable)
- `updated_at` (datetime)

---

### 3. `ServiceCategory`
**Назначение:** группы услуг (например: печать, реклама, сувениры).

Поля:
- `id` (PK)
- `name` (string)
- `slug` (string, unique)
- `sort_order` (int)
- `is_active` (bool)

Связи:
- 1 ко многим с `ServiceItem`.

---

### 4. `ServiceItem`
**Назначение:** конкретная услуга в каталоге.

Поля:
- `id` (PK)
- `category_id` (FK -> ServiceCategory)
- `title` (string)
- `short_description` (string)
- `full_description` (text, nullable)
- `price_from` (numeric, nullable)
- `image_url` (string, nullable)
- `is_active` (bool)
- `sort_order` (int)
- `created_at` (datetime)
- `updated_at` (datetime)

---

### 5. `UserRequest`
**Назначение:** заявки с сайта.

Поля:
- `id` (PK)
- `name` (string)
- `phone` (string)
- `email` (string, nullable)
- `message` (text)
- `service_item_id` (FK -> ServiceItem, nullable)
- `status` (enum: `new`, `in_progress`, `done`, `archived`)
- `manager_comment` (text, nullable)
- `created_at` (datetime)

---

## 2) Что редактируется в админке

### Раздел «Контакты»
- редактируются все поля `ContactInfo`;
- обычно одна активная запись контактов для всего сайта.

### Раздел «Каталог услуг»
- `ServiceCategory`: создание/редактирование/скрытие, порядок;
- `ServiceItem`: создание/редактирование/скрытие, цена «от», описание, изображение, порядок.

### Раздел «Заявки»
- просмотр списка `UserRequest`;
- фильтр по статусу и дате;
- изменение статуса заявки;
- добавление комментария менеджера;
- удаление лучше отключить (история обращений важна).

---

## 3) Доступ в админку

- URL: `/admin` (прямая ссылка).
- Авторизация: логин + пароль (данные в `AdminUser`).
- После входа: JWT/сессия, защищенные API-маршруты.

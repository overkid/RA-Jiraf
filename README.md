# RA «Жираф» — монорепозиторий (Vue + Fastify + SQL)

Проект переведен в **монорепозиторий** с фронтендом и бэкендом:

- `apps/frontend` — сайт на **Vue 3 + Vite** (главная, каталог услуг, админка).
- `apps/backend` — API на **Fastify**.
- База данных — **SQLite (SQL)**, файл `apps/backend/data.sqlite`.

## Сущности (SQL)

1. `service_categories` — категории услуг:
   - `id`, `slug`, `title`, `sort_order`
2. `services` — услуги каталога:
   - `id`, `category_id`, `title`, `description`, `is_active`, `sort_order`
3. `contacts` — контактные данные сайта:
   - `id=1`, `email`, `phone_main`, `phone_alt`, `address_line1..3`

## Админка

- Вход по прямой ссылке: `http://localhost:5173/admin/login`
- Логин/пароль по умолчанию:
  - `admin`
  - `admin123`
- Что можно редактировать:
  - услуги в каталоге;
  - контактные данные.

> Рекомендуется переопределить учетные данные через переменные окружения на бэкенде.


> Примечание: медиа-файлы не дублируются в `apps/frontend/public` — используется символическая ссылка `apps/frontend/public/media -> ../../../media`, чтобы избежать проблем с бинарными файлами при публикации изменений.

## Запуск

### 1) Установка зависимостей

```bash
npm install
```

### 2) Запуск фронтенда и бэкенда одновременно

```bash
npm run dev
```

- Frontend: `http://localhost:5173`
- Backend: `http://localhost:3001`

## Переменные окружения (backend)

- `PORT` — порт API (по умолчанию `3001`)
- `JWT_SECRET` — секрет для JWT
- `ADMIN_LOGIN` — логин администратора
- `ADMIN_PASSWORD` — пароль администратора

Пример:

```bash
PORT=3001 JWT_SECRET=supersecret ADMIN_LOGIN=myadmin ADMIN_PASSWORD=mypassword npm run dev -w @ra-jiraf/backend
```

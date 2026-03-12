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


> Примечание: медиа-файлы не дублируются в `apps/frontend/public`. Путь `/media/...` резолвится через `vite.config.js` в корневую папку `media`, что работает кроссплатформенно (включая Windows без symlink).

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


## Требования к Node.js

- Рекомендуется **Node.js 20 LTS** или **22 LTS**.
- Node 25 (current) может ломать установку нативных пакетов на Windows.

## Частые проблемы на Windows

1. `concurrently не является ... командой`
   - Это значит, что `npm install` завершился с ошибкой и зависимости не поставились.
   - В этом репозитории `npm run dev` теперь запускается через встроенный `node scripts/dev.mjs`, без зависимости `concurrently`.

2. Ошибка сборки `better-sqlite3` / `node-gyp` / Visual Studio
   - Раньше проект использовал нативный пакет `better-sqlite3`, который требует C++ toolchain на Windows.
   - Теперь бэкенд использует `sql.js` (SQLite на WebAssembly), поэтому Visual Studio Build Tools не нужны.

3. EPERM при очистке `node_modules`
   - Закройте все терминалы/процессы Node, которые могут держать файлы.
   - Затем выполните:

```powershell
rd /s /q node_modules
del package-lock.json
npm cache verify
npm install
```


4. `Error: no such table: services`
   - Это означает, что локальный `apps/backend/data.sqlite` был создан в частично-инициализированном состоянии.
   - Удалите файл БД и перезапустите backend, таблицы будут созданы заново с сид-данными:

```powershell
del apps\backend\data.sqlite
npm run dev
```

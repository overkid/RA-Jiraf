# RA-Jiraf — перенос на фреймворки (рабочая версия)

Проект переведен на фреймворки в формате монорепозитория:
- **База (SQL):** PostgreSQL (или SQLite для быстрого локального старта)
- **Backend:** FastAPI + SQLAlchemy
- **Frontend:** Vue 3 + Vite

## Структура

```text
apps/
  backend-fastapi/
  frontend-vue/
legacy (старые статические файлы) лежат в корне пока как резерв.
```

## Быстрый запуск (локально)

### 1) Backend (FastAPI)

```bash
cd apps/backend-fastapi
python -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
uvicorn app.main:app --reload --port 8000
```

Backend поднимется на `http://localhost:8000`.

Проверка:
- `http://localhost:8000/api/health`
- `http://localhost:8000/docs`

### 2) Frontend (Vue)

Открыть второй терминал:

```bash
cd apps/frontend-vue
npm install
npm run dev
```

Frontend поднимется на `http://localhost:5173`.

## Админка

- Страница: `http://localhost:5173/admin`
- Логин: `admin`
- Пароль: `admin123`

(можно переопределить переменными окружения `ADMIN_LOGIN`, `ADMIN_PASSWORD` в backend)

## Что уже работает

- Публичная часть:
  - Главная страница (`/`)
  - Каталог услуг (`/services`)
  - Отправка заявки
- Админка (`/admin`):
  - Логин по паролю
  - Редактирование контактной информации
  - Просмотр каталога услуг
  - Просмотр заявок и смена их статуса

## Переменные окружения backend

- `DATABASE_URL` (по умолчанию: `sqlite:///./app.db`)
- `JWT_SECRET`
- `ADMIN_LOGIN`
- `ADMIN_PASSWORD`
- `JWT_EXP_HOURS`

Пример для PostgreSQL:

```bash
export DATABASE_URL='postgresql+psycopg://user:password@localhost:5432/rajiraf'
```

## Важно для GitHub/Codex

Чтобы не падало обновление ветки с ошибкой `Бинарные файлы не поддерживаются`, бинарные ассеты (`.png`, `.ico`) удалены из репозитория. В legacy-страницах вместо них используются SVG-ресурсы.

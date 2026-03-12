# RA-Jiraf

Ок, максимально просто.

## Хочу просто увидеть сайт ПРЯМО СЕЙЧАС (без установок)

Открой файл:

- `index.html`

Это legacy-версия, она работает без npm/python.

---

## Запуск новой версии (Windows PowerShell, без Linux-команд)

> Ниже команды именно для **PowerShell**, поэтому `source` использовать не нужно.

### 1) Backend (терминал №1)

```powershell
cd D:\github\tasks\RA-Jiraf\apps\backend-fastapi
py -m venv .venv
.\.venv\Scripts\python -m pip install -r requirements.txt
.\.venv\Scripts\python -m uvicorn app.main:app --reload --port 8000
```

Если `py` не найден — установи Python с галкой **Add python to PATH**.

### 2) Frontend (терминал №2)

```powershell
cd D:\github\tasks\RA-Jiraf\apps\frontend-vue
npm install
npm run dev
```

Открыть:
- `http://localhost:5173`

Админка:
- `http://localhost:5173/admin`
- логин: `admin`
- пароль: `admin123`

---

## Почему у тебя были ошибки

1. `source .venv/bin/activate` — это команда из Linux/macOS, в PowerShell она не работает.
2. `pip`/`uvicorn` “не найдено” — потому что ты не вызывал их из `.venv` через `\.venv\Scripts\python -m ...`.
3. `npm ENOENT ... RA-Jiraf\package.json` — ты запускал `npm install` не в `apps/frontend-vue`.
4. `uvicorn ... 8000cd apps/frontend-vue` — две команды слиплись в одну строку.

---

## Самая короткая проверка (без backend)

Если хочешь просто посмотреть новый фронт даже без API:

```powershell
cd D:\github\tasks\RA-Jiraf\apps\frontend-vue
npm install
npm run dev
```

Главная и каталог откроются с локальными данными-заглушками, даже если backend не запущен.

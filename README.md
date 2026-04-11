# RA-Jiraf

<img width="800" height="100" alt="banner" src="https://github.com/user-attachments/assets/9b02744b-82e8-4323-9e0b-a7c89ccd9cf7" />

Краткий гайд по запуску проекта локально.

## Стек
- **PHP** (страницы + API)
- **MySQL** (услуги, заявки, контент)
- **Vue 3** (через CDN, без сборщика)

## Быстрый старт (XAMPP)
1. Установите XAMPP и запустите `Apache` + `MySQL`.
2. Скопируйте проект в папку `htdocs`, например:
   `C:\xampp\htdocs\RA-Jiraf`
3. Импортируйте структуру БД:
   в phpMyAdmin выполните SQL из файла `db/schema.sql`.
4. Откройте сайт в браузере:
   - Главная: `http://localhost/RA-Jiraf/`
   - Услуги: `http://localhost/RA-Jiraf/services.php`
   - Админка (только по прямой ссылке): `http://localhost/RA-Jiraf/admin.php`

ТЕКУЩИЙ ЛОГИН:ПАРОЛЬ: `admin:123`

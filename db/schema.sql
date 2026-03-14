CREATE DATABASE IF NOT EXISTS ra_jiraf CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ra_jiraf;

CREATE TABLE IF NOT EXISTS services (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category VARCHAR(100) NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS client_requests (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  comment TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS site_content (
  content_key VARCHAR(120) PRIMARY KEY,
  content_value TEXT NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO services (category, title, description) VALUES
('Типография и полиграфия', 'Изготовление визиток', 'Плотная бумага, аккуратная резка, фирменный стиль.'),
('Типография и полиграфия', 'Печать буклетов и листовок', 'Яркая печать для рекламных акций и презентаций.'),
('Сувенирная продукция', 'Нанесение логотипа на кружки', 'Печатаем логотипы и фирменные фразы на сувенирах.'),
('Широкоформатная печать', 'Изготовление рекламных баннеров', 'Баннеры для улицы и помещений любых размеров.'),
('Наружная реклама', 'Монтаж вывесок под ключ', 'Производство и монтаж вывесок с гарантией.');

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
  content_key VARCHAR(100) PRIMARY KEY,
  content_value TEXT NOT NULL
);

INSERT INTO services (category, title, description) VALUES
('Типография и полиграфия', 'Изготовление визиток', 'Плотная бумага, аккуратная резка, фирменный стиль.'),
('Типография и полиграфия', 'Печать буклетов и листовок', 'Яркая печать для рекламных акций и презентаций.'),
('Сувенирная продукция', 'Нанесение логотипа на кружки', 'Печатаем логотипы и фирменные фразы на сувенирах.'),
('Широкоформатная печать', 'Изготовление рекламных баннеров', 'Баннеры для улицы и помещений любых размеров.'),
('Наружная реклама', 'Монтаж вывесок под ключ', 'Производство и монтаж вывесок с гарантией.');

INSERT INTO site_content (content_key, content_value) VALUES
('hero_title', 'Рекламное агентство полного цикла'),
('hero_subtitle', 'Мы предлагаем свои производственные и рекламные услуги на территории всей Владимирской области'),
('services_title', 'Полный спектр услуг'),
('services_subtitle', 'Услуги рекламного агентства покрывают почти все возможные потребности'),
('footer_title', 'Мы готовы решить вашу проблему'),
('footer_text', 'Вам не обязательно ехать в офис рекламного агентства — можно оформить заказ дистанционно по удобному каналу связи')
ON DUPLICATE KEY UPDATE content_value = VALUES(content_value);

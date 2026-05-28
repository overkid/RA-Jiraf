CREATE DATABASE IF NOT EXISTS ra_jiraf CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ra_jiraf;

CREATE TABLE IF NOT EXISTS services (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category VARCHAR(100) NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  base_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  unit_name VARCHAR(40) NOT NULL DEFAULT 'шт.',
  calculator_enabled TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS client_requests (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  email VARCHAR(160) NULL,
  service_title VARCHAR(255) NULL,
  service_is_other TINYINT(1) NOT NULL DEFAULT 0,
  comment TEXT NULL,
  calculator_payload JSON NULL,
  estimated_total DECIMAL(12,2) NULL,
  request_status VARCHAR(20) NOT NULL DEFAULT 'new',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS request_notes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  request_id INT UNSIGNED NOT NULL,
  author_login VARCHAR(64) NOT NULL,
  author_role VARCHAR(20) NOT NULL,
  note_text TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_request_notes_request_id_created_at (request_id, created_at)
);



CREATE TABLE IF NOT EXISTS clients (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  email VARCHAR(160) NULL,
  company VARCHAR(180) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_clients_phone (phone)
);

CREATE TABLE IF NOT EXISTS calculation_options (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  service_id INT UNSIGNED NOT NULL,
  option_type VARCHAR(40) NOT NULL,
  title VARCHAR(160) NOT NULL,
  price_delta DECIMAL(12,2) NOT NULL DEFAULT 0,
  multiplier DECIMAL(8,3) NOT NULL DEFAULT 1,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 100,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_calculation_options_service (service_id, option_type, is_active, sort_order)
);

CREATE TABLE IF NOT EXISTS orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id INT UNSIGNED NOT NULL,
  request_id INT UNSIGNED NULL,
  service_id INT UNSIGNED NULL,
  title VARCHAR(255) NOT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'new',
  total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  deadline DATE NULL,
  manager_comment TEXT NULL,
  calculator_payload JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_orders_client_status (client_id, status),
  INDEX idx_orders_request (request_id)
);

CREATE TABLE IF NOT EXISTS order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  service_id INT UNSIGNED NULL,
  title VARCHAR(255) NOT NULL,
  quantity DECIMAL(12,2) NOT NULL DEFAULT 1,
  unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  INDEX idx_order_items_order (order_id)
);

CREATE TABLE IF NOT EXISTS order_status_history (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  old_status VARCHAR(40) NULL,
  new_status VARCHAR(40) NOT NULL,
  changed_by VARCHAR(80) NOT NULL,
  comment TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_order_history_order_created (order_id, created_at)
);

CREATE TABLE IF NOT EXISTS request_files (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  request_id INT UNSIGNED NULL,
  order_id INT UNSIGNED NULL,
  client_phone VARCHAR(40) NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  stored_path VARCHAR(255) NOT NULL,
  mime_type VARCHAR(120) NOT NULL,
  file_size INT UNSIGNED NOT NULL,
  uploaded_by VARCHAR(40) NOT NULL DEFAULT 'client',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_request_files_request (request_id, created_at),
  INDEX idx_request_files_order (order_id, created_at),
  INDEX idx_request_files_phone (client_phone)
);

CREATE TABLE IF NOT EXISTS site_content (
  content_key VARCHAR(120) PRIMARY KEY,
  content_value TEXT NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO services (category, title, description) VALUES
('Типография и полиграфия', 'Изготовление визиток', 'Услуга «Изготовление визиток» относится к направлению «Типография и полиграфия» и настраивается под конкретную задачу вашего бизнеса.

Плотная бумага, аккуратная резка, фирменный стиль.

Перед запуском согласовываем материалы, размер, тираж и сроки, чтобы вы заранее понимали итоговый вид и бюджет проекта.

Если макет еще не готов, менеджер поможет с подготовкой и предложит оптимальный вариант производства без лишних затрат.'),
('Типография и полиграфия', 'Печать буклетов и листовок', 'Услуга «Печать буклетов и листовок» относится к направлению «Типография и полиграфия» и настраивается под конкретную задачу вашего бизнеса.

Яркая печать для рекламных акций и презентаций.

Перед запуском согласовываем материалы, размер, тираж и сроки, чтобы вы заранее понимали итоговый вид и бюджет проекта.

Если макет еще не готов, менеджер поможет с подготовкой и предложит оптимальный вариант производства без лишних затрат.'),
('Типография и полиграфия', 'Печать фирменных бланков', 'Услуга «Печать фирменных бланков» относится к направлению «Типография и полиграфия» и настраивается под конкретную задачу вашего бизнеса.

Фирменные бланки для деловой переписки и договоров.

Перед запуском согласовываем материалы, размер, тираж и сроки, чтобы вы заранее понимали итоговый вид и бюджет проекта.

Если макет еще не готов, менеджер поможет с подготовкой и предложит оптимальный вариант производства без лишних затрат.'),
('Типография и полиграфия', 'Изготовление календарей', 'Услуга «Изготовление календарей» относится к направлению «Типография и полиграфия» и настраивается под конкретную задачу вашего бизнеса.

Настенные и настольные календари под ваш дизайн.

Перед запуском согласовываем материалы, размер, тираж и сроки, чтобы вы заранее понимали итоговый вид и бюджет проекта.

Если макет еще не готов, менеджер поможет с подготовкой и предложит оптимальный вариант производства без лишних затрат.'),
('Сувенирная продукция', 'Нанесение логотипа на кружки', 'Услуга «Нанесение логотипа на кружки» относится к направлению «Сувенирная продукция» и настраивается под конкретную задачу вашего бизнеса.

Печатаем логотипы и фирменные фразы на сувенирах.

Перед запуском согласовываем материалы, размер, тираж и сроки, чтобы вы заранее понимали итоговый вид и бюджет проекта.

Если макет еще не готов, менеджер поможет с подготовкой и предложит оптимальный вариант производства без лишних затрат.'),
('Сувенирная продукция', 'Печать на футболках', 'Услуга «Печать на футболках» относится к направлению «Сувенирная продукция» и настраивается под конкретную задачу вашего бизнеса.

Стойкая печать логотипов и изображений на текстиле.

Перед запуском согласовываем материалы, размер, тираж и сроки, чтобы вы заранее понимали итоговый вид и бюджет проекта.

Если макет еще не готов, менеджер поможет с подготовкой и предложит оптимальный вариант производства без лишних затрат.'),
('Сувенирная продукция', 'Сувенирные ручки с логотипом', 'Услуга «Сувенирные ручки с логотипом» относится к направлению «Сувенирная продукция» и настраивается под конкретную задачу вашего бизнеса.

Практичные сувениры для промоакций и партнёров.

Перед запуском согласовываем материалы, размер, тираж и сроки, чтобы вы заранее понимали итоговый вид и бюджет проекта.

Если макет еще не готов, менеджер поможет с подготовкой и предложит оптимальный вариант производства без лишних затрат.'),
('Сувенирная продукция', 'Подарочные наборы для компаний', 'Услуга «Подарочные наборы для компаний» относится к направлению «Сувенирная продукция» и настраивается под конкретную задачу вашего бизнеса.

Сувенирные наборы для клиентов и сотрудников.

Перед запуском согласовываем материалы, размер, тираж и сроки, чтобы вы заранее понимали итоговый вид и бюджет проекта.

Если макет еще не готов, менеджер поможет с подготовкой и предложит оптимальный вариант производства без лишних затрат.'),
('Широкоформатная печать', 'Изготовление рекламных баннеров', 'Услуга «Изготовление рекламных баннеров» относится к направлению «Широкоформатная печать» и настраивается под конкретную задачу вашего бизнеса.

Баннеры для улицы и помещений любых размеров.

Перед запуском согласовываем материалы, размер, тираж и сроки, чтобы вы заранее понимали итоговый вид и бюджет проекта.

Если макет еще не готов, менеджер поможет с подготовкой и предложит оптимальный вариант производства без лишних затрат.'),
('Широкоформатная печать', 'Печать наклеек для заднего и лобового стекла', 'Услуга «Печать наклеек для заднего и лобового стекла» относится к направлению «Широкоформатная печать» и настраивается под конкретную задачу вашего бизнеса.

Наклейки для авто и витрин с защитной ламинацией.

Перед запуском согласовываем материалы, размер, тираж и сроки, чтобы вы заранее понимали итоговый вид и бюджет проекта.

Если макет еще не готов, менеджер поможет с подготовкой и предложит оптимальный вариант производства без лишних затрат.'),
('Широкоформатная печать', 'Печать на холсте', 'Услуга «Печать на холсте» относится к направлению «Широкоформатная печать» и настраивается под конкретную задачу вашего бизнеса.

Интерьерная печать на холсте с подрамником.

Перед запуском согласовываем материалы, размер, тираж и сроки, чтобы вы заранее понимали итоговый вид и бюджет проекта.

Если макет еще не готов, менеджер поможет с подготовкой и предложит оптимальный вариант производства без лишних затрат.'),
('Широкоформатная печать', 'Печать виниловых наклеек и стикеров', 'Услуга «Печать виниловых наклеек и стикеров» относится к направлению «Широкоформатная печать» и настраивается под конкретную задачу вашего бизнеса.

Виниловые стикеры любых форматов и тиражей.

Перед запуском согласовываем материалы, размер, тираж и сроки, чтобы вы заранее понимали итоговый вид и бюджет проекта.

Если макет еще не готов, менеджер поможет с подготовкой и предложит оптимальный вариант производства без лишних затрат.'),
('Наружная реклама', 'Изготовление световых коробов', 'Услуга «Изготовление световых коробов» относится к направлению «Наружная реклама» и настраивается под конкретную задачу вашего бизнеса.

Световые короба и лайтбоксы для фасадов.

Перед запуском согласовываем материалы, размер, тираж и сроки, чтобы вы заранее понимали итоговый вид и бюджет проекта.

Если макет еще не готов, менеджер поможет с подготовкой и предложит оптимальный вариант производства без лишних затрат.'),
('Наружная реклама', 'Монтаж вывесок под ключ', 'Услуга «Монтаж вывесок под ключ» относится к направлению «Наружная реклама» и настраивается под конкретную задачу вашего бизнеса.

Производство и монтаж вывесок с гарантией.

Перед запуском согласовываем материалы, размер, тираж и сроки, чтобы вы заранее понимали итоговый вид и бюджет проекта.

Если макет еще не готов, менеджер поможет с подготовкой и предложит оптимальный вариант производства без лишних затрат.'),
('Наружная реклама', 'Оформление входных групп', 'Услуга «Оформление входных групп» относится к направлению «Наружная реклама» и настраивается под конкретную задачу вашего бизнеса.

Комплексное оформление входов и навигации.

Перед запуском согласовываем материалы, размер, тираж и сроки, чтобы вы заранее понимали итоговый вид и бюджет проекта.

Если макет еще не готов, менеджер поможет с подготовкой и предложит оптимальный вариант производства без лишних затрат.'),
('Наружная реклама', 'Брендирование фасадов и витрин', 'Услуга «Брендирование фасадов и витрин» относится к направлению «Наружная реклама» и настраивается под конкретную задачу вашего бизнеса.

Фирменное оформление фасадов и витрин.

Перед запуском согласовываем материалы, размер, тираж и сроки, чтобы вы заранее понимали итоговый вид и бюджет проекта.

Если макет еще не готов, менеджер поможет с подготовкой и предложит оптимальный вариант производства без лишних затрат.');

UPDATE services SET base_price = CASE
  WHEN category = 'Широкоформатная печать' THEN 950
  WHEN category = 'Наружная реклама' THEN 3500
  WHEN category = 'Сувенирная продукция' THEN 420
  ELSE 850
END WHERE base_price = 0;

INSERT INTO calculation_options (service_id, option_type, title, price_delta, multiplier, sort_order)
SELECT id, 'Материал', 'Стандарт', 0, 1, 10 FROM services;

INSERT INTO calculation_options (service_id, option_type, title, price_delta, multiplier, sort_order)
SELECT id, 'Срочность', 'Обычный срок', 0, 1, 10 FROM services;

INSERT INTO calculation_options (service_id, option_type, title, price_delta, multiplier, sort_order)
SELECT id, 'Срочность', 'Срочно +30%', 0, 1.3, 20 FROM services;

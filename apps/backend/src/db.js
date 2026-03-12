import Database from 'better-sqlite3';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const dbPath = path.join(__dirname, '..', 'data.sqlite');
const db = new Database(dbPath);

db.pragma('journal_mode = WAL');

db.exec(`
  CREATE TABLE IF NOT EXISTS service_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT NOT NULL UNIQUE,
    title TEXT NOT NULL,
    sort_order INTEGER DEFAULT 0
  );

  CREATE TABLE IF NOT EXISTS services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    description TEXT DEFAULT '',
    is_active INTEGER DEFAULT 1,
    sort_order INTEGER DEFAULT 0,
    FOREIGN KEY(category_id) REFERENCES service_categories(id)
  );

  CREATE TABLE IF NOT EXISTS contacts (
    id INTEGER PRIMARY KEY CHECK (id = 1),
    email TEXT NOT NULL,
    phone_main TEXT NOT NULL,
    phone_alt TEXT NOT NULL,
    address_line1 TEXT NOT NULL,
    address_line2 TEXT NOT NULL,
    address_line3 TEXT NOT NULL
  );
`);

const categoryCount = db.prepare('SELECT COUNT(*) AS count FROM service_categories').get().count;
if (!categoryCount) {
  const insertCategory = db.prepare('INSERT INTO service_categories (slug, title, sort_order) VALUES (?, ?, ?)');
  const categories = [
    ['print', 'Типография и полиграфия', 1],
    ['souvenir', 'Сувенирная продукция', 2],
    ['wide', 'Широкоформатная печать', 3],
    ['outdoor', 'Наружная реклама', 4]
  ];
  categories.forEach((category) => insertCategory.run(...category));

  const categoryIds = db.prepare('SELECT id, slug FROM service_categories').all();
  const ids = Object.fromEntries(categoryIds.map((item) => [item.slug, item.id]));

  const insertService = db.prepare(
    'INSERT INTO services (category_id, title, description, sort_order) VALUES (?, ?, ?, ?)'
  );
  const services = [
    [ids.print, 'Изготовление визиток', 'Дизайн и печать визиток на плотной бумаге.', 1],
    [ids.print, 'Печать буклетов и листовок', 'Цветная печать рекламных материалов.', 2],
    [ids.print, 'Печать фирменных бланков', 'Бланки, счета и сопроводительная документация.', 3],
    [ids.print, 'Изготовление календарей', 'Карманные, настенные и квартальные календари.', 4],
    [ids.souvenir, 'Нанесение логотипа на кружки', 'Фирменные кружки с устойчивой печатью.', 1],
    [ids.souvenir, 'Печать на футболках', 'Термоперенос и шелкография.', 2],
    [ids.souvenir, 'Сувенирные ручки с логотипом', 'Брендированные ручки для компаний.', 3],
    [ids.souvenir, 'Подарочные наборы для компаний', 'Сборка комплектов под ваш бренд.', 4],
    [ids.wide, 'Изготовление рекламных баннеров', 'Баннеры для улицы и помещений.', 1],
    [ids.wide, 'Печать наклеек для заднего и лобового стекла', 'Автомобильные наклейки и аппликации.', 2],
    [ids.wide, 'Печать на холсте', 'Интерьерная печать с высокими деталями.', 3],
    [ids.wide, 'Печать виниловых наклеек и стикеров', 'Износостойкие стикеры для разных поверхностей.', 4],
    [ids.outdoor, 'Изготовление световых коробов', 'Световые конструкции для фасадов.', 1],
    [ids.outdoor, 'Монтаж вывесок под ключ', 'Проект, изготовление и монтаж.', 2],
    [ids.outdoor, 'Оформление входных групп', 'Комплексное оформление входов в бизнес.', 3],
    [ids.outdoor, 'Брендирование фасадов и витрин', 'Плоттерная резка и оклейка.', 4]
  ];
  services.forEach((service) => insertService.run(...service));
}

const contactExists = db.prepare('SELECT id FROM contacts WHERE id = 1').get();
if (!contactExists) {
  db.prepare(
    `INSERT INTO contacts (id, email, phone_main, phone_alt, address_line1, address_line2, address_line3)
     VALUES (1, ?, ?, ?, ?, ?, ?)`
  ).run(
    'giraf33@mail.ru',
    '8 (4922) 46-64-84',
    '8 (958) 510-64-84',
    'Офис находится по адресу:',
    'г. Владимир, ул. Ставровская, д. 4',
    'ост. 1001 мелочь, парковка рядом с домом'
  );
}

export default db;

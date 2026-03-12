import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import initSqlJs from 'sql.js';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const dbPath = path.join(__dirname, '..', 'data.sqlite');

const SQL = await initSqlJs({
  locateFile: (file) => path.join(__dirname, '..', '..', '..', 'node_modules', 'sql.js', 'dist', file)
});

const dbBuffer = fs.existsSync(dbPath) ? fs.readFileSync(dbPath) : null;
const db = dbBuffer ? new SQL.Database(dbBuffer) : new SQL.Database();

const persist = () => {
  const data = db.export();
  fs.writeFileSync(dbPath, Buffer.from(data));
};

const run = (sql, params = []) => {
  db.run(sql, params);
};

const all = (sql, params = []) => {
  const stmt = db.prepare(sql);
  stmt.bind(params);
  const rows = [];
  while (stmt.step()) {
    rows.push(stmt.getAsObject());
  }
  stmt.free();
  return rows;
};

const get = (sql, params = []) => all(sql, params)[0];

const ensureSchema = () => {
  run(`
    CREATE TABLE IF NOT EXISTS service_categories (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      slug TEXT NOT NULL UNIQUE,
      title TEXT NOT NULL,
      sort_order INTEGER DEFAULT 0
    )
  `);

  run(`
    CREATE TABLE IF NOT EXISTS services (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      category_id INTEGER NOT NULL,
      title TEXT NOT NULL,
      description TEXT DEFAULT '',
      is_active INTEGER DEFAULT 1,
      sort_order INTEGER DEFAULT 0,
      FOREIGN KEY(category_id) REFERENCES service_categories(id)
    )
  `);

  run(`
    CREATE TABLE IF NOT EXISTS contacts (
      id INTEGER PRIMARY KEY CHECK (id = 1),
      email TEXT NOT NULL,
      phone_main TEXT NOT NULL,
      phone_alt TEXT NOT NULL,
      address_line1 TEXT NOT NULL,
      address_line2 TEXT NOT NULL,
      address_line3 TEXT NOT NULL
    )
  `);
};

const seedCategories = () => {
  const categoryCount = get('SELECT COUNT(*) AS count FROM service_categories')?.count ?? 0;
  if (categoryCount) {
    return;
  }

  const categories = [
    ['print', 'Типография и полиграфия', 1],
    ['souvenir', 'Сувенирная продукция', 2],
    ['wide', 'Широкоформатная печать', 3],
    ['outdoor', 'Наружная реклама', 4]
  ];

  categories.forEach(([slug, title, sortOrder]) => {
    run('INSERT INTO service_categories (slug, title, sort_order) VALUES (?, ?, ?)', [slug, title, sortOrder]);
  });
};

const seedServices = () => {
  const serviceCount = get('SELECT COUNT(*) AS count FROM services')?.count ?? 0;
  if (serviceCount) {
    return;
  }

  const categoryIds = all('SELECT id, slug FROM service_categories');
  const ids = Object.fromEntries(categoryIds.map((item) => [item.slug, item.id]));

  if (!ids.print || !ids.souvenir || !ids.wide || !ids.outdoor) {
    throw new Error('Категории услуг не инициализированы корректно');
  }

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

  services.forEach(([categoryId, title, description, sortOrder]) => {
    run('INSERT INTO services (category_id, title, description, sort_order) VALUES (?, ?, ?, ?)', [
      categoryId,
      title,
      description,
      sortOrder
    ]);
  });
};

const seedContacts = () => {
  const contactExists = get('SELECT id FROM contacts WHERE id = 1');
  if (contactExists) {
    return;
  }

  run(
    `INSERT INTO contacts (id, email, phone_main, phone_alt, address_line1, address_line2, address_line3)
     VALUES (1, ?, ?, ?, ?, ?, ?)`,
    [
      'giraf33@mail.ru',
      '8 (4922) 46-64-84',
      '8 (958) 510-64-84',
      'Офис находится по адресу:',
      'г. Владимир, ул. Ставровская, д. 4',
      'ост. 1001 мелочь, парковка рядом с домом'
    ]
  );
};

ensureSchema();
seedCategories();
seedServices();
seedContacts();
persist();

export const dbApi = {
  getCatalog(activeOnly = true) {
    const categories = all('SELECT id, slug, title, sort_order FROM service_categories ORDER BY sort_order');
    const servicesQuery = activeOnly
      ? 'SELECT id, category_id, title, description, sort_order, is_active FROM services WHERE is_active = 1 ORDER BY sort_order'
      : 'SELECT id, category_id, title, description, sort_order, is_active FROM services ORDER BY sort_order';
    const services = all(servicesQuery);

    return categories.map((category) => ({
      ...category,
      services: services.filter((service) => service.category_id === category.id)
    }));
  },

  getContacts() {
    return get('SELECT * FROM contacts WHERE id = 1');
  },

  updateService({ id, title, description, categoryId, isActive }) {
    const existing = get('SELECT id FROM services WHERE id = ?', [id]);
    if (!existing) return false;

    run(
      `UPDATE services
       SET title = ?, description = ?, category_id = ?, is_active = ?
       WHERE id = ?`,
      [title, description ?? '', categoryId, isActive ? 1 : 0, id]
    );
    persist();
    return true;
  },

  updateContacts({ email, phoneMain, phoneAlt, addressLine1, addressLine2, addressLine3 }) {
    run(
      `UPDATE contacts
       SET email = ?, phone_main = ?, phone_alt = ?, address_line1 = ?, address_line2 = ?, address_line3 = ?
       WHERE id = 1`,
      [email, phoneMain, phoneAlt, addressLine1, addressLine2, addressLine3]
    );
    persist();
  }
};

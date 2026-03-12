import Fastify from 'fastify';
import cors from '@fastify/cors';
import jwt from '@fastify/jwt';
import db from './db.js';

const app = Fastify({ logger: true });
const PORT = Number(process.env.PORT || 3001);
const JWT_SECRET = process.env.JWT_SECRET || 'jiraf-admin-secret';
const ADMIN_LOGIN = process.env.ADMIN_LOGIN || 'admin';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'admin123';

await app.register(cors, { origin: true });
await app.register(jwt, { secret: JWT_SECRET });

app.decorate('verifyAdmin', async function verifyAdmin(request, reply) {
  try {
    await request.jwtVerify();
  } catch {
    reply.code(401).send({ message: 'Необходима авторизация' });
  }
});

app.get('/health', async () => ({ ok: true }));

app.get('/api/catalog', async () => {
  const categories = db.prepare('SELECT id, slug, title, sort_order FROM service_categories ORDER BY sort_order').all();
  const services = db
    .prepare('SELECT id, category_id, title, description, sort_order FROM services WHERE is_active = 1 ORDER BY sort_order')
    .all();

  return categories.map((category) => ({
    ...category,
    services: services.filter((service) => service.category_id === category.id)
  }));
});

app.get('/api/contacts', async () => db.prepare('SELECT * FROM contacts WHERE id = 1').get());

app.post('/api/admin/login', async (request, reply) => {
  const { login, password } = request.body ?? {};

  if (login !== ADMIN_LOGIN || password !== ADMIN_PASSWORD) {
    return reply.code(401).send({ message: 'Неверный логин или пароль' });
  }

  const token = app.jwt.sign({ login, role: 'admin' }, { expiresIn: '8h' });
  return { token };
});

app.get('/api/admin/catalog', { preHandler: [app.verifyAdmin] }, async () => {
  const categories = db.prepare('SELECT id, slug, title, sort_order FROM service_categories ORDER BY sort_order').all();
  const services = db
    .prepare('SELECT id, category_id, title, description, is_active, sort_order FROM services ORDER BY sort_order')
    .all();

  return categories.map((category) => ({
    ...category,
    services: services.filter((service) => service.category_id === category.id)
  }));
});

app.put('/api/admin/services/:id', { preHandler: [app.verifyAdmin] }, async (request, reply) => {
  const { id } = request.params;
  const { title, description, categoryId, isActive } = request.body ?? {};

  const update = db.prepare(
    `UPDATE services
     SET title = ?, description = ?, category_id = ?, is_active = ?
     WHERE id = ?`
  );

  const result = update.run(title, description ?? '', categoryId, isActive ? 1 : 0, id);

  if (!result.changes) {
    return reply.code(404).send({ message: 'Услуга не найдена' });
  }

  return { ok: true };
});

app.put('/api/admin/contacts', { preHandler: [app.verifyAdmin] }, async (request) => {
  const { email, phoneMain, phoneAlt, addressLine1, addressLine2, addressLine3 } = request.body ?? {};

  db.prepare(
    `UPDATE contacts
     SET email = ?, phone_main = ?, phone_alt = ?, address_line1 = ?, address_line2 = ?, address_line3 = ?
     WHERE id = 1`
  ).run(email, phoneMain, phoneAlt, addressLine1, addressLine2, addressLine3);

  return { ok: true };
});

app.listen({ host: '0.0.0.0', port: PORT });

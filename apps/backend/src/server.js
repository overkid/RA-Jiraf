import Fastify from 'fastify';
import cors from '@fastify/cors';
import jwt from '@fastify/jwt';
import { dbApi } from './db.js';

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

app.get('/api/catalog', async () => dbApi.getCatalog(true));

app.get('/api/contacts', async () => dbApi.getContacts());

app.post('/api/admin/login', async (request, reply) => {
  const { login, password } = request.body ?? {};

  if (login !== ADMIN_LOGIN || password !== ADMIN_PASSWORD) {
    return reply.code(401).send({ message: 'Неверный логин или пароль' });
  }

  const token = app.jwt.sign({ login, role: 'admin' }, { expiresIn: '8h' });
  return { token };
});

app.get('/api/admin/catalog', { preHandler: [app.verifyAdmin] }, async () => dbApi.getCatalog(false));

app.put('/api/admin/services/:id', { preHandler: [app.verifyAdmin] }, async (request, reply) => {
  const { id } = request.params;
  const { title, description, categoryId, isActive } = request.body ?? {};

  const updated = dbApi.updateService({
    id: Number(id),
    title,
    description,
    categoryId,
    isActive
  });

  if (!updated) {
    return reply.code(404).send({ message: 'Услуга не найдена' });
  }

  return { ok: true };
});

app.put('/api/admin/contacts', { preHandler: [app.verifyAdmin] }, async (request) => {
  const { email, phoneMain, phoneAlt, addressLine1, addressLine2, addressLine3 } = request.body ?? {};

  dbApi.updateContacts({ email, phoneMain, phoneAlt, addressLine1, addressLine2, addressLine3 });
  return { ok: true };
});

app.listen({ host: '0.0.0.0', port: PORT });

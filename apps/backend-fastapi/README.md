# backend-fastapi

FastAPI backend for RA-Jiraf.

## Run

```bash
python -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
uvicorn app.main:app --reload --port 8000
```

Default admin credentials:
- login: `admin`
- password: `admin123`

You can override by env vars:
- `DATABASE_URL` (default `sqlite:///./app.db`)
- `ADMIN_LOGIN`
- `ADMIN_PASSWORD`
- `JWT_SECRET`

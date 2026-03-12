from datetime import datetime, timedelta, timezone
from decimal import Decimal
import os
from typing import Optional

import jwt
from fastapi import Depends, FastAPI, HTTPException, status
from fastapi.middleware.cors import CORSMiddleware
from fastapi.security import HTTPAuthorizationCredentials, HTTPBearer
from passlib.context import CryptContext
from pydantic import BaseModel, ConfigDict
from sqlalchemy import Boolean, DateTime, ForeignKey, Numeric, String, Text, create_engine
from sqlalchemy.orm import DeclarativeBase, Mapped, Session, mapped_column, relationship, sessionmaker

DATABASE_URL = os.getenv("DATABASE_URL", "sqlite:///./app.db")
JWT_SECRET = os.getenv("JWT_SECRET", "change-me-in-production")
JWT_ALG = "HS256"
JWT_EXP_HOURS = int(os.getenv("JWT_EXP_HOURS", "12"))
ADMIN_LOGIN = os.getenv("ADMIN_LOGIN", "admin")
ADMIN_PASSWORD = os.getenv("ADMIN_PASSWORD", "admin123")


class Base(DeclarativeBase):
    pass


engine = create_engine(DATABASE_URL, future=True)
SessionLocal = sessionmaker(bind=engine, autoflush=False, autocommit=False)
pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")
security = HTTPBearer()


class AdminUser(Base):
    __tablename__ = "admin_users"

    id: Mapped[int] = mapped_column(primary_key=True)
    login: Mapped[str] = mapped_column(String(100), unique=True, index=True)
    password_hash: Mapped[str] = mapped_column(String(255))
    is_active: Mapped[bool] = mapped_column(Boolean, default=True)
    created_at: Mapped[datetime] = mapped_column(DateTime, default=lambda: datetime.now(timezone.utc))


class ContactInfo(Base):
    __tablename__ = "contact_info"

    id: Mapped[int] = mapped_column(primary_key=True)
    company_name: Mapped[str] = mapped_column(String(120))
    phone: Mapped[str] = mapped_column(String(50))
    email: Mapped[str] = mapped_column(String(120))
    address: Mapped[str] = mapped_column(String(255))
    work_hours: Mapped[str] = mapped_column(String(120))


class ServiceCategory(Base):
    __tablename__ = "service_categories"

    id: Mapped[int] = mapped_column(primary_key=True)
    name: Mapped[str] = mapped_column(String(120), unique=True)
    sort_order: Mapped[int] = mapped_column(default=0)
    is_active: Mapped[bool] = mapped_column(Boolean, default=True)

    services: Mapped[list["ServiceItem"]] = relationship(back_populates="category", cascade="all, delete-orphan")


class ServiceItem(Base):
    __tablename__ = "service_items"

    id: Mapped[int] = mapped_column(primary_key=True)
    category_id: Mapped[int] = mapped_column(ForeignKey("service_categories.id"))
    title: Mapped[str] = mapped_column(String(120))
    short_description: Mapped[str] = mapped_column(String(255))
    price_from: Mapped[Decimal | None] = mapped_column(Numeric(10, 2), nullable=True)
    image_path: Mapped[str | None] = mapped_column(String(255), nullable=True)
    is_active: Mapped[bool] = mapped_column(Boolean, default=True)

    category: Mapped[ServiceCategory] = relationship(back_populates="services")


class UserRequest(Base):
    __tablename__ = "user_requests"

    id: Mapped[int] = mapped_column(primary_key=True)
    name: Mapped[str] = mapped_column(String(120))
    phone: Mapped[str] = mapped_column(String(50))
    email: Mapped[str | None] = mapped_column(String(120), nullable=True)
    message: Mapped[str] = mapped_column(Text)
    status: Mapped[str] = mapped_column(String(20), default="new")
    manager_comment: Mapped[str | None] = mapped_column(Text, nullable=True)
    created_at: Mapped[datetime] = mapped_column(DateTime, default=lambda: datetime.now(timezone.utc))


class ContactOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)
    company_name: str
    phone: str
    email: str
    address: str
    work_hours: str


class ContactIn(BaseModel):
    company_name: str
    phone: str
    email: str
    address: str
    work_hours: str


class ServiceItemOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)
    id: int
    title: str
    short_description: str
    price_from: Optional[float] = None
    image_path: Optional[str] = None


class ServiceCategoryOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)
    id: int
    name: str
    services: list[ServiceItemOut]


class RequestCreate(BaseModel):
    name: str
    phone: str
    email: Optional[str] = None
    message: str


class AdminLogin(BaseModel):
    login: str
    password: str


class RequestOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)
    id: int
    name: str
    phone: str
    email: Optional[str] = None
    message: str
    status: str
    manager_comment: Optional[str] = None
    created_at: datetime


class RequestPatch(BaseModel):
    status: str
    manager_comment: Optional[str] = None


class CategoryIn(BaseModel):
    name: str
    sort_order: int = 0
    is_active: bool = True


class ServiceIn(BaseModel):
    category_id: int
    title: str
    short_description: str
    price_from: Optional[float] = None
    image_path: Optional[str] = None
    is_active: bool = True


app = FastAPI(title="RA Jiraf API")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()


def create_access_token(user_id: int, login: str) -> str:
    payload = {
        "sub": str(user_id),
        "login": login,
        "exp": datetime.now(timezone.utc) + timedelta(hours=JWT_EXP_HOURS),
    }
    return jwt.encode(payload, JWT_SECRET, algorithm=JWT_ALG)


def get_current_admin(
    credentials: HTTPAuthorizationCredentials = Depends(security), db: Session = Depends(get_db)
) -> AdminUser:
    try:
        payload = jwt.decode(credentials.credentials, JWT_SECRET, algorithms=[JWT_ALG])
        user_id = int(payload["sub"])
    except Exception as exc:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Invalid token") from exc

    user = db.get(AdminUser, user_id)
    if not user or not user.is_active:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="User not found")
    return user


def seed_data(db: Session) -> None:
    if not db.query(AdminUser).first():
        db.add(
            AdminUser(
                login=ADMIN_LOGIN,
                password_hash=pwd_context.hash(ADMIN_PASSWORD),
            )
        )

    if not db.query(ContactInfo).first():
        db.add(
            ContactInfo(
                company_name="RA Jiraf",
                phone="+7 (900) 000-00-00",
                email="info@rajiraf.ru",
                address="г. Омск, ул. Примерная, 1",
                work_hours="Пн-Пт 09:00-18:00",
            )
        )

    if not db.query(ServiceCategory).first():
        polig = ServiceCategory(name="Полиграфия", sort_order=1)
        suvenir = ServiceCategory(name="Сувенирная продукция", sort_order=2)
        db.add_all([polig, suvenir])
        db.flush()
        db.add_all(
            [
                ServiceItem(
                    category_id=polig.id,
                    title="Визитки",
                    short_description="Печать визиток с вашим дизайном",
                    price_from=500,
                ),
                ServiceItem(
                    category_id=polig.id,
                    title="Широкоформатная печать",
                    short_description="Баннеры, постеры, плакаты",
                    price_from=1500,
                ),
                ServiceItem(
                    category_id=suvenir.id,
                    title="Печать на кружках",
                    short_description="Именные кружки для бизнеса и подарков",
                    price_from=700,
                ),
            ]
        )
    db.commit()


@app.on_event("startup")
def startup_event():
    Base.metadata.create_all(bind=engine)
    with SessionLocal() as db:
        seed_data(db)


@app.get("/api/health")
def healthcheck():
    return {"status": "ok"}


@app.get("/api/public/contacts", response_model=ContactOut)
def get_contacts(db: Session = Depends(get_db)):
    contact = db.query(ContactInfo).first()
    if not contact:
        raise HTTPException(status_code=404, detail="Contacts not found")
    return contact


@app.get("/api/public/services", response_model=list[ServiceCategoryOut])
def get_services(db: Session = Depends(get_db)):
    categories = (
        db.query(ServiceCategory)
        .filter(ServiceCategory.is_active.is_(True))
        .order_by(ServiceCategory.sort_order.asc())
        .all()
    )
    return categories


@app.post("/api/public/requests")
def create_request(payload: RequestCreate, db: Session = Depends(get_db)):
    db.add(UserRequest(**payload.model_dump()))
    db.commit()
    return {"ok": True}


@app.post("/api/admin/login")
def admin_login(payload: AdminLogin, db: Session = Depends(get_db)):
    admin = db.query(AdminUser).filter(AdminUser.login == payload.login).first()
    if not admin or not pwd_context.verify(payload.password, admin.password_hash):
        raise HTTPException(status_code=401, detail="Wrong login or password")
    return {"access_token": create_access_token(admin.id, admin.login)}


@app.get("/api/admin/contacts", response_model=ContactOut)
def admin_get_contacts(_: AdminUser = Depends(get_current_admin), db: Session = Depends(get_db)):
    return db.query(ContactInfo).first()


@app.put("/api/admin/contacts", response_model=ContactOut)
def admin_update_contacts(
    payload: ContactIn, _: AdminUser = Depends(get_current_admin), db: Session = Depends(get_db)
):
    contact = db.query(ContactInfo).first()
    for key, value in payload.model_dump().items():
        setattr(contact, key, value)
    db.commit()
    db.refresh(contact)
    return contact


@app.get("/api/admin/categories", response_model=list[ServiceCategoryOut])
def admin_categories(_: AdminUser = Depends(get_current_admin), db: Session = Depends(get_db)):
    return db.query(ServiceCategory).order_by(ServiceCategory.sort_order.asc()).all()


@app.post("/api/admin/categories", response_model=ServiceCategoryOut)
def admin_create_category(
    payload: CategoryIn, _: AdminUser = Depends(get_current_admin), db: Session = Depends(get_db)
):
    row = ServiceCategory(**payload.model_dump())
    db.add(row)
    db.commit()
    db.refresh(row)
    return row


@app.put("/api/admin/categories/{category_id}", response_model=ServiceCategoryOut)
def admin_update_category(
    category_id: int, payload: CategoryIn, _: AdminUser = Depends(get_current_admin), db: Session = Depends(get_db)
):
    row = db.get(ServiceCategory, category_id)
    if not row:
        raise HTTPException(status_code=404, detail="Category not found")
    for key, value in payload.model_dump().items():
        setattr(row, key, value)
    db.commit()
    db.refresh(row)
    return row


@app.post("/api/admin/services", response_model=ServiceItemOut)
def admin_create_service(
    payload: ServiceIn, _: AdminUser = Depends(get_current_admin), db: Session = Depends(get_db)
):
    row = ServiceItem(**payload.model_dump())
    db.add(row)
    db.commit()
    db.refresh(row)
    return row


@app.put("/api/admin/services/{service_id}", response_model=ServiceItemOut)
def admin_update_service(
    service_id: int, payload: ServiceIn, _: AdminUser = Depends(get_current_admin), db: Session = Depends(get_db)
):
    row = db.get(ServiceItem, service_id)
    if not row:
        raise HTTPException(status_code=404, detail="Service not found")
    for key, value in payload.model_dump().items():
        setattr(row, key, value)
    db.commit()
    db.refresh(row)
    return row


@app.delete("/api/admin/services/{service_id}")
def admin_delete_service(service_id: int, _: AdminUser = Depends(get_current_admin), db: Session = Depends(get_db)):
    row = db.get(ServiceItem, service_id)
    if not row:
        raise HTTPException(status_code=404, detail="Service not found")
    db.delete(row)
    db.commit()
    return {"ok": True}


@app.get("/api/admin/requests", response_model=list[RequestOut])
def admin_requests(_: AdminUser = Depends(get_current_admin), db: Session = Depends(get_db)):
    return db.query(UserRequest).order_by(UserRequest.created_at.desc()).all()


@app.patch("/api/admin/requests/{request_id}", response_model=RequestOut)
def admin_patch_request(
    request_id: int, payload: RequestPatch, _: AdminUser = Depends(get_current_admin), db: Session = Depends(get_db)
):
    row = db.get(UserRequest, request_id)
    if not row:
        raise HTTPException(status_code=404, detail="Request not found")
    row.status = payload.status
    row.manager_comment = payload.manager_comment
    db.commit()
    db.refresh(row)
    return row

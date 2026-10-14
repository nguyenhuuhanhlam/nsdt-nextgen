from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker

from app.core.config import settings

# pool_pre_ping: tránh dùng connection đã chết sau thời gian idle dài
# (quan trọng với MySQL trên server nội bộ)
engine = create_engine(
    settings.database_url,
    pool_pre_ping=True,
    pool_size=10,
    max_overflow=20,
)

SessionLocal = sessionmaker(bind=engine, autoflush=False, autocommit=False)

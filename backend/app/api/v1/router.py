from fastapi import APIRouter

from app.api.v1.attendance import router as attendance_router
from app.api.v1.catalogs import router as catalogs_router
from app.api.v1.employees import router as employees_router
from app.api.v1.maternity import router as maternity_router

api_router = APIRouter()
api_router.include_router(employees_router)
api_router.include_router(catalogs_router)
api_router.include_router(maternity_router)
api_router.include_router(attendance_router)

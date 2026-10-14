from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    model_config = SettingsConfigDict(env_file=".env", extra="ignore")

    app_name: str = "nsdt-nextgen"
    database_url: str = "mysql+pymysql://nsdt:nsdt@db:3306/nsdt?charset=utf8mb4"
    api_prefix: str = "/api/v1"
    # comma-separated list, e.g. "http://localhost:5173,http://10.0.0.5:5173"
    cors_origins: str = "http://localhost:5173"


settings = Settings()

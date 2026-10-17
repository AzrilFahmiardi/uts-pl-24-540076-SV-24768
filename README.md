# Inventaris Alat Lab

Sistem inventaris alat laboratorium: REST API Laravel (Sanctum), antarmuka Vue 3, dijalankan dengan Docker Compose. Backend memakai FrankenPHP.

## Struktur
- `backend/` Laravel 12 + Sanctum
- `frontend/` Vue 3 + Vite + Axios
- `postman/` Postman Collection untuk menguji API
- `docker-compose.yml` menjalankan db (MySQL), backend, dan frontend (Nginx)

## Menjalankan
```bash
cp .env.example .env
# isi APP_KEY (php artisan key:generate --show di folder backend/) dan password
docker compose up -d --build
```
- Aplikasi: http://localhost:8080
- API langsung: http://localhost:8000/api

Login awal memakai `ADMIN_EMAIL` dan `ADMIN_PASSWORD` dari `.env`.

## Pengujian
```bash
cd backend && php artisan test
```

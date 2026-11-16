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

## Monitoring (Prometheus + Grafana)
`docker compose up -d` juga menjalankan prometheus, grafana, cadvisor, dan node-exporter (versi image dikunci, data di volume `prometheus_data` dan `grafana_data`).
- Prometheus: http://localhost:9090 (`/targets`, `/alerts`)
- Grafana: http://localhost:3000, user `admin`, password dari `GRAFANA_ADMIN_PASSWORD` di `.env` (wajib diisi, jangan `admin`)
- Dashboard "Inventaris Alat Lab - Kesehatan Aplikasi" otomatis dimuat dari `monitoring/grafana/dashboards/`
- Aturan alarm: `monitoring/alerts.yml` (`BackendDown`, `for: 30s`)
- Metrik permintaan HTTP berasal dari endpoint `/metrics` Caddy di container backend (hanya dari jaringan privat)

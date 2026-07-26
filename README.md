# Sobres

![Laravel](https://img.shields.io/badge/Laravel-11-red)
![PHP](https://img.shields.io/badge/PHP-8.2-777bb4)
![Docker](https://img.shields.io/badge/Docker-ready-0db7ed)
![MySQL](https://img.shields.io/badge/MySQL-8.0-blue)
![License](https://img.shields.io/badge/license-MIT-green)

Sistema para acomodar las imágenes para los sobres de mi mamá.

## Requisitos

* Git
* Docker
* Docker Compose

## Clonar el proyecto

```bash
git clone https://github.com/Jhon-mantila/sobres.git
cd sobres
```

## Levantar el proyecto en local

Entra a la carpeta donde está Docker y levanta los contenedores:

```bash
cd docker
docker compose up --build
```

Si tu instalación usa el comando antiguo:

```bash
docker-compose up -d --build
```

## Instalar dependencias

Backend:

```bash
docker compose exec app composer install
```

Frontend:

```bash
docker compose exec node npm install
```

## Configurar el archivo .env

Copia el archivo de ejemplo:

```bash
cp .env.example .env
```

Luego revisa la conexión a la base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=sobres
DB_USERNAME=sobres_user
DB_PASSWORD=sobres_pass
```

## Generar la clave de Laravel

```bash
docker compose exec app php artisan key:generate
```

## Migraciones

```bash
docker compose exec app php artisan migrate
```

Si necesitas datos de prueba:

```bash
docker compose exec app php artisan migrate --seed
```

## Levantar Vite

```bash
docker compose exec node npm run dev -- --host 0.0.0.0 --port 5173
```

## Modo producción sin Levantar Vite

```bash
docker compose exec node npm run build
```

## Levantar las imagenes para que se vean

```bash
docker compose exec app php artisan storage:link
```
## Acceso local

* Aplicación: `http://localhost:8087`
* phpMyAdmin: `http://localhost:8088`
* Vite: `http://localhost:5173`

## Detener el proyecto

```bash
docker compose down
```

Para borrar también la base de datos local:

```bash
docker compose down -v
```

# Citas Neurología - Proyecto Laravel

## Requisitos Previos

- PHP >= 8.x
- Composer
- Node.js y npm
- PostgreSQL instalado y funcionando

## Instalación

1. Clona el repositorio:

```bash
git clone <url-del-repositorio>
cd citas-neurologia
```

2. Instala Jetstream con Inertia y Vue:

```bash
composer require laravel/jetstream
php artisan jetstream:install inertia
npm install
npm install @vitejs/plugin-vue@^6 --save-dev
npm install vue-cal
composer require league/flysystem-aws-s3-v3 ~3.0
```

3. Configura PostgreSQL editando el archivo `.env` en la raíz del proyecto:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=citas_neurologia
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña

APPOINTMENT_DURATION_MINUTES=30

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_username_mailtrap
MAIL_PASSWORD=tu_password_mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@citasneurologia.com"
MAIL_FROM_NAME="Citas Neurología"
```

4. Crea la base de datos `citas_neurologia` en PostgreSQL:

```bash
psql -U tu_usuario
CREATE DATABASE citas_neurologia;
\q
```

5. Ejecuta las migraciones para crear las tablas necesarias:

```bash
php artisan migrate
```

6. Ejecuta los seeders

```bash
php artisan db:seed
```



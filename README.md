# SICA-EPO6

Sistema de Identificacion y Control de Acceso (Laravel 12 + MySQL).

## Instalacion en XAMPP (Windows)
1. Copia esta carpeta a `C:\xampp\htdocs\sica-epo6`.
2. Abre el Panel de XAMPP y da **Start** en **MySQL**.
3. Haz doble clic en `instalar.bat` (crea el .env, la clave, la base de datos, las tablas y las cuentas).
4. Haz doble clic en `iniciar.bat` y se abre el sistema en http://127.0.0.1:8000
   (deja abierta la ventana negra mientras uses el sistema).

Requisitos: XAMPP con PHP 8.2 o mayor y Composer (https://getcomposer.org/download/) solo si falta la carpeta `vendor`.
En `C:\xampp\php\php.ini` deben estar activas (sin `;`): `extension=zip`, `extension=gd`, `extension=mbstring`, `extension=intl`, `extension=fileinfo`.

Cuentas iniciales: ver `database/seeders/UsuarioSeeder.php` (contrasena inicial `prepa06`; cambiala desde Control Escolar).
Si aparece "500 Server Error": pon `APP_DEBUG=true` en `.env` o revisa `storage/logs/laravel.log`.

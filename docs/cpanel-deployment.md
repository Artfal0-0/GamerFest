# Despliegue de GamerFest en cPanel

Esta guía es para un hosting cPanel con PHP 8.2 o superior, MySQL, Composer y acceso a Terminal/SSH. Los nombres de menús, comandos y rutas pueden variar según el proveedor.

## 1. Confirma los requisitos con el hosting

Antes de subir el proyecto, confirma:

- PHP 8.2 o superior para el dominio y para la terminal CLI.
- Extensiones PHP requeridas por Composer, en particular `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `xml`, `dom`, `ctype`, `curl` y `zip`.
- Composer 2 y acceso a cPanel Terminal o SSH.
- MySQL/MariaDB, certificados SSL, tareas Cron y permisos de escritura para `storage/` y `bootstrap/cache/`.
- Si el plan permite cambiar el document root del dominio a una carpeta fuera de `public_html`, y cómo mantener workers de cola.

La aplicación requiere PHP `^8.2` según `composer.json`. No uses `php artisan serve` en producción.

## 2. Prepara los archivos en tu computadora

Desde PowerShell, en la carpeta del proyecto:

```powershell
npm ci
npm run build
```

Antes del build, configura los valores reales `VITE_BILLING_NAME`, `VITE_BILLING_ACCOUNT`, `VITE_BILLING_BANK`, `VITE_BILLING_CI` y `VITE_BILLING_TYPE` en tu entorno local o en un `.env.production` local. El prefijo `VITE_` significa que esos valores quedan visibles en el JavaScript público: solo deben ser datos de transferencia que el sitio ya muestra a los usuarios; nunca pongas contraseñas ni claves privadas ahí.

El build genera `public/build`, que debe incluirse al subir el proyecto. No subas `node_modules/`, `.git/`, tu `.env` local ni copias de bases de datos. Conserva `composer.lock` y `package-lock.json`.

Puedes usar el administrador de archivos o Git para subir el código. Lo recomendable es guardar la aplicación completa fuera del directorio público, por ejemplo:

```text
/home/USUARIOCPANEL/gamerfest
```

No pongas el proyecto completo dentro de `public_html`: contiene configuración, código y archivos privados.

## 3. Crea la base de datos y su usuario

En cPanel abre **MySQL Database Wizard** (o **Manage My Databases**):

1. Crea una base de datos y un usuario con una contraseña fuerte.
2. Asigna el usuario a la base y concede todos los privilegios para esa base.
3. Anota el nombre completo de base, usuario y contraseña. cPanel normalmente les antepone `USUARIOCPANEL_`.
4. El host suele ser `localhost`, pero confirma el valor con tu hosting.

No importes la base local si quieres empezar con datos vacíos. Si ya hay datos que conservar, haz un respaldo y coordina la importación antes de migrar.

## 4. Configura el dominio para apuntar a `public/`

En **cPanel → Domains → Manage**, establece el document root del dominio en:

```text
/home/USUARIOCPANEL/gamerfest/public
```

Usa la ruta relativa que solicite cPanel. Solo la carpeta `public/` debe ser accesible desde internet. Activa SSL/AutoSSL y confirma que el dominio abre por HTTPS.

El dominio principal puede no permitir cambiar su document root. En ese caso:

1. Mantén la aplicación completa en `/home/USUARIOCPANEL/gamerfest`, fuera de `public_html`.
2. En el administrador de archivos activa **Show Hidden Files** y copia **el contenido** de `gamerfest/public/` a `public_html/` (incluye `.htaccess`, `index.php`, `build/`, `imgGames/`, `images/` y `reglamento/`).
3. Edita `public_html/index.php` para apuntar a las rutas absolutas reales del proyecto. Cambia las tres rutas relativas para que queden así, sustituyendo `USUARIOCPANEL` si corresponde:

```php
if (file_exists($maintenance = '/home/USUARIOCPANEL/gamerfest/storage/framework/maintenance.php')) {
    require $maintenance;
}

require '/home/USUARIOCPANEL/gamerfest/vendor/autoload.php';
$app = require_once '/home/USUARIOCPANEL/gamerfest/bootstrap/app.php';
```

No copies `.env` ni las carpetas `app/`, `vendor/`, `storage/` o `database/` dentro de `public_html`. Si el hosting no permite que el código esté fuera del document root, pide instrucciones al soporte antes de publicarlo.

## 5. Crea el `.env` privado en el servidor

En el directorio raíz de la aplicación, crea un archivo llamado `.env` a partir de `.env.cpanel.example`. Complétalo con:

- `APP_URL=https://tu-dominio-real`
- Los nombres y credenciales reales de MySQL.
- SMTP que indique tu hosting/proveedor. No asumas que `mail.tu-dominio.com` y el puerto 587 son los correctos; verifica su guía.

El archivo `.env` debe permanecer en la raíz privada de la aplicación y fuera de `public_html`. No lo envíes por correo ni lo subas a Git.

En cPanel selecciona PHP 8.2+ para el dominio desde **MultiPHP Manager**. En Terminal comprueba que la CLI usa una versión compatible:

```bash
php -v
composer --version
```

Si `php -v` muestra otra versión o Composer no está disponible, pide al proveedor el comando de PHP/Composer correspondiente antes de seguir.

## 6. Importa el SQL de la base de datos

Antes de importar, abre `database/gamerfest_cpanel.sql` y reemplaza:

- `@ADMIN_EMAIL` por un correo real que controles.
- `@ADMIN_BCRYPT_HASH` por un hash bcrypt generado a partir de una contraseña fuerte. No pongas la contraseña en texto plano en el SQL.

Puedes generar el hash en Terminal con PHP; escribe la contraseña cuando el comando la solicite:

```bash
php -r 'echo password_hash(trim(fgets(STDIN)), PASSWORD_BCRYPT, ["cost" => 12]), PHP_EOL;'
```

En **cPanel → phpMyAdmin**, selecciona la base de datos vacía que creaste y usa **Import** para subir el archivo SQL ya editado. El archivo crea todas las tablas, carga los juegos fijos, roles/permisos y el administrador, y registra las migraciones incluidas como aplicadas. No contiene sentencias `DROP TABLE`, `DELETE FROM` ni `TRUNCATE`; impórtalo una sola vez en una base vacía. Conserva las reglas `ON DELETE CASCADE` que ya definen algunas claves foráneas de las migraciones; no son instrucciones que se ejecuten durante la importación.

Después de importar, comprueba que existen 12 juegos, los roles `admin` y `jugador`, los permisos y el usuario administrador. El administrador queda con correo verificado, pero sin teléfono; al iniciar sesión debe completar el teléfono solicitado por la aplicación.

## 7. Instala dependencias y prepara Laravel

En Terminal:

```bash
cd /home/USUARIOCPANEL/gamerfest
composer install --no-dev --optimize-autoloader --no-interaction
php artisan key:generate --force
php artisan migrate --force
php artisan migrate:status
```

`migrate:status` debe mostrar las migraciones incluidas en el SQL como aplicadas. No ejecutes `php artisan db:seed`: los datos iniciales se cargan desde el SQL y el seeder sincroniza el catálogo de juegos.

Genera `APP_KEY` solo en la instalación inicial. Nunca ejecutes `key:generate` en una instalación existente: cambiar la clave puede invalidar sesiones y datos cifrados.

Si copiaste el contenido de `public/` a `public_html/`, verifica allí `build/` y las carpetas de recursos públicos.

## 8. Permisos y cachés de producción

Desde la raíz privada de la aplicación:

```bash
chmod -R u+rwX,g+rwX storage bootstrap/cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

No habilites `APP_DEBUG=true` en producción. Si aparece un error, revisa `storage/logs/laravel-*.log` desde el servidor y no muestres el contenido al público.

Los comprobantes se guardan en `storage/app/private`; por ello, `storage/` debe ser escribible y permanecer fuera del document root. No conviertas ese almacenamiento privado en una carpeta pública.

## 9. Deja funcionando la cola de correos

Los correos de registro y reenvío son trabajos encolados. Sin un worker, la aplicación puede indicar que solicitó el reenvío aunque el mensaje permanezca pendiente.

**Opción preferida:** si el hosting ofrece Supervisor o un gestor de procesos, solicita un worker persistente con:

```bash
php /home/USUARIOCPANEL/gamerfest/artisan queue:work database --sleep=3 --tries=3 --timeout=90
```

Configúralo para que reinicie automáticamente al fallar o al desplegar.

**Si solo hay Cron:** crea una tarea en **cPanel → Cron Jobs** para ejecutarse cada minuto y procesar la cola cuando haya trabajos:

```bash
/RUTA/REAL/DE/PHP /home/USUARIOCPANEL/gamerfest/artisan queue:work database --stop-when-empty --tries=3 --timeout=90
```

Reemplaza la ruta de PHP con la que confirme tu proveedor. Si existe `flock`, úsalo para evitar que dos workers del Cron se ejecuten simultáneamente:

```bash
/usr/bin/flock -n /home/USUARIOCPANEL/.gamerfest-queue.lock /RUTA/REAL/DE/PHP /home/USUARIOCPANEL/gamerfest/artisan queue:work database --stop-when-empty --tries=3 --timeout=90
```

Si no tienes un worker persistente, Cron o acceso a `flock`, contacta con el hosting: el envío y reenvío de correos no se procesará de forma confiable sin un worker.

Tras configurar SMTP, registra una cuenta de prueba, comprueba el mensaje y prueba **Reenviar correo**. Para revisar fallos:

```bash
php /home/USUARIOCPANEL/gamerfest/artisan queue:failed
```

## 10. Verificación final

1. Abre `https://tu-dominio-real/up`; Laravel debe responder `OK`.
2. Registra una cuenta con un email que controles y confirma que llega el mensaje de verificación.
3. Prueba iniciar sesión, cerrar sesión, guardar perfil, cargar y consultar un comprobante.
4. Prueba una inscripción y verifica en el panel de administración los datos guardados.
5. Si un correo no llega, comprueba SMTP, Spam, la cola y `storage/logs/`; no vuelvas a mostrar ni publiques errores detallados.
6. Configura copias de seguridad regulares de la base de datos y de `storage/app/private`.

## 11. Actualizaciones posteriores

Antes de cada actualización, respalda la base de datos y `storage/app/private`. Sube los archivos nuevos y ejecuta, desde la raíz privada:

```bash
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

Compila `public/build` en tu computadora con `npm ci` y `npm run build` en cada versión que cambie el frontend, y sube el build junto con los archivos de la aplicación.

## Documentación oficial de cPanel

- [Terminal](https://docs.cpanel.net/cpanel/advanced/terminal-in-cpanel/)
- [Administrar el document root del dominio](https://docs.cpanel.net/cpanel/domains/domains/manage-the-domain/)
- [Seleccionar PHP con MultiPHP Manager](https://docs.cpanel.net/cpanel/software/multiphp-manager-for-cpanel/)
- [Crear base de datos con MySQL Database Wizard](https://docs.cpanel.net/cpanel/databases/mysql-database-wizard/)
- [Configurar Cron Jobs](https://docs.cpanel.net/cpanel/advanced/cron-jobs/)

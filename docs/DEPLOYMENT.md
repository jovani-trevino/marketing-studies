# Despliegue y operación

## Mantener la instalación recuperada

El código no incluye una base de producción, una cuenta predeterminada ni las credenciales del alojamiento. Conserva la base existente, la configuración privada y los archivos subidos. No reemplaces una base con `public-content.json` ni con una base generada por la vista local.

Antes de cambiar el sitio, crea un respaldo verificable de la base y de `blog/public/uploads/`, conserva la versión anterior del código y prueba la nueva versión sobre una copia aislada. En el primer acceso PHP ejecuta cambios de esquema aditivos. Esto conserva los registros, pero sigue siendo una operación sobre la base: el respaldo es necesario antes de actualizar.

## Configuración privada

Utiliza variables de entorno disponibles para PHP-FPM/Apache o copia `blog/app/config.local.example.php` a una ruta privada. `MS_CONFIG_FILE` puede señalar un archivo fuera del checkout; si no se define, se busca `blog/app/config.local.php`. Las variables de base de datos, rutas y acceso enumeradas a continuación tienen prioridad sobre el archivo local.

| Variable | Uso |
| --- | --- |
| `MS_APP_URL` | Origen público HTTPS, por ejemplo `https://marketing-studies.com`. |
| `MS_DB_DRIVER` | `sqlite` o `mysql`. |
| `MS_STORAGE_PATH` | Directorio privado de datos persistentes. |
| `MS_SQLITE_PATH` | Ruta absoluta de la base SQLite existente. |
| `MS_SESSION_PATH` | Directorio privado de sesiones PHP. |
| `MS_RATE_LIMIT_PATH` | Directorio privado de límites de solicitudes. |
| `MS_CONFIG_FILE` | Ruta absoluta de la configuración PHP privada. |
| `MS_DB_HOST`, `MS_DB_PORT` | Servidor y puerto MySQL. |
| `MS_DB_NAME`, `MS_DB_USER`, `MS_DB_PASSWORD` | Base y credenciales MySQL. |
| `MS_TRUST_PROXY` | Mantener `false`; usar `true` sólo detrás de un proxy controlado que reescriba los encabezados de origen. |
| `MS_ADMIN_USER`, `MS_ADMIN_PASSWORD_HASH` | Configuración externa opcional de acceso; preferir la cuenta persistida en la base. |
| `MS_BLOG_BASE_PATH` | Sólo para el portafolio si el blog se monta aparte; vacío en la raíz pública unificada. |

Las rutas privadas deben ser escribibles por el usuario que ejecuta PHP y no accesibles desde HTTP. No otorgues escritura sobre todo el código. El directorio de archivos subidos necesita escritura de PHP y puede enlazarse a un directorio persistente compartido entre versiones. Asegura que `MS_SQLITE_PATH`, `MS_SESSION_PATH` y `MS_RATE_LIMIT_PATH` apunten al mismo entorno para el blog y el portafolio.

La conexión SMTP y ORCID se configuran en el archivo PHP privado o mediante los controles existentes del CMS. Evita reutilizar secretos que hayan aparecido en el historial público. No actives envíos ni conectes credenciales reales en el entorno de prueba.

## Raíz pública

Configura el **DocumentRoot en `<checkout>/public/`**, no en la raíz del repositorio. El despachador publica únicamente las rutas y extensiones permitidas; la aplicación, la base, los archivos SQL y la configuración quedan fuera de esa raíz.

Para Apache, habilita `mod_rewrite` y permite la configuración de `public/.htaccess`. Este fragmento se integra dentro del VirtualHost HTTPS ya configurado por el alojamiento:

```apache
DocumentRoot /srv/marketing-studies/current/public
<Directory /srv/marketing-studies/current/public>
    AllowOverride All
    Options -Indexes
    Require all granted
</Directory>
```

Para Nginx con PHP-FPM, envía las rutas públicas al despachador. Ajusta el socket de PHP a tu instalación y conserva la configuración TLS del alojamiento:

```nginx
root /srv/marketing-studies/current/public;
client_max_body_size 20m;

location ~ /\. {
    return 404;
}

location / {
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME /srv/marketing-studies/current/public/index.php;
    fastcgi_param SCRIPT_NAME /index.php;
    fastcgi_pass unix:/run/php/php8.3-fpm.sock;
}
```

No añadas una regla genérica que ejecute archivos PHP dentro de los directorios de subidas. Para admitir archivos del Lab de hasta 15 MB, configura PHP con `upload_max_filesize = 15M` y `post_max_size = 20M`; las imágenes mantienen su validación y límite propio en la aplicación. El servidor WASM local usa los mismos límites de 15 MB por archivo y 20 MB por solicitud. Verifica también los límites del alojamiento con PHP nativo en staging.

## Procedimiento de actualización

1. Obtén el commit revisado y ejecuta `npm ci`, `npm run check` y `npm test` en una copia de trabajo o CI.
2. Respalda base y subidas. Comprueba que el respaldo se puede abrir y que contiene los artículos y archivos esperados.
3. Prepara la nueva carpeta de versión, instala la configuración privada fuera de Git y conecta las rutas persistentes. No publiques `.preview/`, `.runtime/` ni `node_modules/`.
4. Con una copia aislada de datos, comprueba portada, búsquedas, un artículo ES/EN, acceso al CMS, edición de borrador, contacto, Lab, portafolio y CV.
5. Apunta el alojamiento al nuevo `public/`. Revisa errores PHP y verifica las mismas rutas públicas. No uses un envío de newsletter como prueba de humo.
6. Conserva la versión y el respaldo anteriores hasta validar el funcionamiento. Si debes volver atrás, restaura el par compatible de código y datos, evitando perder cambios posteriores al respaldo.

El esquema MySQL es aditivo. No ejecutes los antiguos dumps privados sobre la base activa: pueden sustituir tablas o datos ajenos al despliegue. La prueba automatizada incluida usa SQLite; prueba MySQL en el alojamiento o staging que lo utilice antes de actualizarlo.

## Copias de seguridad

Guarda respaldos fuera del directorio público, con acceso limitado y una copia externa cifrada. Incluye base, archivos subidos, configuración privada y el identificador del commit. El repositorio conserva código y recursos públicos; no sustituye un respaldo de datos.

### SQLite

Para obtener una instantánea consistente de una base en uso, utiliza la API de respaldo de SQLite o su comando `.backup`; con la utilidad `sqlite3` instalada:

```bash
sqlite3 /ruta/privada/blog.sqlite ".backup '/ruta/privada/respaldos/blog-AAAA-MM-DD.sqlite'"
sqlite3 /ruta/privada/respaldos/blog-AAAA-MM-DD.sqlite "PRAGMA integrity_check;"
```

También puede utilizarse `VACUUM INTO` hacia un archivo nuevo desde una conexión autorizada. No copies sólo `blog.sqlite` mientras haya escrituras activas: el modo WAL puede contener datos recientes en archivos auxiliares. Si haces una copia de archivos, detén las escrituras y cierra las conexiones antes de copiar la base y sus archivos asociados.

Respalda `blog/public/uploads/` durante la misma ventana de mantenimiento para mantener referencias coherentes. Comprueba una restauración en una ruta de prueba; no importes una instantánea de prueba sobre producción.

### MySQL

Usa el mecanismo de respaldo del proveedor o `mysqldump` con una cuenta autorizada. Un ejemplo para tablas InnoDB es:

```bash
mysqldump --defaults-extra-file=/ruta/privada/mysql-backup.cnf --single-transaction --routines --triggers --databases marketing_studies > /ruta/privada/respaldos/marketing-studies-AAAA-MM-DD.sql
```

El archivo privado contiene los datos de conexión; no pongas la contraseña en la línea de comandos. Ajusta el nombre de base y permisos a la instalación. Si utilizas tablas que no sean transaccionales, coordina una ventana sin escrituras o usa el respaldo administrado del alojamiento.

## Primer acceso y recuperación pública

Para una instalación sin acceso previo, ejecuta `php blog/tools/create_admin.php` con la misma configuración privada que usa el servidor. La herramienta no reinicia contraseñas existentes. El modo `--stdin` acepta JSON privado con `username` y `password`, sin valores en argumentos o logs.

El importador `blog/tools/import-public-content.php` es opcional y sólo sirve para una base sin artículos. `--dry-run` valida archivos y contenido sin abrir la base. Si encuentra cualquier artículo, incluso un borrador, termina sin modificar esquema ni registros. No importa cuentas, sesiones, contactos o suscriptores; tampoco envía correos. Para una recuperación completa utiliza el respaldo privado existente.

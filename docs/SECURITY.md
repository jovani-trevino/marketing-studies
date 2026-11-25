# Seguridad y datos privados

## Exposición encontrada en el repositorio recuperado

La versión recuperada contenía configuración local, una base SQLite, archivos de sesión PHP, un dump SQL y páginas temporales exportadas. Esos archivos se retiraron del árbol actual. `.gitignore` evita que los mismos tipos de datos se incorporen por accidente en las siguientes versiones.

**Eliminar un archivo en un commit no lo elimina de commits anteriores.** Los datos que estuvieron en el repositorio deben tratarse como expuestos aunque ya no aparezcan en `main`. Esta actualización no reescribe el historial, no revoca accesos en el alojamiento y no cambia contraseñas de servicios externos.

El responsable del alojamiento debe revisar los valores que estaban en esos archivos, rotar las credenciales afectadas —CMS, base de datos, SMTP, claves de servicios o secretos OAuth según corresponda— y revocar las sesiones existentes. Actualiza después la configuración privada del servidor. Si se decide retirar también los blobs del historial, planifica una limpieza del repositorio y de sus referencias con las personas que lo utilicen; requiere coordinar clones, ramas y copias existentes.

No se reproducen aquí valores, direcciones de suscriptores, mensajes de contacto ni identificadores de sesión. Los registros de la comunidad pertenecen a la base privada, no al paquete público de artículos.

## Controles de esta versión

- No hay usuario ni contraseña predeterminados. El alta inicial se hace por CLI y guarda un hash; se rechaza si ya existe acceso configurado.
- El acceso al CMS regenera la sesión, limita intentos y aplica vencimiento. Las operaciones de edición y cierre de sesión validan CSRF.
- La configuración acepta rutas privadas externas al checkout. El despachador público no sirve código de aplicación, SQL ni bases de datos.
- Las subidas validan extensión, contenido MIME y, para imágenes, dimensiones. Los nombres de archivo se generan en el servidor.
- El contenido del editor se sanea al guardar y al mostrar. Los vídeos embebidos tienen proveedores permitidos y restricciones de iframe.
- Los comentarios requieren moderación según la configuración editorial. Contactos y suscripciones permanecen en el CMS.
- Las pruebas usan datos temporales y no cargan credenciales de producción ni envían newsletters.

Estos controles necesitan una configuración correcta de HTTPS, permisos, respaldos y actualizaciones del servidor. No constituyen una certificación de seguridad ni sustituyen la revisión del alojamiento.

## Manejo diario

Mantén configuración, bases, sesiones, dumps y archivos con credenciales fuera de Git. Usa cuentas con permisos limitados, restringe el acceso a los respaldos y prueba periódicamente una restauración. Antes de compartir capturas o exports del CMS, revisa que no incluyan correos, mensajes, tokens o configuración privada.

La página pública de privacidad describe las funciones del sitio. Debe ajustarse a los servicios que realmente se activen, los datos que se recaben y las prácticas del responsable. Los anuncios, protección antiabuso, enlaces externos y servicios de correo requieren su propia configuración; no actives integraciones reales para probar el diseño.

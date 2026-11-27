# Revisión del CRM — 27 de noviembre de 2026

Entorno: `http://mkt.local/blog/admin/posts`, checkout existente y rama `codex/perfil-marketing-intelligence`. Se conservaron los cambios previos. Esta revisión queda local; no se hizo push ni despliegue de producción.

## Recorrido por módulos

| Módulo | Resultado de esta revisión |
| --- | --- |
| Resumen | Acceso directo a mensajes nuevos con su contador real. |
| Mi perfil y agenda | Búsqueda de casos por texto, sector y periodo; grupos vacíos ocultos; guardar avance regresa a la fila editada con confirmación. |
| Artículos | Opciones de categoría con identificadores canónicos, protección de cambios sin guardar y contexto de retorno al eliminar. |
| Casos | Categoría preseleccionada correctamente aunque su etiqueta esté en inglés; encabezado, sección activa, botón, estado vacío y retorno de guardar/eliminar corresponden a Casos. |
| Lab | Búsqueda, estados y paginación; conserva los campos tras errores de validación o subida; URL automática de solo lectura; validación de existencia y CSRF al eliminar. |
| Categorías | Búsqueda y paginación, estado vacío de resultados y etiquetas asociadas a controles de edición. |
| Autores | Búsqueda, filtro de estado, paginación y estados en español; no ofrece un perfil público para autores inactivos. |
| Comentarios | Búsqueda y paginación; estados validados; errores 403/404/422 explícitos; conserva filtros al moderar; comentarios de borradores enlazan al editor. |
| Mensajes | Conserva búsqueda, estado y página al actualizar; conexión desde el resumen. |
| Newsletter | Búsqueda y paginación de suscriptores, incluidos registros posteriores al antiguo límite de 300; estados inválidos no reactivan suscripciones; conserva filtros. |
| SEO y visibilidad | Búsqueda y filtro por estado dentro de la revisión editorial paginada. |
| Ajustes | Etiquetas de campos accesibles y aviso de cambios sin guardar, sin almacenar contraseñas ni ajustes en el navegador. |

Los formularios de artículos, autores, categorías, Lab y ajustes avisan antes de abandonar cambios pendientes. Una subida que bloquea el envío no desactiva ese aviso. Se añadió foco visible para teclado, ajuste de filtros a pantallas estrechas y tratamiento de textos largos.

## Verificación

- `npm test`: finalizó correctamente. Incluye 38 comprobaciones autenticadas del CMS, 53 comprobaciones de rutas y montajes, 13 pruebas JavaScript, 16 comprobaciones SEO y las suites existentes de datos, perfiles, catálogo e importación.
- Las pruebas nuevas usan una base temporal con 65 comentarios, 65 recursos y 305 suscriptores; comprueban búsquedas, límites de página, parámetros mal formados, CSRF, estados inválidos, registros inexistentes, retorno seguro y carga rechazada sin pérdida de texto.
- `npm run check`: 102 archivos PHP y 24 JavaScript. El verificador ahora revisa cada PHP individualmente, también cuando el CLI anterior a 8.3 solo acepta un archivo por `-l`.
- `git diff --check`: sin errores.
- Navegador autenticado: se visitaron las 12 secciones en móvil a 390 × 844; ninguna desbordó el ancho de página. Se revisaron visualmente ajustes en móvil, editor de casos en oscuro y newsletter en escritorio, además del listado de artículos.
- La búsqueda de la biblioteca profesional devuelve cero resultados correctamente y restablece los casos al limpiar. No se registraron errores de consola en la comprobación final.

## Límites de la revisión

- No se enviaron correos, conectó ORCID ni modificaron credenciales reales. La entrega SMTP, ORCID y los servicios externos necesitan verificación de integración específica.
- Los cambios de datos de prueba, publicación y eliminación ocurrieron en la base aislada. La base utilizada por `mkt.local` se inspeccionó mediante sus pantallas sin crear contenido de prueba.
- La suite de rutas pública conserva mensajes previos `lockWholeFile: unlock failed` de su runtime en Windows, aunque todas sus comprobaciones terminaron correctamente.
- El historial de campañas y el selector de artículos para envío manual conservan sus límites existentes de 60 campañas y 12 publicaciones recientes; la paginación añadida en Newsletter corresponde a suscriptores.
- La comprobación visual móvil usa emulación de viewport, no un teléfono físico. No se afirma una auditoría exhaustiva de accesibilidad ni cobertura de toda combinación de datos.

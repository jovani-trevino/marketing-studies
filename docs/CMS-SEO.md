# CMS editorial y SEO automático

Actualización local del 27 de noviembre de 2026. El código no se ha desplegado al alojamiento público.

## Uso

- El CMS conserva las rutas existentes. En el montaje local de AMPPS se abre en `http://mkt.local/blog/admin`; en la raíz pública unificada, en `/admin`.
- La navegación lateral reúne contenido, comunidad, perfil, ajustes y la nueva sección **SEO y visibilidad**. En móvil se despliega con **Secciones**.
- El editor muestra una vista orientativa de búsqueda mientras se escribe. El extracto es opcional: cuando está vacío se genera un resumen extractivo desde el contenido, sin llamadas a servicios externos.
- Título y descripción SEO se generan al guardar. En **Personalizar título y descripción** se pueden escribir valores propios; al vaciar los campos se vuelve a la generación automática. La descripción queda limitada a 160 caracteres, respetando palabras y Unicode.
- La revisión editorial señala portadas, categorías, resúmenes, subtítulos e imágenes sin descripción. No representa una puntuación de Google ni una garantía de posicionamiento.
- Se conserva la protección existente ante cambios sin guardar y la copia local del cuerpo del editor. `Ctrl+S` / `Cmd+S` envía el formulario, con sus validaciones; la copia local no equivale a guardar en el servidor.

## Comportamiento público

- Los artículos publicados emiten `BlogPosting` con título, autor, URL, idioma, imagen disponible y fechas reales. La fecha de modificación tiene en cuenta la traducción correspondiente. El JSON se escapa contra cierres de etiqueta de script.
- El sitemap se calcula desde la base actual: publicar añade una URL y pasar a borrador la retira. Superados 1,000 artículos utiliza un índice y archivos paginados, con ambas versiones de idioma. Se eliminó el límite anterior de 500 artículos.
- Las URLs de categoría del sitemap utilizan sus slugs localizados. Las páginas de blog conservan página y categoría en la URL canónica y en sus alternativas de idioma; descartan parámetros de seguimiento.
- El CMS, las búsquedas internas y las páginas 404 declaran `noindex`. Esto complementa la autenticación y los estados HTTP; no los sustituye.
- Se normalizan de forma estable los acentos españoles al crear slugs en Windows y Linux. No hay migración masiva de URLs antiguas; editar conserva el slug guardado.
- Los artículos sin categoría ya no redirigen innecesariamente cuando se consultan con parámetros de seguimiento.

## Verificación

`npm run check` y `npm test` completados. La suite incorpora:

- `tests/seo_test.php`: 16 comprobaciones de metadatos, Unicode, traducciones, canonical, JSON, sitemap con 1,001 artículos publicados, paginación y exclusión de borradores.
- `tests/cms_routes_test.mjs`: 21 comprobaciones HTTP autenticadas, incluyendo todas las secciones principales, creación con extracto vacío, publicación, conservación de URL, SEO personalizado y retirada de publicación.

Las pruebas usan bases temporales con newsletter desactivada. La revisión visual se realizó en Edge en escritorio y a 390 px: temas claro y oscuro, editor Quill, creación de borrador, menú móvil y lista sin desbordamiento horizontal de página. Las rutas heredadas se prueban con PHP WASM 8.3; las pruebas nativas de esta máquina usan PHP 8.2.31. Producción mantiene el requisito documentado de PHP 8.3+.

La instalación local habitual tiene configurada la cuenta inicial `admin`, creada mediante la herramienta oficial del CMS. Se verificó el acceso por HTTP y en Edge. La contraseña se entregó por separado y no se guarda en el código. No se cambiaron credenciales existentes ni se enviaron newsletters reales. MySQL y el alojamiento de producción no se verificaron en esta actualización.

## Identidad visual compartida

`cv/brand.css` centraliza azul marino, dorado, superficies, tipografías y controles para blog, perfil, portafolio, casos, currículum y CMS. Se carga después de los estilos de cada sección para conservar sus estructuras con una identidad común. La ubicación `/cv/` es accesible tanto desde la raíz pública unificada como desde los montajes heredados.

`cv/brand-theme.js` conserva la preferencia claro/oscuro con la clave existente `gb-theme`, sincroniza pestañas y respeta el sistema cuando no hay una selección explícita. El currículum mantiene una versión clara al imprimir. Las pruebas `tests/brand_theme_test.mjs` cubren preferencias, sincronización, etiquetas y almacenamiento bloqueado; las pruebas de rutas cubren los recursos compartidos y montajes en subcarpetas.

## Referencias

- [Sitemaps: construcción, partición y lastmod](https://developers.google.com/search/docs/crawling-indexing/sitemaps/build-sitemap).
- [Datos estructurados de artículos](https://developers.google.com/search/docs/appearance/structured-data/article).
- [Descripciones y fragmentos de búsqueda](https://developers.google.com/search/docs/appearance/snippet).

Para publicar se aplica `docs/DEPLOYMENT.md`, preservando configuración privada, base y archivos subidos. Estos cambios no requieren un servicio programado: las salidas públicas se calculan con el contenido guardado.

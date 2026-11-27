# Piezas originales en los casos de estudio

Se incorporaron 37 piezas a 16 casos (4 videos y 33 imágenes). La fuente fue el archivo profesional de OneDrive: videos del sitio histórico, mockups existentes y láminas del portafolio PDF. No se generaron supuestos originales con IA. Las imágenes de vidrio se extrajeron de la región correspondiente de la página 5 del PDF.

`professional-deliverables.json` selecciona las piezas verificadas. Las portadas de esos casos muestran el trabajo original, seguido de una galería con controles de video y enlaces para ampliar. Las galerías conceptuales complementarias se mantienen por separado de las piezas documentadas. Los títulos, fuentes y límites de los casos se conservan sin inventar métricas comerciales.

Las 16 ediciones descargables `*-visual.pdf` presentan la narrativa y las piezas. Se usan nombres propios para conservar las ediciones previas y evitar colisiones con otras tareas que regeneran los PDF resumidos. Los enlaces de reproducción de esta edición visual apuntan al sitio local solicitado.

## Validación

- `npm run check`: 103 PHP y 24 JavaScript válidos en la ejecución inicial.
- `npm test`: suites completas aprobadas (catálogo, backend, perfil, SEO, CMS, rutas, JavaScript e importación).
- `node tests/routes_test.mjs` después de añadir streaming: 61 comprobaciones aprobadas, incluidos rangos, rangos inválidos y HEAD de video en el montaje unificado.
- Auditoría HTTP local: 73 respuestas correctas para páginas, archivos y PDF; sin enlaces rotos de la selección.
- Las 16 ediciones visuales se abren correctamente y contienen sus páginas y piezas previstas.
- Navegador: video 3D de 8.133 s reproducido con `readyState=4`, sin error; imágenes cargadas; móvil de 390 px sin desbordamiento horizontal; contraste revisado en tema oscuro; mockups del brochure revisados.

La verificación corresponde a `mkt.local`. No se realizó despliegue desde esta tarea. Hubo cambios y commits simultáneos de otra tarea en el checkout; se preservaron y las galerías originales tienen prioridad sobre las conceptuales.

## Ampliación solicitada desde OneDrive

Se añadieron 30 imágenes originales, para un total de 67 piezas (63 imágenes y 4 videos) en 20 casos. La ampliación incorpora seis páginas de catálogos de productos y materiales, ocho láminas de dos investigaciones académicas, cinco páginas de producto internacional y organización de ventas, nueve detalles de carteles, interfaces y aplicaciones de temporada, y dos registros históricos de campañas en Facebook y YouTube.

Las páginas se renderizaron desde los PDF originales; los detalles se extrajeron de las regiones correspondientes del portafolio. No se recrearon piezas históricas con IA. Las inconsistencias estadísticas del documento de redes sociales siguen señaladas en el caso y en cada pie de imagen; las cifras publicitarias permanecen como reportes históricos sin revalidación. Las 20 ediciones visuales incluyen las nuevas piezas. Los archivos fuente de OneDrive no se modificaron.

Validación de la ampliación: `npm run check` aprobó 104 PHP y 24 JavaScript; `npm test` aprobó todas las suites, incluidas 450 comprobaciones de catálogo y 61 de rutas. La auditoría comprobó 111 respuestas HTTP y la integridad, dimensiones y páginas de las 67 piezas y 20 PDF. Se revisaron visualmente las páginas del catálogo en escritorio y las láminas académicas a 390 px, sin desbordamiento horizontal del documento. La carga diferida mostró las imágenes al recorrer la galería. La comprobación del enlace a la galería conceptual ahora exige que exista su destino, compatible con la galería complementaria incorporada en paralelo.


## Segunda ampliación de OneDrive

Se añadieron 59 piezas (57 imágenes y 2 videos), para un total de 126 piezas en 35 casos. El catálogo de casos ahora contiene 39 entradas, incluida la portada audiovisual del sencillo de marzo de 2018. Los 35 PDF visuales incluyen las piezas correspondientes. Se completaron las 11 páginas de los catálogos de productos y materiales, junto con sus mockups previos.

La selección incorpora anuncios de Display en sus formatos originales, anuncios de Búsqueda, mockups de YouTube, propuestas de capacitación y email del CRM, reportes históricos de campañas, investigación de expansión, una ficha industrial y más aplicaciones de temporada. Los campos de fecha de PowerPoint se fijaron al texto original en copias temporales antes de renderizar. Se descartó una diapositiva vacía y se conservaron los límites de interpretación de cifras y promesas comerciales. Los videos adicionales son la aplicación animada de identidad (15.015 s) y la portada musical (30.030 s).

Validación final: `npm run check` aprobó 66 PHP y 20 JavaScript en el checkout actual; `npm test` aprobó todas las suites, incluidas 461 comprobaciones de catálogo para 39 casos y 62 de rutas. La auditoría local comprobó 202 respuestas HTTP, dimensiones y archivos de las 126 piezas, y las páginas previstas de los 35 PDF. Ambos videos adicionales se decodificaron completamente sin errores. Se revisaron las páginas PDF renderizadas y todas las nuevas imágenes. En el navegador, el video musical reprodujo con duración 30.03 s y `readyState=4`; a 390 px mantuvo su proporción y el documento no desbordó horizontalmente. El caso de Búsqueda y Display también se comprobó a 390 px.

Esta entrega está integrada y verificada en `mkt.local`; no se ejecutó despliegue ni push desde esta tarea. El checkout continúa compartido con otra tarea y se preservaron sus cambios.


## Tercera ampliación: tres proyectos distintos

- Identidad institucional: 15 láminas seleccionadas de un manual de 153 diapositivas. Incluye logotipo, retícula, reserva, color, tipografía, uniformes, tríptico, anuncios, presentaciones, CD y señalética. Se documenta como propuesta, sin afirmar ejecución.
- Locución y comunicación: cinco páginas de un documento académico de 24 páginas; objetivos, temario, agenda de una hora y formato visual. No acredita impartición.
- Canales de distribución: cinco páginas de un estudio académico de 49 páginas, centradas en estructura y diagramas. Se conserva su carácter histórico; no se presenta como información vigente ni asesoría.

Total integrado: 42 casos, 38 galerías originales, 151 piezas (145 imágenes y seis videos) y 38 PDF visuales. Los nuevos PDF contienen 17, 7 y 7 páginas y se revisaron renderizados. Las imágenes son conversiones de páginas o diapositivas originales, sin generar supuesta evidencia histórica.

Validación: sintaxis de 69 PHP y 20 JavaScript; suite completa aprobada, incluidas 496 comprobaciones del catálogo y las tres rutas nuevas. Se ejecutaron `node scripts/check.mjs` y `node scripts/test.mjs`, equivalentes a los comandos npm del proyecto, después de que npm no pudiera escribir su registro por falta de espacio. La auditoría de galerías comprobó 233 respuestas HTTP y las dimensiones, archivos y páginas de los 38 PDF. Revisión en Edge: los tres casos a 390 px sin desbordamiento horizontal, manual también en escritorio; viewport restablecido al terminar.

Se eliminaron solamente renders y copias temporales creados por esta tarea para recuperar espacio. Los originales de OneDrive se conservaron. Integración local, sin push ni despliegue desde esta tarea; se preservaron los cambios concurrentes del checkout.

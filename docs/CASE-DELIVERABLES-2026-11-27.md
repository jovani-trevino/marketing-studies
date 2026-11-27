# Piezas originales en los casos de estudio

Se incorporaron 37 piezas a 16 casos (4 videos y 33 imágenes). La fuente fue el archivo profesional de OneDrive: videos del sitio histórico, mockups existentes y láminas del portafolio PDF. No se generaron supuestos originales con IA. Las imágenes de vidrio se extrajeron de la región correspondiente de la página 5 del PDF.

`professional-deliverables.json` selecciona las piezas verificadas. Las portadas de esos casos muestran el trabajo original, seguido de una galería con controles de video y enlaces para ampliar. La galería conceptual se reserva para los casos sin originales seleccionados. Los títulos, fuentes y límites de los casos se conservan sin inventar métricas comerciales.

Las 16 ediciones descargables `*-visual.pdf` presentan la narrativa y las piezas. Se usan nombres propios para conservar las ediciones previas y evitar colisiones con otras tareas que regeneran los PDF resumidos. Los enlaces de reproducción de esta edición visual apuntan al sitio local solicitado.

## Validación

- `npm run check`: 103 PHP y 24 JavaScript válidos en la ejecución inicial.
- `npm test`: suites completas aprobadas (catálogo, backend, perfil, SEO, CMS, rutas, JavaScript e importación).
- `node tests/routes_test.mjs` después de añadir streaming: 61 comprobaciones aprobadas, incluidos rangos, rangos inválidos y HEAD de video en el montaje unificado.
- Auditoría HTTP local: 73 respuestas correctas para páginas, archivos y PDF; sin enlaces rotos de la selección.
- Las 16 ediciones visuales se abren correctamente y contienen sus páginas y piezas previstas.
- Navegador: video 3D de 8.133 s reproducido con `readyState=4`, sin error; imágenes cargadas; móvil de 390 px sin desbordamiento horizontal; contraste revisado en tema oscuro; mockups del brochure revisados.

La verificación corresponde a `mkt.local`. No se realizó despliegue desde esta tarea. Hubo cambios y commits simultáneos de otra tarea en el checkout; se preservaron y las galerías originales tienen prioridad sobre las conceptuales.

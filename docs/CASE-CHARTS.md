# Gráficas de casos profesionales

Las 15 visualizaciones de los 11 casos cuantitativos se configuran en `blog/content/professional-charts.json`. La gráfica principal conserva los datos de `professional-cases.json`, salvo las filas explícitas de la distribución de clics en la transición web. Las comparaciones adicionales proceden de las métricas y notas del mismo caso.

- Donas: reparto de clics entre Búsqueda y Display, con total común y porcentajes calculados. No se suman personas ni conversiones de plataformas diferentes.
- Barras: costos por categoría, clics por campaña, conversiones por plataforma e índices con base 100. Los CTR comparativos conservan denominadores separados.
- Tasas: una escala completa de 0 a 100, con el porcentaje reportado y su definición contextual.
- Volúmenes: vistas y clics independientes, sin inventar una tasa ni una serie temporal.

El componente PHP genera SVG y HTML con valores y referencias visibles, tabla desplegable, animación breve y soporte de movimiento reducido. No requiere librerías del navegador ni servicios externos. El diseño apila dona y etiquetas en móvil y conserva las cifras sin JavaScript.

`python scripts/export-case-charts.py` genera las hojas vectoriales con ReportLab y PyMuPDF y las integra en los PDF normales y visuales existentes. Sustituye únicamente sus propias hojas, identificadas como «Lectura visual de resultados», y conserva las páginas del proyecto. Dependencias: `reportlab`, `pymupdf` y Arial de Windows. Ejecutar después de regenerar un PDF base.

Validación: 11 páginas locales y sus descargas; 15 visualizaciones; sumas de composiciones y escalas comprobadas en `tests/professional_cases_test.php`; revisión visual de dona en escritorio y 390 px, tabla desplegable y muestras PDF de dona, porcentaje y barras. `npm run check` y `npm test` aprobados.

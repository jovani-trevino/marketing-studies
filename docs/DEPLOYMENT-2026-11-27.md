# Publicación de Marketing Studies — 2026-11-27

- Dominio activo: https://marketing-studies.com
- Versión activa: `20261127-latest`, directorio privado `/home/market32/ms-release-20261127-latest`.
- Publicados 546 archivos de aplicación y recursos; 42 casos y 151 piezas originales en 38 galerías.
- 38 PDF visuales tienen enlaces al dominio público; las páginas afectadas conservan exactamente el mismo render.
- Preservados archivos subidos externos, configuración privada y acceso exclusivo al CV privado. Recuperadas del servidor las rutas del código de ética y casos anónimos.
- La carpeta histórica `/public_html/jovani` se conservó en el respaldo privado para eliminar un conflicto de Apache con la ruta nueva `/jovani`.
- El despachador público utiliza `[END,QSA]` para terminar la reescritura.

## Validación

- `npm run check`: sintaxis válida (69 PHP y 20 JavaScript).
- `npm test`: suites completas aprobadas, 496 comprobaciones del catálogo y 74 de rutas.
- Preparación con copia aislada de la base: 65 rutas aprobadas.
- Producción: 66 rutas y 448 recursos aprobados; PDF seleccionados idénticos por SHA-256 y video con rangos HTTP 206 correctos.
- Base antes/después: 5 artículos, 1 autor, 7 categorías, 4 comentarios y 4 suscriptores. `PRAGMA integrity_check`: `ok`.
- 15 archivos subidos respaldados y conservados. Sin importación ni sustitución de la base activa, sin cambios de cuentas ni envíos de correo.

## Respaldo y recuperación

Respaldo privado: `/home/market32/ms-backup-20261127-latest`. Contiene instantánea consistente SQLite mediante `VACUUM INTO`, subidas verificadas, configuración y despachador anteriores. La versión anterior permanece en `/home/market32/ms-release-a34b61c-v3/produccion-candidato`.

Para volver al código anterior, restaurar el despachador respaldado de forma atómica. No sustituir la base actual por el respaldo sin revisar cambios posteriores. El asistente temporal de despliegue se retiró al terminar.

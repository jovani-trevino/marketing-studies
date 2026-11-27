# Perfil de Marketing Intelligence Digital

El perfil contiene 38 casos en siete áreas. Son intervenciones y entregables, no 38 clientes distintos. Las cifras se conservan con periodo, definición y límites de interpretación. Los casos analíticos mantienen sus identidades reservadas. Por solicitud del usuario del 27/11/2026, los 16 casos incluidos en `professional-deliverables.json` muestran las piezas y mockups del archivo profesional, incluidos sus elementos de marca. Esta selección no autoriza publicar documentos administrativos, credenciales ni archivos de trabajo completos.

## Entregables visuales del archivo

- `blog/content/professional-deliverables.json` relaciona cada caso con sus piezas, portada, dimensiones y textos. Contiene rutas relativas, sin rutas privadas de origen.
- `Jovani/assets/case-work/` contiene copias web optimizadas: 4 videos y 33 imágenes en 16 casos. Los archivos de OneDrive permanecen como fuentes; no se modificaron sus contenidos.
- `Jovani/case-deliverables.php` presenta videos con controles nativos, sin reproducción automática, e imágenes ampliables. `case-work.css` conserva las proporciones de los originales y admite móvil y tema oscuro.
- Los casos con originales priorizan esa galería y ya no muestran la reconstrucción conceptual genérica. Los demás conservan sus gráficas y recursos anteriores.
- Los 16 PDF con sufijo `-visual.pdf` incluyen sus piezas y el contexto del caso, y son los enlazados desde las galerías. Las ediciones previas se conservan. El resumen general del portafolio sigue siendo una edición resumida.
- La procedencia detallada, hashes y recortes de páginas se conservan fuera del sitio. Las piezas se recuperaron del archivo profesional de OneDrive y de su portafolio PDF; no se generaron supuestos entregables históricos con IA.

## Edición

- `blog/content/professional-cases.json`: fuente de los casos y gráficas; `Jovani/cases.php` presenta barras o diagramas cualitativos. Las barras parten de cero; el caso de comparación usa índice anterior = 100.
- `blog/content/professional-areas.json`: nombres de las siete áreas. Cada caso conserva `area` y `evidence_kind`; los proyectos académicos, propuestas e implementaciones locales deben seguir identificados como tales.
- El buscador combina área y texto, ignora tildes, anuncia el número de resultados y muestra un estado vacío. Sin JavaScript los 35 casos siguen accesibles.
- `Jovani/index.php`: perfil; `Jovani/case-detail.php`: página editorial propia de cada caso. Las rutas canónicas son `/Jovani/casos/<id>` en Apache histórico y `/portfolio/casos/<id>` en el montaje unificado. Los enlaces anteriores `?caso=<id>` siguen funcionando, con canonical a la ruta nueva. Identificadores desconocidos y acceso directo al parcial devuelven 404.
- Cada caso incluye `story.context`, `story.objective`, `story.approach` y `story.takeaway`: contexto, objetivo, criterio de presentación de evidencia y lectura profesional. La plantilla del CMS conserva estos textos. No convertir interpretaciones en resultados históricos comprobados.
- `Jovani/assets/case-editorial.css`: portada, ilustraciones conceptuales por área, ficha de participación, índice de secciones, proceso, evidencia, aprendizaje y enlaces relacionados. Las preferencias compartidas de `cv/brand.css` mantienen coherencia visual y modo oscuro.
- `Jovani/assets/intelligence.css` y `.js`: filtros, animación y preferencias de movimiento reducido. El contenido sigue visible sin JavaScript.
- `Jovani/assets/mockups/`: conceptos visuales anónimos, identificados como reconstrucciones y no como capturas originales.
- `cv/cv.html`: CV imprimible. Los PDF en `Jovani/assets/downloads/` son exportaciones estáticas; actualizar ambos formatos cuando cambie la experiencia.
- `blog/content/editorial-topics.json`: 30 temas propuestos. `article-01.md` contiene el primer artículo completo; los demás se inician como esquema.

## CMS

Abrir `/admin/professional` en el montaje unificado o `/blog/admin/professional` en el montaje histórico. Requiere la autenticación existente del CMS. Guarda únicamente estados de tareas permitidos, con CSRF. Abrir una plantilla no inserta un post ni cambia uno existente. El guardado usa el flujo normal del editor y empieza como borrador.

Para crear el primer artículo mediante CLI:

```sh
php blog/tools/create-professional-draft.php article-01
php blog/tools/create-professional-draft.php article-01 --apply
```

El primer comando muestra una vista previa. El segundo inserta un borrador con slug estable y conserva cualquier versión existente. No publica ni envía correo. La automatización diaria está configurada en la aplicación Codex, fuera del repositorio.

## Evidencia y pendientes

El inventario de archivos, hashes y correspondencias entre clientes y casos se conserva como entrega privada fuera del repositorio. Las certificaciones y membresías no verificadas se omiten de la presentación pública. La renovación de identidad aún no identificada y una sesión fotográfica nueva quedan en la agenda interna.

## Comprobación

```sh
npm run check
npm test
```

Las pruebas usan bases aisladas. Cubren rutas, anonimización, borradores, CSRF y estados admitidos. Los montajes WASM explícitos evitan usar rutas de unidad de Windows dentro del sistema POSIX de pruebas.

`tests/professional_cases_test.php` comprueba independientemente la cobertura por áreas, identificadores, anonimización, límites de las gráficas y conservación del tipo de evidencia y narrativa en las 35 plantillas del CMS. `tests/routes_test.mjs` comprueba las nuevas rutas, canonical, secciones y compatibilidad del montaje unificado. El PDF contiene 37 páginas, índice enlazado y marcadores: es la edición resumida de los 35 casos, mientras que las páginas web contienen la explicación ampliada.

En Apache con raíz histórica, `Jovani/bootstrap.php` detecta `/Jovani` y enlaza a `/blog`. `MS_BLOG_BASE_PATH` permite un prefijo local explícito; un valor vacío conserva el montaje unificado.

Estos cambios son locales. No implican despliegue, publicación de artículos ni validación de ventas atribuidas.

## Galerías visuales

Los 38 casos incluyen una galería de dos imágenes ampliables: composición conceptual y lámina específica. `professional-media.json` asigna la composición a cada caso; algunas se comparten entre trabajos afines y se indica en el pie. `case-gallery.php` no sustituye los entregables originales existentes. Los PNG están en `assets/case-premium/` y las láminas SVG en `assets/case-illustrations/`. Son recursos de presentación, no evidencia de ejecución o rendimiento. Los PDF individuales incluyen el muestrario.

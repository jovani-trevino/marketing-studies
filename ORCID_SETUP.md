# ORCID Developer Tools

El CMS puede conectar un autor con un ORCID iD autenticado desde ORCID OAuth.

1. En pruebas entra a `https://sandbox.orcid.org/developer-tools`; en produccion usa `https://orcid.org/developer-tools`.
2. Registra una aplicacion Public API y copia el `Client ID` y `Client Secret`.
3. Agrega como Redirect URI exacta:
   `https://marketing-studies.com/blog/admin/orcid/callback`
4. Copia `blog/app/config.local.example.php` a `blog/app/config.local.php` y llena `orcid.client_id`, `orcid.client_secret`, `orcid.environment` y, si tu ruta cambia, `orcid.redirect_uri`.
5. En el CMS ve a `Autores`, edita un autor y usa `Conectar con ORCID`.

Tambien puedes usar variables de entorno:

```text
MS_ORCID_ENV=sandbox
MS_ORCID_CLIENT_ID=APP-XXXXXXXXXXXXXXXX
MS_ORCID_CLIENT_SECRET=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
MS_ORCID_REDIRECT_URI=https://marketing-studies.com/blog/admin/orcid/callback
MS_ORCID_SCOPE=/authenticate
```

## Uso editorial y codigo de etica

- No publicar un ORCID iD manualmente si no fue verificado por OAuth o por una fuente controlada por el titular.
- Mostrar el ORCID iD como enlace completo a `https://orcid.org/{orcid_id}` cuando exista una verificacion valida.
- Separar certificaciones, membresias, publicaciones, colaboraciones y resultados comprobados para evitar atribuciones ambiguas.
- Usar datos de audiencias, CRM y analitica solo con permisos claros, finalidad proporcional y cuidado de privacidad.
- Documentar casos de portafolio con contexto, rol, entregables e impacto, evitando prometer resultados que no esten respaldados.

Referencias oficiales:

- ORCID iD: https://orcid.org
- Codigo de conducta ORCID: https://info.orcid.org/work-with-us/code-of-conduct/
- Guias de visualizacion del ORCID iD: https://info.orcid.org/documentation/integration-guide/orcid-id-display-guidelines/

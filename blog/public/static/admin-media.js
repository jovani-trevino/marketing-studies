(() => {
    if (!document.body.classList.contains("admin-body")) return;

    // Existing PHP forms keep their names, action URLs and CSRF fields.
    document.querySelectorAll("label:not([for])").forEach((label, index) => {
        if (label.querySelector("input,select,textarea")) return;
        const candidates = [label.nextElementSibling, label.previousElementSibling];
        const control = candidates.find(node => node?.matches("input,select,textarea"));
        if (!control) return;
        if (!control.id) control.id = `cms-field-${index}`;
        label.htmlFor = control.id;
    });
    document.querySelectorAll("button[title]").forEach(button => {
        if (!button.textContent.trim() && !button.hasAttribute("aria-label")) button.setAttribute("aria-label", button.title);
    });

    const menu = document.querySelector("[data-admin-menu]");
    const menuButton = menu?.querySelector("[data-admin-nav-toggle]");
    const navigation = menu?.querySelector("nav");
    const mobile = window.matchMedia("(max-width: 767.98px)");
    if (menu && menuButton && navigation) {
        menu.classList.add("is-enhanced");
        const setMenu = open => {
            navigation.hidden = mobile.matches && !open;
            menuButton.setAttribute("aria-expanded", String(!navigation.hidden));
        };
        setMenu(false);
        menuButton.addEventListener("click", () => setMenu(navigation.hidden));
        mobile.addEventListener("change", () => setMenu(false));
        menu.addEventListener("keydown", event => {
            if (event.key === "Escape" && mobile.matches && !navigation.hidden) {
                setMenu(false);
                menuButton.focus();
            }
        });
    }

    const dirtyForms = new Set();
    const markDirty = form => {
        if (!form || (form.method || "get").toLowerCase() !== "post" || form.closest(".admin-login-form")) return;
        dirtyForms.add(form);
        const state = form.querySelector("[data-admin-save-state]");
        if (state) state.textContent = "Cambios sin guardar en el servidor.";
    };
    document.addEventListener("input", event => markDirty(event.target.closest("form")));
    document.addEventListener("change", event => markDirty(event.target.closest("form")));
    document.addEventListener("admin:content-change", event => markDirty(event.target.closest("form")));
    document.addEventListener("submit", event => {
        if (event.defaultPrevented) return;
        const form = event.target;
        if ((form.method || "get").toLowerCase() !== "post") return;
        dirtyForms.delete(form);
        form.setAttribute("aria-busy", "true");
        const button = event.submitter;
        if (button) button.classList.add("admin-submit-pending");
    });
    window.addEventListener("beforeunload", event => {
        if (!dirtyForms.size) return;
        event.preventDefault();
        event.returnValue = "";
    });
    window.addEventListener("pageshow", () => {
        document.querySelectorAll('form[aria-busy="true"]').forEach(form => form.removeAttribute("aria-busy"));
        document.querySelectorAll(".admin-submit-pending").forEach(button => button.classList.remove("admin-submit-pending"));
    });

    document.querySelectorAll("[data-admin-resource-form]").forEach(form => {
        const url = form.querySelector('[name="download_url"]');
        const file = form.querySelector('[name="download_file"]');
        if (!url || !file) return;
        const validateDownload = () => {
            const selected = file.files?.[0];
            url.setCustomValidity(url.value.trim() || selected ? "" : "Pega una URL de descarga o selecciona un archivo.");
            file.setCustomValidity(selected?.size > 15 * 1024 * 1024 ? "El archivo debe pesar como máximo 15 MB." : "");
        };
        url.addEventListener("input", validateDownload);
        file.addEventListener("change", validateDownload);
        form.addEventListener("submit", event => {
            validateDownload();
            if (!form.checkValidity()) { event.preventDefault(); form.reportValidity(); }
        });
    });

    const allowedTypes = new Set(["image/jpeg", "image/png", "image/webp", "image/gif"]);
    const maxImageBytes = 5 * 1024 * 1024;
    function resolvePreviewUrl(field, value) {
        const raw = String(value || "").trim();
        if (!raw) return "";
        if (/^https?:\/\//i.test(raw)) return raw;
        if (/^[a-z][a-z0-9+.-]*:/i.test(raw) || raw.startsWith("//") || raw.includes("\\")) return "";
        const base = (field.dataset.basePath || "").replace(/\/$/, "");
        if (base && (raw === base || raw.startsWith(`${base}/`))) return raw;
        return `${base}/${raw.replace(/^\/+/, "")}`;
    }

    document.querySelectorAll("[data-admin-media-field]").forEach(field => {
        const preview = field.querySelector("[data-admin-media-preview]");
        const empty = field.querySelector("[data-admin-media-empty]");
        const urlInput = field.querySelector("[data-admin-media-url]");
        const fileInput = field.querySelector("[data-admin-media-file]");
        const dropzone = field.querySelector("[data-admin-media-dropzone]");
        const status = field.querySelector("[data-admin-media-status]");
        const form = field.closest("form");
        let objectUrl = "";
        if (fileInput) fileInput.accept = [...allowedTypes].join(",");
        if (status) { status.setAttribute("role", "status"); status.setAttribute("aria-live", "polite"); }
        const releaseObjectUrl = () => { if (objectUrl) URL.revokeObjectURL(objectUrl); objectUrl = ""; };
        const setStatus = (message, error = false) => {
            if (status) { status.textContent = message; status.classList.toggle("is-error", error); }
        };
        const togglePreview = source => {
            if (preview) {
                if (source) preview.src = source;
                else preview.removeAttribute("src");
                preview.hidden = !source;
            }
            if (empty) empty.hidden = Boolean(source);
            dropzone?.classList.toggle("has-image", Boolean(source));
        };
        const validFile = file => {
            if (!file || !allowedTypes.has(file.type)) { setStatus("Elige una imagen JPG, PNG, WebP o GIF.", true); return false; }
            if (file.size > maxImageBytes) { setStatus("La imagen debe pesar como máximo 5 MB.", true); return false; }
            return true;
        };
        const syncFromUrl = () => {
            releaseObjectUrl();
            const raw = (urlInput?.value || "").trim();
            const resolved = resolvePreviewUrl(field, raw);
            togglePreview(resolved);
            setStatus(raw && !resolved ? "Usa una URL HTTP/HTTPS o una ruta del sitio." : resolved ? "Vista previa de la imagen. Guarda el formulario para aplicar cambios." : "Sin imagen seleccionada.", Boolean(raw && !resolved));
        };
        const useLocalFile = file => {
            if (!validFile(file)) { if (fileInput) fileInput.value = ""; return false; }
            if (urlInput) urlInput.value = "";
            releaseObjectUrl();
            objectUrl = URL.createObjectURL(file);
            togglePreview(objectUrl);
            setStatus(`Seleccionaste ${file.name}. Se subirá al guardar el formulario.`);
            markDirty(form);
            return true;
        };
        field.querySelector("[data-admin-media-pick]")?.addEventListener("click", () => fileInput?.click());
        dropzone?.addEventListener("click", () => fileInput?.click());
        dropzone?.addEventListener("keydown", event => {
            if (["Enter", " "].includes(event.key)) { event.preventDefault(); fileInput?.click(); }
        });
        fileInput?.addEventListener("change", () => { if (fileInput.files?.[0]) useLocalFile(fileInput.files[0]); });
        urlInput?.addEventListener("input", () => {
            if (fileInput && urlInput.value.trim()) fileInput.value = "";
            syncFromUrl();
        });
        field.querySelector("[data-admin-media-clear]")?.addEventListener("click", () => {
            releaseObjectUrl();
            if (urlInput) urlInput.value = "";
            if (fileInput) fileInput.value = "";
            togglePreview("");
            setStatus("Imagen retirada de este formulario. Guarda para aplicar el cambio.");
            markDirty(form);
        });
        preview?.addEventListener("error", () => {
            if (!preview.getAttribute("src")) return;
            preview.hidden = true;
            if (empty) empty.hidden = false;
            setStatus("No se pudo cargar la vista previa. Revisa la ruta o elige otra imagen.", true);
        });
        ["dragenter", "dragover"].forEach(type => dropzone?.addEventListener(type, event => {
            event.preventDefault(); dropzone.classList.add("is-dragging");
        }));
        ["dragleave", "dragend"].forEach(type => dropzone?.addEventListener(type, () => dropzone.classList.remove("is-dragging")));
        dropzone?.addEventListener("drop", event => {
            event.preventDefault(); dropzone.classList.remove("is-dragging");
            const files = [...(event.dataTransfer?.files || [])];
            if (files.length !== 1) { setStatus("Arrastra una sola imagen a este campo.", true); return; }
            const file = files[0];
            if (!validFile(file)) return;
            try {
                const transfer = new DataTransfer(); transfer.items.add(file); fileInput.files = transfer.files;
                if (!fileInput.files.length) throw new Error();
                useLocalFile(file);
            } catch { setStatus("Usa el botón para seleccionar el archivo en este navegador.", true); }
        });
        form?.addEventListener("reset", () => window.setTimeout(() => { if (fileInput) fileInput.value = ""; dirtyForms.delete(form); syncFromUrl(); }, 0));
        syncFromUrl();
    });
})();

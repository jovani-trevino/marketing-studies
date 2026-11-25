(() => {
    const mediaFields = document.querySelectorAll("[data-admin-media-field]");
    if (!mediaFields.length) {
        return;
    }

    function resolvePreviewUrl(field, value) {
        const rawValue = (value || "").trim();
        if (!rawValue) {
            return "";
        }

        if (/^(https?:)?\/\//i.test(rawValue) || rawValue.startsWith("data:") || rawValue.startsWith("blob:")) {
            return rawValue;
        }

        const basePath = (field.dataset.basePath || "").replace(/\/$/, "");
        if (rawValue.startsWith("/")) {
            return `${basePath}${rawValue}`;
        }

        return `${basePath}/${rawValue.replace(/^\//, "")}`;
    }

    mediaFields.forEach((field) => {
        const preview = field.querySelector("[data-admin-media-preview]");
        const emptyState = field.querySelector("[data-admin-media-empty]");
        const urlInput = field.querySelector("[data-admin-media-url]");
        const fileInput = field.querySelector("[data-admin-media-file]");
        const pickButton = field.querySelector("[data-admin-media-pick]");
        const clearButton = field.querySelector("[data-admin-media-clear]");
        const statusNode = field.querySelector("[data-admin-media-status]");
        const dropzone = field.querySelector("[data-admin-media-dropzone]");

        let localObjectUrl = "";

        function releaseObjectUrl() {
            if (localObjectUrl) {
                URL.revokeObjectURL(localObjectUrl);
                localObjectUrl = "";
            }
        }

        function setStatus(message) {
            if (statusNode) {
                statusNode.textContent = message;
            }
        }

        function togglePreview(source) {
            const hasSource = Boolean(source);
            if (preview) {
                preview.src = hasSource ? source : "";
                preview.hidden = !hasSource;
            }
            if (emptyState) {
                emptyState.hidden = hasSource;
            }
            dropzone?.classList.toggle("has-image", hasSource);
        }

        function syncFromUrl() {
            releaseObjectUrl();
            const previewUrl = resolvePreviewUrl(field, urlInput?.value || "");
            togglePreview(previewUrl);
            setStatus(previewUrl ? "Imagen lista para guardarse." : "Sin imagen todavia.");
        }

        function useLocalFile(file) {
            if (!file || !file.type.startsWith("image/")) {
                setStatus("Solo puedes usar archivos de imagen.");
                return;
            }

            if (urlInput) {
                urlInput.value = "";
            }

            releaseObjectUrl();
            localObjectUrl = URL.createObjectURL(file);
            togglePreview(localObjectUrl);
            setStatus(`Seleccionaste ${file.name}. Se guardara al enviar el formulario.`);
        }

        pickButton?.addEventListener("click", () => {
            fileInput?.click();
        });

        dropzone?.addEventListener("click", () => {
            fileInput?.click();
        });

        dropzone?.addEventListener("keydown", (event) => {
            if (event.key !== "Enter" && event.key !== " ") {
                return;
            }
            event.preventDefault();
            fileInput?.click();
        });

        fileInput?.addEventListener("change", () => {
            const file = fileInput.files?.[0];
            if (file) {
                useLocalFile(file);
            }
        });

        urlInput?.addEventListener("input", () => {
            if (fileInput && urlInput.value.trim() !== "") {
                fileInput.value = "";
            }
            syncFromUrl();
        });

        clearButton?.addEventListener("click", () => {
            releaseObjectUrl();
            if (urlInput) {
                urlInput.value = "";
            }
            if (fileInput) {
                fileInput.value = "";
            }
            togglePreview("");
            setStatus("Imagen limpiada. Ahora puedes subir otra.");
        });

        dropzone?.addEventListener("dragover", (event) => {
            event.preventDefault();
            dropzone.classList.add("is-dragging");
        });

        dropzone?.addEventListener("dragenter", (event) => {
            event.preventDefault();
            dropzone.classList.add("is-dragging");
        });

        dropzone?.addEventListener("dragleave", () => {
            dropzone.classList.remove("is-dragging");
        });

        dropzone?.addEventListener("dragend", () => {
            dropzone.classList.remove("is-dragging");
        });

        dropzone?.addEventListener("drop", (event) => {
            event.preventDefault();
            dropzone.classList.remove("is-dragging");

            const imageFile = [...(event.dataTransfer?.files || [])].find((file) => file.type.startsWith("image/"));
            if (!imageFile) {
                setStatus("Arrastra una imagen valida en JPG, PNG, WEBP o GIF.");
                return;
            }

            if (fileInput && typeof DataTransfer !== "undefined") {
                const transfer = new DataTransfer();
                transfer.items.add(imageFile);
                fileInput.files = transfer.files;
            }

            useLocalFile(imageFile);
        });

        const form = field.closest("form");
        form?.addEventListener("reset", () => {
            window.setTimeout(() => {
                releaseObjectUrl();
                if (fileInput) {
                    fileInput.value = "";
                }
                syncFromUrl();
            }, 0);
        });

        syncFromUrl();
    });
})();

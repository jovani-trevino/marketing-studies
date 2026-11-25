(() => {
    function slugify(value) {
        return value
            .toLowerCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .replace(/[^a-z0-9]+/g, "-")
            .replace(/^-+|-+$/g, "");
    }

    document.querySelectorAll("[data-auto-slug-target]").forEach((target) => {
        const sourceSelector = target.getAttribute("data-auto-slug-source") || "#title";
        const source = document.querySelector(sourceSelector);
        if (!source) {
            return;
        }
        const keepExistingSlug = target.value.trim() !== "";
        const syncSlug = () => {
            if (!keepExistingSlug) target.value = slugify(source.value);
        };
        source.addEventListener("input", syncSlug);
        syncSlug();
    });

    const editors = document.querySelectorAll("[data-markdown-editor]");
    const editorInstances = new WeakMap();
    if (!editors.length) {
        return;
    }

    editors.forEach(editor => {
        const input = editor.querySelector("[data-md-input]");
        const html = editor.querySelector("[data-md-html-input]");
        input?.addEventListener("input", () => {
            if (!editorInstances.has(editor) && html) html.value = "";
        });
    });

    if (!window.Quill || !window.TurndownService) {
        editors.forEach(editor => {
            editor.querySelectorAll(".markdown-toolbar,.editor-assist-strip").forEach(node => { node.hidden = true; });
            const hint = editor.querySelector(".markdown-editor-hint");
            if (hint) hint.textContent = "El editor visual no pudo cargarse. Puedes editar el contenido en Markdown y guardarlo desde este formulario.";
        });
        return;
    }

    const Quill = window.Quill;
    const BlockEmbed = Quill.import("blots/block/embed");

    class DividerBlot extends BlockEmbed {}
    DividerBlot.blotName = "divider";
    DividerBlot.tagName = "hr";
    Quill.register(DividerBlot);

    const SNIPPETS = {
        intro: "<h2>Contexto</h2><p>Explica rapido el problema, para quien aplica y la promesa del articulo.</p>",
        steps: "<h2>Paso 1</h2><p>Describe la primera accion.</p><h2>Paso 2</h2><p>Explica como ejecutarla.</p><h2>Paso 3</h2><p>Cierra con el aprendizaje o siguiente paso.</p>",
        cta: "<blockquote><p><strong>Takeaway:</strong> Resume aqui la accion concreta que el lector debe ejecutar hoy.</p></blockquote>",
        summary: "<h2>Resumen ejecutivo</h2><ul><li>Insight 1</li><li>Insight 2</li><li>Insight 3</li></ul>"
    };

    const turndownService = new window.TurndownService({
        headingStyle: "atx",
        hr: "---",
        bulletListMarker: "-",
        codeBlockStyle: "fenced",
        emDelimiter: "*",
        strongDelimiter: "**",
        linkStyle: "inlined"
    });

    turndownService.addRule("horizontalRule", {
        filter: ["hr"],
        replacement: () => "\n\n---\n\n"
    });

    turndownService.addRule("images", {
        filter: "img",
        replacement: (content, node) => {
            const src = node.getAttribute("src") || "";
            const alt = node.getAttribute("alt") || "";
            return src ? `![${alt}](${src})` : "";
        }
    });

    function escapeHtml(value) {
        return String(value || "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");
    }

    function resolveMediaUrl(url, editor) {
        const trimmed = String(url || "").trim().replace(/^<|>$/g, "");
        if (!trimmed) {
            return "";
        }
        if (/^(https?:)?\/\//i.test(trimmed) || /^(mailto|tel):/i.test(trimmed)) {
            return trimmed;
        }
        const basePath = (editor?.dataset.basePath || "").replace(/\/$/, "");
        if (basePath && trimmed.startsWith(`${basePath}/`)) {
            return trimmed;
        }
        if (trimmed.startsWith("/")) {
            return `${basePath}${trimmed}`;
        }
        return basePath ? `${basePath}/${trimmed.replace(/^\/+/, "")}` : trimmed;
    }

    function inlineMarkdown(value, editor) {
        const tokens = {};
        let tokenIndex = 0;
        const nextToken = (prefix) => `%%${prefix}_${tokenIndex++}%%`;
        let text = String(value || "");

        text = text.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, (_, alt, url) => {
            const token = nextToken("IMG");
            const resolved = resolveMediaUrl(url, editor);
            tokens[token] = resolved
                ? `<img src="${escapeHtml(resolved)}" alt="${escapeHtml(alt)}" loading="lazy">`
                : escapeHtml(alt);
            return token;
        });

        text = text.replace(/\[([^\]]+)\]\(([^)]+)\)/g, (_, label, url) => {
            const token = nextToken("LINK");
            const resolved = resolveMediaUrl(url, editor);
            tokens[token] = resolved
                ? `<a href="${escapeHtml(resolved)}" target="_blank" rel="noopener noreferrer">${escapeHtml(label)}</a>`
                : escapeHtml(label);
            return token;
        });

        let html = escapeHtml(text);
        html = html.replace(/`([^`\n]+)`/g, "<code>$1</code>");
        html = html.replace(/\*\*([^*\n]+)\*\*/g, "<strong>$1</strong>");
        html = html.replace(/(?<!\*)\*([^*\n]+)\*(?!\*)/g, "<em>$1</em>");

        return Object.entries(tokens).reduce((nextHtml, [token, tokenHtml]) => nextHtml.split(token).join(tokenHtml), html);
    }

    function markdownToHtml(markdown, editor, fallbackEmpty = true) {
        const source = String(markdown || "").trim();
        if (!source) {
            return fallbackEmpty ? '<p class="preview-empty">Empieza a escribir para comenzar.</p>' : "<p></p>";
        }

        const lines = source.replace(/\r\n/g, "\n").split("\n");
        let html = "";
        let listType = "";
        let inCodeBlock = false;
        let codeLines = [];

        const closeList = () => {
            if (!listType) {
                return;
            }
            html += `</${listType}>`;
            listType = "";
        };

        const flushCodeBlock = () => {
            if (!inCodeBlock) {
                return;
            }
            html += `<pre><code>${escapeHtml(codeLines.join("\n"))}</code></pre>`;
            inCodeBlock = false;
            codeLines = [];
        };

        lines.forEach((line) => {
            const trimmed = line.trim();

            if (/^```/.test(trimmed)) {
                closeList();
                if (inCodeBlock) {
                    flushCodeBlock();
                } else {
                    inCodeBlock = true;
                    codeLines = [];
                }
                return;
            }

            if (inCodeBlock) {
                codeLines.push(line);
                return;
            }

            if (!trimmed) {
                closeList();
                return;
            }

            if (/^(-{3,}|\*{3,}|_{3,})$/.test(trimmed)) {
                closeList();
                html += "<hr>";
                return;
            }

            if (/^###\s+/.test(trimmed)) {
                closeList();
                html += `<h3>${inlineMarkdown(trimmed.replace(/^###\s+/, ""), editor)}</h3>`;
                return;
            }

            if (/^##\s+/.test(trimmed)) {
                closeList();
                html += `<h2>${inlineMarkdown(trimmed.replace(/^##\s+/, ""), editor)}</h2>`;
                return;
            }

            if (/^#\s+/.test(trimmed)) {
                closeList();
                html += `<h1>${inlineMarkdown(trimmed.replace(/^#\s+/, ""), editor)}</h1>`;
                return;
            }

            if (/^>\s?/.test(trimmed)) {
                closeList();
                html += `<blockquote>${inlineMarkdown(trimmed.replace(/^>\s?/, ""), editor)}</blockquote>`;
                return;
            }

            if (/^\d+\.\s+/.test(trimmed)) {
                if (listType !== "ol") {
                    closeList();
                    html += "<ol>";
                    listType = "ol";
                }
                html += `<li>${inlineMarkdown(trimmed.replace(/^\d+\.\s+/, ""), editor)}</li>`;
                return;
            }

            if (/^[-*]\s+/.test(trimmed)) {
                if (listType !== "ul") {
                    closeList();
                    html += "<ul>";
                    listType = "ul";
                }
                html += `<li>${inlineMarkdown(trimmed.replace(/^[-*]\s+/, ""), editor)}</li>`;
                return;
            }

            closeList();
            html += `<p>${inlineMarkdown(trimmed, editor)}</p>`;
        });

        closeList();
        flushCodeBlock();

        return html || (fallbackEmpty ? '<p class="preview-empty">Empieza a escribir para comenzar.</p>' : "<p></p>");
    }

    function cleanSemanticHtml(html) {
        const template = document.createElement("template");
        template.innerHTML = html || "";
        template.content.querySelectorAll(".ql-ui,script,style,object,embed,link,meta,form,input,button,textarea,select").forEach((node) => node.remove());
        template.content.querySelectorAll("iframe").forEach(node => {
            if (!/^https:\/\/(?:www\.)?(?:youtube(?:-nocookie)?\.com\/embed\/|player\.vimeo\.com\/video\/)/i.test(node.getAttribute("src") || "")) node.remove();
        });
        template.content.querySelectorAll("*").forEach(node => {
            [...node.attributes].forEach(attribute => {
                if (/^on/i.test(attribute.name) || attribute.name === "srcdoc") node.removeAttribute(attribute.name);
                if (["href", "src"].includes(attribute.name) && /^\s*(?:javascript|vbscript|data):/i.test(attribute.value)) node.removeAttribute(attribute.name);
            });
        });
        return template.innerHTML.trim();
    }

    function isEditorContentEmpty(quill) {
        const text = quill.getText().replace(/\s+/g, "").trim();
        const hasEmbeds = !!quill.root.querySelector("img, hr");
        return text === "" && !hasEmbeds;
    }

    function editorHtml(quill) {
        if (isEditorContentEmpty(quill)) {
            return "";
        }
        return cleanSemanticHtml(quill.root.innerHTML);
    }

    function htmlToMarkdown(html) {
        const normalized = cleanSemanticHtml(html);
        if (!normalized) {
            return "";
        }
        return turndownService.turndown(normalized).trim();
    }

    function extractOutline(markdown) {
        const lines = String(markdown || "").replace(/\r\n/g, "\n").split("\n");
        const items = [];
        let offset = 0;

        lines.forEach((line) => {
            const trimmed = line.trim();
            const match = trimmed.match(/^(#{1,3})\s+(.+)$/);
            if (match) {
                items.push({
                    level: match[1].length,
                    label: match[2].replace(/[*`_[\]()!]/g, "").trim() || `Seccion ${items.length + 1}`,
                    offset
                });
            }
            offset += line.length + 1;
        });

        return items;
    }

    function setStatus(node, message, tone = "ready") {
        if (!node) {
            return;
        }
        node.textContent = message;
        node.classList.toggle("is-uploading", tone === "uploading");
        node.classList.toggle("is-error", tone === "error");
    }

    function syncToolbarState(editor, quill) {
        const range = quill.getSelection();
        const format = range ? quill.getFormat(range) : {};

        editor.querySelectorAll("[data-md-command]").forEach((button) => {
            const command = button.dataset.mdCommand;
            const active =
                (command === "bold" && !!format.bold) ||
                (command === "italic" && !!format.italic) ||
                (command === "underline" && !!format.underline) ||
                (command === "strike" && !!format.strike) ||
                (command === "sub" && format.script === "sub") ||
                (command === "super" && format.script === "super") ||
                (command === "quote" && !!format.blockquote) ||
                (command === "ul" && format.list === "bullet") ||
                (command === "ol" && format.list === "ordered") ||
                (command === "check" && format.list === "check") ||
                (command === "link" && !!format.link) ||
                (command === "inline-code" && !!format.code) ||
                (command === "code" && !!format["code-block"]) ||
                (command === "h1" && format.header === 1 && !format.size) ||
                (command === "h2" && format.header === 2) ||
                (command === "h3" && format.header === 3) ||
                (command === "align-left" && !format.align) ||
                (command === "align-center" && format.align === "center") ||
                (command === "align-right" && format.align === "right") ||
                (command === "align-justify" && format.align === "justify");

            button.classList.toggle("is-active", active);
        });

        const blockSelect = editor.querySelector('[data-md-select="block"]');
        if (blockSelect) {
            blockSelect.value = format.header === 1 ? "h1" : format.header === 2 ? "h2" : format.header === 3 ? "h3" : "paragraph";
        }

        const sizeSelect = editor.querySelector('[data-md-select="size"]');
        if (sizeSelect) {
            sizeSelect.value = typeof format.size === "string" ? format.size : "normal";
        }

        editor.querySelectorAll("[data-md-color]").forEach((input) => {
            const kind = input.dataset.mdColor;
            if (kind === "color") {
                input.value = normalizeHexColor(format.color || "#14243a");
            }
            if (kind === "background") {
                input.value = normalizeHexColor(format.background || "#fff1b8");
            }
        });
    }

    function updateStats(editor, quill, textarea, htmlField) {
        const text = quill.getText().trim();
        const words = text ? text.split(/\s+/).filter(Boolean).length : 0;
        const readTime = Math.max(1, Math.ceil(words / 220));
        const html = editorHtml(quill);
        const markdown = htmlToMarkdown(html);
        const outline = extractOutline(markdown);
        const wordsNode = editor.querySelector("[data-md-words]");
        const readNode = editor.querySelector("[data-md-read]");
        const structureNode = editor.querySelector("[data-md-structure]");
        const shell = editor.closest("[data-md-shell]");
        const shellWordsNode = shell?.querySelector("[data-md-shell-words]");
        const shellReadNode = shell?.querySelector("[data-md-shell-read]");
        const shellStructureNode = shell?.querySelector("[data-md-shell-structure]");

        textarea.value = markdown;
        htmlField.value = html;
        if (wordsNode) {
            wordsNode.textContent = `${words} ${words === 1 ? "palabra" : "palabras"}`;
        }
        if (shellWordsNode) {
            shellWordsNode.textContent = `${words} ${words === 1 ? "palabra" : "palabras"}`;
        }
        if (readNode) {
            readNode.textContent = `${readTime} min lectura`;
        }
        if (shellReadNode) {
            shellReadNode.textContent = `${readTime} min`;
        }
        if (structureNode) {
            structureNode.textContent = `${outline.length} ${outline.length === 1 ? "seccion" : "secciones"}`;
        }
        if (shellStructureNode) {
            shellStructureNode.textContent = `${outline.length} ${outline.length === 1 ? "seccion" : "secciones"}`;
        }
        syncToolbarState(editor, quill);
        return { html, markdown };
    }

    function insertSnippet(quill, html) {
        const range = quill.getSelection(true) || { index: quill.getLength(), length: 0 };
        quill.clipboard.dangerouslyPasteHTML(range.index, html, "user");
        quill.focus();
    }

    function toggleLineFormat(quill, name, value) {
        const range = quill.getSelection(true) || { index: 0, length: 0 };
        const current = quill.getFormat(range);
        const nextValue = current[name] === value ? false : value;
        quill.formatLine(range.index, range.length || 1, name, nextValue, "user");
        quill.focus();
    }

    function toggleInlineFormat(quill, name) {
        const range = quill.getSelection(true) || { index: quill.getLength(), length: 0 };
        const current = quill.getFormat(range);
        quill.format(name, !current[name], "user");
        quill.focus();
    }

    function normalizeHexColor(value) {
        const raw = String(value || "").trim().toLowerCase();
        if (!raw) {
            return "#14243a";
        }
        if (/^#[0-9a-f]{6}$/.test(raw)) {
            return raw;
        }
        if (/^#[0-9a-f]{3}$/.test(raw)) {
            return `#${raw[1]}${raw[1]}${raw[2]}${raw[2]}${raw[3]}${raw[3]}`;
        }
        const rgb = raw.match(/^rgb\s*\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)$/i);
        if (rgb) {
            return `#${[rgb[1], rgb[2], rgb[3]].map((part) => Number(part).toString(16).padStart(2, "0")).join("")}`;
        }
        return "#14243a";
    }

    function toggleScriptFormat(quill, script) {
        const range = quill.getSelection(true) || { index: quill.getLength(), length: 0 };
        const current = quill.getFormat(range);
        quill.format("script", current.script === script ? false : script, "user");
        quill.focus();
    }

    function changeBlockType(quill, value) {
        const range = quill.getSelection(true) || { index: 0, length: 0 };
        const header = value === "paragraph" ? false : Number(value.replace("h", ""));
        quill.formatLine(range.index, range.length || 1, "header", header, "user");
        quill.focus();
    }

    function changeTextSize(quill, value) {
        const normalized = value === "normal" ? false : value;
        quill.format("size", normalized, "user");
        quill.focus();
    }

    function setAlign(quill, value) {
        const normalized = value === "left" ? false : value;
        const range = quill.getSelection(true) || { index: 0, length: 0 };
        quill.formatLine(range.index, range.length || 1, "align", normalized, "user");
        quill.focus();
    }

    function changeIndent(quill, direction) {
        const range = quill.getSelection(true) || { index: 0, length: 0 };
        quill.formatLine(range.index, range.length || 1, "indent", direction, "user");
        quill.focus();
    }

    function clearFormatting(quill) {
        const range = quill.getSelection(true) || { index: 0, length: 0 };
        if (range.length > 0) {
            quill.removeFormat(range.index, range.length, "user");
        } else {
            quill.formatLine(range.index, 1, {
                header: false,
                blockquote: false,
                list: false,
                indent: false,
                align: false,
                direction: false,
                "code-block": false
            }, "user");
            quill.formatText(range.index, 1, {
                bold: false,
                italic: false,
                underline: false,
                strike: false,
                script: false,
                color: false,
                background: false,
                code: false,
                link: false,
                size: false
            }, "user");
        }
        quill.focus();
    }

    function insertDivider(quill) {
        const range = quill.getSelection(true) || { index: quill.getLength(), length: 0 };
        quill.insertText(range.index, "\n", "user");
        quill.insertEmbed(range.index + 1, "divider", true, "user");
        quill.insertText(range.index + 2, "\n", "user");
        quill.setSelection(range.index + 3, 0, "user");
        quill.focus();
    }

    function insertCallout(quill) {
        const range = quill.getSelection(true) || { index: quill.getLength(), length: 0 };
        const text = "Nota: Agrega aqui el insight clave.";
        quill.insertText(range.index, text, "user");
        quill.formatLine(range.index, text.length, "blockquote", true, "user");
        quill.setSelection(range.index + 6, text.length - 6, "user");
        quill.focus();
    }

    async function uploadImages(editor, quill, files) {
        const uploadUrl = editor.dataset.uploadUrl;
        const csrfToken = editor.dataset.csrfToken || "";
        const statusNode = editor.querySelector("[data-md-upload-status]");
        const imageFiles = Array.from(files || []);
        const allowedTypes = ["image/jpeg", "image/png", "image/webp", "image/gif"];

        if (!uploadUrl || !imageFiles.length) {
            return;
        }
        if (imageFiles.some(file => !allowedTypes.includes(file.type))) {
            setStatus(statusNode, "Selecciona imágenes JPG, PNG, WebP o GIF.", "error");
            return;
        }
        if (imageFiles.some(file => file.size > 5 * 1024 * 1024)) {
            setStatus(statusNode, "Cada imagen debe pesar como máximo 5 MB.", "error");
            return;
        }
        if (editor.dataset.uploading === "true") {
            setStatus(statusNode, "Espera a que termine la subida actual.", "uploading");
            return;
        }
        const form = editor.closest("form");
        editor.dataset.uploading = "true";
        if (form) form.dataset.editorUploads = String(Number(form.dataset.editorUploads || 0) + 1);

        setStatus(statusNode, `Subiendo ${imageFiles.length} imagen${imageFiles.length === 1 ? "" : "es"}...`, "uploading");

        try {
            let cursor = (quill.getSelection(true) || { index: quill.getLength() }).index;

            for (const file of imageFiles) {
                const formData = new FormData();
                formData.append("csrf_token", csrfToken);
                formData.append("editor_image_file", file, file.name);

                const controller = new AbortController();
                const timeout = window.setTimeout(() => controller.abort(), 30000);
                let response;
                try {
                    response = await fetch(uploadUrl, {
                        method: "POST", body: formData, credentials: "same-origin",
                        headers: { "X-Requested-With": "XMLHttpRequest" }, signal: controller.signal
                    });
                } finally { window.clearTimeout(timeout); }

                const result = await response.json().catch(() => ({ ok: false, message: "No se pudo interpretar la respuesta del servidor." }));
                if (!response.ok || !result.ok || !result.url) {
                    throw new Error(result.message || "No se pudo subir la imagen.");
                }

                quill.insertEmbed(cursor, "image", resolveMediaUrl(result.url, editor), "user");
                cursor += 1;
                quill.insertText(cursor, "\n", "user");
                cursor += 1;
            }

            quill.setSelection(cursor, 0, "user");
            quill.focus();
            setStatus(statusNode, `${imageFiles.length} imagen${imageFiles.length === 1 ? "" : "es"} agregada${imageFiles.length === 1 ? "" : "s"} al editor.`);
        } catch (error) {
            setStatus(statusNode, error?.name === "AbortError" ? "La subida tardó demasiado. El texto sigue aquí; intenta subir la imagen otra vez." : error instanceof Error ? error.message : "La subida falló. Intenta otra vez.", "error");
        } finally {
            delete editor.dataset.uploading;
            if (form) form.dataset.editorUploads = String(Math.max(0, Number(form.dataset.editorUploads || 1) - 1));
        }
    }

    function createImageResizer(editor, quill, onChange) {
        const container = quill.container;
        if (!container) {
            return {
                refresh() {},
                destroy() {}
            };
        }

        const overlay = document.createElement("div");
        overlay.className = "ql-image-resizer";
        overlay.hidden = true;
        overlay.innerHTML = `
            <div class="ql-image-resizer-toolbar" data-image-toolbar>
                <button type="button" class="ql-image-align-button" data-image-align="left" aria-label="Alinear imagen a la izquierda" title="Izquierda">
                    <i class="bi bi-text-left"></i>
                </button>
                <button type="button" class="ql-image-align-button" data-image-align="center" aria-label="Centrar imagen" title="Centrar">
                    <i class="bi bi-text-center"></i>
                </button>
                <button type="button" class="ql-image-align-button" data-image-align="right" aria-label="Alinear imagen a la derecha" title="Derecha">
                    <i class="bi bi-text-right"></i>
                </button>
            </div>
            <div class="ql-image-resizer-size" data-image-size hidden></div>
            <button type="button" class="ql-image-resizer-handle" data-image-handle aria-label="Cambiar tamano de imagen"></button>
        `;
        container.appendChild(overlay);

        const sizeNode = overlay.querySelector("[data-image-size]");
        const handle = overlay.querySelector("[data-image-handle]");
        const alignButtons = overlay.querySelectorAll("[data-image-align]");
        let activeImage = null;
        let dragState = null;

        function imageIndex(image) {
            if (!image) {
                return null;
            }
            const blot = Quill.find(image);
            if (!blot) {
                return null;
            }
            return quill.getIndex(blot);
        }

        function currentImageAlign(image) {
            const index = imageIndex(image);
            if (index === null) {
                return "left";
            }
            const format = quill.getFormat(index, 1);
            return typeof format.align === "string" && format.align ? format.align : "left";
        }

        function updateAlignButtons() {
            const currentAlign = currentImageAlign(activeImage);
            alignButtons.forEach((button) => {
                button.classList.toggle("is-active", button.dataset.imageAlign === currentAlign);
            });
        }

        function applyImageAlign(align) {
            if (!activeImage) {
                return;
            }
            const index = imageIndex(activeImage);
            if (index === null) {
                return;
            }
            quill.formatLine(index, 1, "align", align === "left" ? false : align, "user");
            updateAlignButtons();
            window.requestAnimationFrame(positionOverlay);
            onChange?.();
            quill.focus();
        }

        function cycleImageAlign() {
            const currentAlign = currentImageAlign(activeImage);
            const order = ["left", "center", "right"];
            const currentIndex = Math.max(0, order.indexOf(currentAlign));
            const nextAlign = order[(currentIndex + 1) % order.length];
            applyImageAlign(nextAlign);
        }

        function clearSelection() {
            if (activeImage) {
                activeImage.classList.remove("is-resizable-selected");
            }
            activeImage = null;
            dragState = null;
            overlay.hidden = true;
            if (sizeNode) {
                sizeNode.hidden = true;
            }
        }

        function maxImageWidth() {
            const rootStyles = window.getComputedStyle(quill.root);
            const paddingLeft = Number.parseFloat(rootStyles.paddingLeft || "0") || 0;
            const paddingRight = Number.parseFloat(rootStyles.paddingRight || "0") || 0;
            return Math.max(140, quill.root.clientWidth - paddingLeft - paddingRight);
        }

        function positionOverlay() {
            if (!activeImage || !activeImage.isConnected) {
                clearSelection();
                return;
            }

            const containerRect = container.getBoundingClientRect();
            const imageRect = activeImage.getBoundingClientRect();

            overlay.style.width = `${imageRect.width}px`;
            overlay.style.height = `${imageRect.height}px`;
            overlay.style.transform = `translate(${imageRect.left - containerRect.left + container.scrollLeft}px, ${imageRect.top - containerRect.top + container.scrollTop}px)`;

            if (sizeNode) {
                sizeNode.hidden = false;
                sizeNode.textContent = `${Math.round(imageRect.width)} x ${Math.round(imageRect.height)}`;
            }
        }

        function applyImageSize(image, nextWidth) {
            if (!image) {
                return;
            }

            const naturalWidth = image.naturalWidth || maxImageWidth();
            const clampedWidth = Math.max(140, Math.min(nextWidth, Math.min(naturalWidth, maxImageWidth())));
            const roundedWidth = Math.round(clampedWidth);

            image.style.width = `${roundedWidth}px`;
            image.style.maxWidth = "100%";
            image.style.height = "auto";
            image.setAttribute("width", String(roundedWidth));
            image.removeAttribute("height");
        }

        function selectImage(image) {
            if (activeImage && activeImage !== image) {
                activeImage.classList.remove("is-resizable-selected");
            }
            activeImage = image;
            activeImage.classList.add("is-resizable-selected");
            const index = imageIndex(image);
            if (index !== null) {
                quill.setSelection(index, 1, "silent");
            }
            overlay.hidden = false;
            updateAlignButtons();
            positionOverlay();
        }

        editor.addEventListener("click", (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement)) {
                return;
            }

            if (target.tagName === "IMG" && quill.root.contains(target)) {
                selectImage(target);
                return;
            }

            if (!overlay.contains(target)) {
                clearSelection();
            }
        });

        quill.root.addEventListener("keyup", () => {
            if (activeImage) {
                window.requestAnimationFrame(positionOverlay);
            }
        });

        quill.on("text-change", () => {
            if (activeImage) {
                window.requestAnimationFrame(positionOverlay);
            }
        });

        handle?.addEventListener("mousedown", (event) => {
            if (!activeImage) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            const rect = activeImage.getBoundingClientRect();
            dragState = {
                startX: event.clientX,
                startWidth: rect.width
            };

            document.body.classList.add("is-resizing-editor-image");
        });

        alignButtons.forEach((button) => {
            button.addEventListener("mousedown", (event) => {
                event.preventDefault();
                event.stopPropagation();
            });
            button.addEventListener("click", (event) => {
                event.preventDefault();
                event.stopPropagation();
                applyImageAlign(button.dataset.imageAlign || "left");
            });
        });

        window.addEventListener("mousemove", (event) => {
            if (!dragState || !activeImage) {
                return;
            }

            const deltaX = event.clientX - dragState.startX;
            applyImageSize(activeImage, dragState.startWidth + deltaX);
            positionOverlay();
        });

        window.addEventListener("mouseup", () => {
            if (!dragState) {
                return;
            }

            dragState = null;
            document.body.classList.remove("is-resizing-editor-image");
            positionOverlay();
            onChange?.();
        });

        window.addEventListener("resize", () => {
            if (activeImage) {
                positionOverlay();
            }
        });

        return {
            hasActiveImage() {
                return !!activeImage;
            },
            cycleAlign: cycleImageAlign,
            refresh() {
                if (activeImage) {
                    updateAlignButtons();
                    positionOverlay();
                }
            },
            destroy() {
                clearSelection();
                overlay.remove();
            }
        };
    }

    function hasDraggedFiles(dataTransfer) {
        return !!dataTransfer && Array.from(dataTransfer.types || []).includes("Files");
    }

    function parseSavedDraft(rawValue) {
        if (!rawValue) {
            return null;
        }
        try {
            const parsed = JSON.parse(rawValue);
            if (parsed && typeof parsed === "object") {
                return {
                    html: typeof parsed.html === "string" ? parsed.html : "",
                    markdown: typeof parsed.markdown === "string" ? parsed.markdown : ""
                };
            }
        } catch {}
        return {
            html: "",
            markdown: String(rawValue)
        };
    }

    function bindToolbar(editor, quill, uploadInput) {
        editor.querySelectorAll("[data-md-select]").forEach((select) => {
            select.addEventListener("change", () => {
                const kind = select.dataset.mdSelect;
                if (kind === "block") {
                    changeBlockType(quill, select.value);
                }
                if (kind === "size") {
                    changeTextSize(quill, select.value);
                }
            });
        });

        editor.querySelectorAll("[data-md-color]").forEach((input) => {
            input.addEventListener("input", () => {
                const kind = input.dataset.mdColor;
                const value = input.value || "";
                quill.format(kind, value, "user");
                quill.focus();
            });
        });

        editor.querySelectorAll("[data-md-command]").forEach((button) => {
            button.addEventListener("click", () => {
                const command = button.dataset.mdCommand;

                switch (command) {
                    case "h1":
                        toggleLineFormat(quill, "header", 1);
                        break;
                    case "h2":
                        toggleLineFormat(quill, "header", 2);
                        break;
                    case "h3":
                        toggleLineFormat(quill, "header", 3);
                        break;
                    case "bold":
                        toggleInlineFormat(quill, "bold");
                        break;
                    case "italic":
                        toggleInlineFormat(quill, "italic");
                        break;
                    case "underline":
                        toggleInlineFormat(quill, "underline");
                        break;
                    case "strike":
                        toggleInlineFormat(quill, "strike");
                        break;
                    case "sub":
                        toggleScriptFormat(quill, "sub");
                        break;
                    case "super":
                        toggleScriptFormat(quill, "super");
                        break;
                    case "quote":
                        toggleLineFormat(quill, "blockquote", true);
                        break;
                    case "ul":
                        toggleLineFormat(quill, "list", "bullet");
                        break;
                    case "ol":
                        toggleLineFormat(quill, "list", "ordered");
                        break;
                    case "check":
                        toggleLineFormat(quill, "list", "check");
                        break;
                    case "indent-":
                        changeIndent(quill, "-1");
                        break;
                    case "indent+":
                        changeIndent(quill, "+1");
                        break;
                    case "align-left":
                        setAlign(quill, "left");
                        break;
                    case "align-center":
                        setAlign(quill, "center");
                        break;
                    case "align-right":
                        setAlign(quill, "right");
                        break;
                    case "align-justify":
                        setAlign(quill, "justify");
                        break;
                    case "link": {
                        const range = quill.getSelection(true) || { index: quill.getLength(), length: 0 };
                        const current = quill.getFormat(range);
                        const typed = window.prompt("Pega la URL del enlace", typeof current.link === "string" ? current.link : "https://");
                        if (typed === null) {
                            return;
                        }
                        const url = typed.trim();
                        if (!url) {
                            quill.format("link", false, "user");
                            quill.focus();
                            return;
                        }
                        try {
                            const parsed = new URL(url, window.location.href);
                            if (!["http:", "https:", "mailto:", "tel:"].includes(parsed.protocol)) throw new Error();
                        } catch {
                            window.alert("Usa un enlace HTTP, HTTPS, de correo o de teléfono válido.");
                            return;
                        }
                        if (range.length === 0) {
                            quill.insertText(range.index, url, "user");
                            quill.setSelection(range.index, url.length, "silent");
                        }
                        quill.format("link", url, "user");
                        quill.focus();
                        break;
                    }
                    case "image":
                        uploadInput?.click();
                        break;
                    case "divider":
                        insertDivider(quill);
                        break;
                    case "inline-code":
                        toggleInlineFormat(quill, "code");
                        break;
                    case "code":
                        toggleLineFormat(quill, "code-block", true);
                        break;
                    case "callout":
                        insertCallout(quill);
                        break;
                    case "clear":
                        clearFormatting(quill);
                        break;
                    case "undo":
                        quill.history.undo();
                        quill.focus();
                        break;
                    case "redo":
                        quill.history.redo();
                        quill.focus();
                        break;
                    default:
                        break;
                }
            });
        });

        editor.querySelectorAll("[data-md-snippet]").forEach((button) => {
            button.addEventListener("click", () => {
                const snippet = SNIPPETS[button.dataset.mdSnippet || ""];
                if (snippet) {
                    insertSnippet(quill, snippet);
                }
            });
        });
    }

    function syncShellState(editor) {
        const shell = editor?.closest("[data-md-shell]");
        const stateNode = shell?.querySelector("[data-md-shell-state]");
        if (stateNode) {
            stateNode.textContent = shell?.open ? "Ocultar editor" : "Abrir editor";
        }
    }

    function editorIsVisible(editor) {
        const shell = editor.closest("[data-md-shell]");
        if (shell && !shell.open) {
            return false;
        }

        const tabPane = editor.closest(".tab-pane");
        if (tabPane && !(tabPane.classList.contains("active") || tabPane.classList.contains("show"))) {
            return false;
        }

        return true;
    }

    function refreshEditor(editor) {
        const instance = editorInstances.get(editor);
        if (!instance) {
            return;
        }

        window.requestAnimationFrame(() => instance.refresh());
    }

    function initializeEditor(editor) {
        if (editorInstances.has(editor)) {
            return editorInstances.get(editor);
        }

        const textarea = editor.querySelector("[data-md-input]");
        const htmlField = editor.querySelector("[data-md-html-input]");
        const host = editor.querySelector("[data-md-editor-host]");
        const saveNode = editor.querySelector("[data-md-save]");
        const recovery = editor.querySelector("[data-md-recovery]");
        const uploadInput = editor.querySelector("[data-md-upload-input]");
        const storageKey = textarea?.dataset.storageKey;
        let saveTimer = null;
        let dragDepth = 0;

        if (!textarea || !host || !htmlField) {
            return null;
        }

        const notifyChange = () => editor.dispatchEvent(new CustomEvent("admin:content-change", {bubbles:true}));
        const readLocalDraft = () => {
            try { return storageKey ? parseSavedDraft(localStorage.getItem(storageKey)) : null; }
            catch { return null; }
        };

        host.hidden = false;
        editor.classList.add("is-enhanced");
        textarea.required = false;

        const quill = new Quill(host, {
            theme: "snow",
            placeholder: "Empieza a escribir aqui...",
            modules: {
                toolbar: false,
                history: {
                    delay: 400,
                    maxStack: 200,
                    userOnly: true
                }
            }
        });
        quill.root.setAttribute("role", "textbox");
        quill.root.setAttribute("aria-multiline", "true");
        quill.root.setAttribute("aria-label", editor.querySelector(".markdown-editor-head .form-label")?.textContent.trim() || "Contenido del artículo");

        const initialHtml = cleanSemanticHtml(htmlField.value || "") || markdownToHtml(textarea.value, editor, false);
        quill.clipboard.dangerouslyPasteHTML(initialHtml, "silent");

        const queueAutosave = (draft) => {
            if (!storageKey) {
                return;
            }
            if (saveNode) {
                saveNode.textContent = "Copiando en este navegador…";
            }
            window.clearTimeout(saveTimer);
            saveTimer = window.setTimeout(() => {
                try {
                    localStorage.setItem(storageKey, JSON.stringify(draft));
                    if (saveNode) saveNode.textContent = "Copia local actualizada";
                } catch {
                    if (saveNode) saveNode.textContent = "Sin copia local. Guarda el artículo.";
                }
            }, 350);
        };

        const imageResizer = createImageResizer(editor, quill, () => {
            const draft = updateStats(editor, quill, textarea, htmlField);
            queueAutosave(draft);
            notifyChange();
        });

        const savedDraft = readLocalDraft();
        if (savedDraft && ((savedDraft.html && savedDraft.html !== initialHtml) || (savedDraft.markdown && savedDraft.markdown !== textarea.value)) && recovery) {
            recovery.hidden = false;
            recovery.querySelector("[data-md-restore]")?.addEventListener("click", () => {
                const restoreHtml = savedDraft.html || markdownToHtml(savedDraft.markdown, editor, false);
                quill.setText("");
                quill.clipboard.dangerouslyPasteHTML(restoreHtml || "<p></p>", "silent");
                updateStats(editor, quill, textarea, htmlField);
                recovery.hidden = true;
                notifyChange();
                quill.focus();
            });
            recovery.querySelector("[data-md-discard]")?.addEventListener("click", () => {
                try { localStorage.removeItem(storageKey); } catch { /* Storage may be unavailable. */ }
                recovery.hidden = true;
            });
        }

        bindToolbar(editor, quill, uploadInput);

        const fullscreenButton = editor.querySelector("[data-md-fullscreen]");
        const setFullscreen = open => {
            editor.classList.toggle("is-fullscreen", open);
            document.body.classList.toggle("editor-fullscreen-open", open);
            fullscreenButton?.setAttribute("aria-pressed", String(open));
            fullscreenButton?.setAttribute("aria-label", open ? "Salir de pantalla completa" : "Pantalla completa");
            refreshEditor(editor);
            if (open) quill.focus(); else fullscreenButton?.focus();
        };
        fullscreenButton?.setAttribute("aria-pressed", "false");
        fullscreenButton?.addEventListener("click", () => setFullscreen(!editor.classList.contains("is-fullscreen")));
        editor.addEventListener("keydown", event => {
            if (!editor.classList.contains("is-fullscreen")) return;
            if (event.key === "Escape") { event.preventDefault(); setFullscreen(false); }
            if (event.key === "Tab") {
                const focusable = [...editor.querySelectorAll('button:not([disabled]),select:not([disabled]),input:not([disabled]),[contenteditable="true"],a[href]')].filter(node => node.offsetParent !== null);
                const first = focusable[0], last = focusable.at(-1);
                if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
                if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
            }
        });

        uploadInput?.addEventListener("change", async () => {
            if (uploadInput.files?.length) {
                await uploadImages(editor, quill, uploadInput.files);
                updateStats(editor, quill, textarea, htmlField);
            }
            uploadInput.value = "";
        });

        quill.root.addEventListener("keydown", (event) => {
            if (imageResizer.hasActiveImage() && event.key === " " && !event.ctrlKey && !event.metaKey && !event.altKey) {
                event.preventDefault();
                imageResizer.cycleAlign();
                return;
            }

            if (!(event.ctrlKey || event.metaKey)) {
                return;
            }
            const key = event.key.toLowerCase();
            if (key === "b") {
                event.preventDefault();
                toggleInlineFormat(quill, "bold");
            }
            if (key === "i") {
                event.preventDefault();
                toggleInlineFormat(quill, "italic");
            }
            if (key === "k") {
                event.preventDefault();
                const activeLink = editor.querySelector('[data-md-command="link"]');
                activeLink?.click();
            }
            if (key === "u") {
                event.preventDefault();
                toggleInlineFormat(quill, "underline");
            }
        });

        quill.root.addEventListener("paste", async (event) => {
            const files = Array.from(event.clipboardData?.items || [])
                .map((item) => item.getAsFile())
                .filter((file) => file && String(file.type || "").startsWith("image/"));

            if (!files.length) {
                return;
            }

            event.preventDefault();
            await uploadImages(editor, quill, files);
            updateStats(editor, quill, textarea, htmlField);
        });

        ["dragenter", "dragover"].forEach((eventName) => {
            editor.addEventListener(eventName, (event) => {
                if (!hasDraggedFiles(event.dataTransfer)) {
                    return;
                }
                event.preventDefault();
                if (eventName === "dragenter") {
                    dragDepth += 1;
                }
                editor.classList.add("is-dragover");
            });
        });

        editor.addEventListener("dragleave", (event) => {
            if (!hasDraggedFiles(event.dataTransfer)) {
                return;
            }
            event.preventDefault();
            dragDepth = Math.max(0, dragDepth - 1);
            if (!dragDepth) {
                editor.classList.remove("is-dragover");
            }
        });

        editor.addEventListener("drop", async (event) => {
            if (!hasDraggedFiles(event.dataTransfer)) {
                return;
            }
            event.preventDefault();
            dragDepth = 0;
            editor.classList.remove("is-dragover");
            await uploadImages(editor, quill, event.dataTransfer.files);
            updateStats(editor, quill, textarea, htmlField);
        });

        quill.on("text-change", (_delta, _old, source) => {
            const draft = updateStats(editor, quill, textarea, htmlField);
            queueAutosave(draft);
            if (source !== "silent") notifyChange();
        });

        quill.on("selection-change", () => syncToolbarState(editor, quill));

        const form = editor.closest("form");
        form?.addEventListener("submit", event => {
            if (Number(form.dataset.editorUploads || 0) > 0) {
                event.preventDefault();
                const state = form.querySelector("[data-admin-save-state]");
                if (state) state.textContent = "Espera a que terminen de subir las imágenes antes de guardar.";
                return;
            }
            const draft = updateStats(editor, quill, textarea, htmlField);
            textarea.value = draft.markdown;
            htmlField.value = draft.html;
        });

        updateStats(editor, quill, textarea, htmlField);

        const instance = {
            quill,
            refresh() {
                quill.update("silent");
                imageResizer.refresh();
            }
        };

        editorInstances.set(editor, instance);
        return instance;
    }

    function initializeVisibleEditors(scope = document) {
        editors.forEach((editor) => {
            if (scope !== document && scope !== editor && !scope.contains(editor)) {
                return;
            }

            syncShellState(editor);
            if (!editorIsVisible(editor)) {
                return;
            }

            initializeEditor(editor);
            refreshEditor(editor);
        });
    }

    document.querySelectorAll("[data-md-shell]").forEach((shell) => {
        const editor = shell.querySelector("[data-markdown-editor]");
        if (!editor) {
            return;
        }

        syncShellState(editor);
        shell.addEventListener("toggle", () => {
            syncShellState(editor);
            if (shell.open) {
                initializeEditor(editor);
                refreshEditor(editor);
            }
        });
    });

    document.querySelectorAll('[data-bs-toggle="pill"], [data-bs-toggle="tab"]').forEach((trigger) => {
        trigger.addEventListener("shown.bs.tab", (event) => {
            const targetSelector = event.target.getAttribute("data-bs-target");
            const target = targetSelector ? document.querySelector(targetSelector) : null;
            initializeVisibleEditors(target || document);
        });
    });

    initializeVisibleEditors();
})();

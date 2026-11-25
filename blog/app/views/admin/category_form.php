<?php
$categoryColorPresets = category_color_presets();
$currentCategoryColor = normalize_hex_color((string) ($category['accent_color'] ?? '#c9853b'));
$categoryIconCatalog = category_icon_catalog();
$currentCategoryIcon = normalize_bootstrap_icon_class((string) ($category['icon_class'] ?? 'bi bi-megaphone-fill'));
if (!isset($categoryIconCatalog[$currentCategoryIcon])) {
    $categoryIconCatalog = [
        $currentCategoryIcon => [
            'label' => 'Icono actual',
            'hint' => 'Guardado previamente en esta categoria.',
        ],
    ] + $categoryIconCatalog;
}
$categoryPreviewName = trim((string) ($category['name'] ?? '')) ?: 'Nombre de categoria';
$categoryPreviewSlug = trim((string) ($category['slug'] ?? '')) ?: 'slug-canonico';
$currentCategoryColorLabel = 'Color personalizado';
foreach ($categoryColorPresets as $preset) {
    if (strtolower(normalize_hex_color((string) ($preset['value'] ?? '#c9853b'))) === strtolower($currentCategoryColor)) {
        $currentCategoryColorLabel = (string) ($preset['label'] ?? $currentCategoryColorLabel);
        break;
    }
}
$currentCategoryIconLabel = (string) ($categoryIconCatalog[$currentCategoryIcon]['label'] ?? 'Icono actual');
$currentCategoryIconHint = (string) ($categoryIconCatalog[$currentCategoryIcon]['hint'] ?? 'Icono de categoria.');
?>
<section class="section-space">
    <div class="container">
        <?php require __DIR__ . '/nav.php'; ?>
        <?php $isSystemCategory = !empty($systemCategory); ?>
        <div class="admin-header">
            <div>
                <span class="eyebrow">Categoria</span>
                <h1><?= $categoryId ? 'Editar categoria' : 'Nueva categoria' ?></h1>
                <p>Define version base y traducciones EN / ES para que menu, URLs y paginas cambien desde el CMS.</p>
            </div>
            <a href="<?= e(url_path('/admin/categories')) ?>" class="btn btn-outline-dark rounded-pill px-4">Volver</a>
        </div>
        <form method="post" class="admin-card" data-category-designer>
            <?= csrf_input() ?>
            <?php if ($isSystemCategory): ?>
                <div class="alert alert-light border rounded-4 mb-4">
                    <strong><?= e($systemCategory['name'] ?? 'Categoria del sistema') ?></strong> forma parte del CMS.
                    <?= e($systemCategory['publish_copy'] ?? 'Administra su contenido desde su modulo propio.') ?>
                    Puedes editar textos, color e idiomas, pero el slug canonico queda protegido.
                </div>
            <?php endif; ?>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="admin-card h-100">
                        <span class="eyebrow">Base canonica</span>
                        <h2 class="h5 mb-3">Identidad interna</h2>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Nombre base</label><input name="name" class="form-control" value="<?= e($category['name']) ?>" required></div>
                            <div class="col-md-6"><label class="form-label">Slug canonico</label><input name="slug" class="form-control" value="<?= e($category['slug']) ?>" placeholder="marketing" <?= $isSystemCategory ? 'readonly' : '' ?>></div>
                            <div class="col-12"><label class="form-label">Descripcion base</label><textarea name="description" rows="3" class="form-control" required><?= e($category['description']) ?></textarea></div>
                            <div class="col-12"><label class="form-label">Spotlight base</label><textarea name="spotlight" rows="2" class="form-control"><?= e($category['spotlight']) ?></textarea></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="admin-card h-100">
                        <span class="eyebrow">Arquitectura</span>
                        <h2 class="h5 mb-3">Menu y estilo</h2>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Categoria padre</label>
                                <select name="parent_id" class="form-select">
                                    <option value="">Sin padre</option>
                                    <?php foreach ($categoryOptions as $option): ?>
                                        <option value="<?= (int) $option['id'] ?>" <?= (string) ($category['parent_id'] ?? '') === (string) $option['id'] ? 'selected' : '' ?>><?= e($option['tree_label'] ?? $option['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3"><label class="form-label">Orden menu</label><input type="number" name="nav_order" class="form-control" value="<?= e((string) ($category['nav_order'] ?? 0)) ?>"></div>
                            <div class="col-md-3 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="show_in_nav" name="show_in_nav" <?= !isset($category['show_in_nav']) || (string) $category['show_in_nav'] === '1' ? 'checked' : '' ?>><label class="form-check-label" for="show_in_nav">Mostrar en menu</label></div></div>
                            <div class="col-12">
                                <div class="category-design-preview" style="--category-accent: <?= e($currentCategoryColor) ?>" data-category-preview>
                                    <span class="category-design-preview-icon">
                                        <i class="<?= e($currentCategoryIcon) ?>" data-category-preview-icon></i>
                                    </span>
                                    <div class="category-design-preview-copy">
                                        <span class="eyebrow">Vista previa</span>
                                        <strong data-category-preview-name><?= e($categoryPreviewName) ?></strong>
                                        <small data-category-preview-slug>/<?= e($categoryPreviewSlug) ?></small>
                                    </div>
                                    <span class="category-design-preview-chip">Menu</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Color de categoria</label>
                                <div class="category-config-card">
                                    <div class="category-config-head">
                                        <div class="category-color-control">
                                            <input type="color" name="accent_color" class="category-color-input" value="<?= e($currentCategoryColor) ?>" data-category-color-input aria-label="Color de categoria">
                                            <div>
                                                <strong>Tono principal</strong>
                                                <small>Elige un color con el picker o toca una muestra.</small>
                                            </div>
                                        </div>
                                        <span class="category-color-badge" data-category-color-value><?= e($currentCategoryColorLabel) ?></span>
                                    </div>
                                    <div class="category-color-swatches">
                                        <?php foreach ($categoryColorPresets as $preset): ?>
                                            <?php $presetValue = normalize_hex_color((string) ($preset['value'] ?? '#c9853b')); ?>
                                            <button
                                                type="button"
                                                class="category-color-swatch<?= strtolower($presetValue) === strtolower($currentCategoryColor) ? ' is-active' : '' ?>"
                                                style="--swatch-color: <?= e($presetValue) ?>"
                                                data-category-color-option="<?= e($presetValue) ?>"
                                                data-category-color-label="<?= e((string) ($preset['label'] ?? 'Color')) ?>"
                                                title="<?= e($preset['label'] ?? $presetValue) ?>"
                                                aria-label="<?= e($preset['label'] ?? $presetValue) ?>"
                                            ></button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                    <label class="form-label mb-0" id="category_icon_label">Icono</label>
                                    <span class="text-body-secondary small">Elige uno desde la lista.</span>
                                </div>
                                <input type="hidden" id="icon_class" name="icon_class" value="<?= e($currentCategoryIcon) ?>" data-category-icon-input>
                                <div class="dropdown category-icon-dropdown">
                                    <button class="btn category-icon-select dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-labelledby="category_icon_label">
                                        <span class="category-icon-select-current">
                                            <span class="category-icon-option-mark" style="--category-accent: <?= e($currentCategoryColor) ?>">
                                                <i class="<?= e($currentCategoryIcon) ?>" data-category-icon-selected-icon></i>
                                            </span>
                                            <span class="category-icon-option-copy">
                                                <strong data-category-icon-selected-label><?= e($currentCategoryIconLabel) ?></strong>
                                                <small data-category-icon-selected-copy><?= e($currentCategoryIconHint) ?></small>
                                            </span>
                                        </span>
                                    </button>
                                    <div class="dropdown-menu category-icon-dropdown-menu w-100">
                                        <?php foreach ($categoryIconCatalog as $iconClass => $iconDefinition): ?>
                                            <button
                                                type="button"
                                                class="dropdown-item category-icon-option<?= $iconClass === $currentCategoryIcon ? ' is-active' : '' ?>"
                                                data-category-icon-option="<?= e($iconClass) ?>"
                                                data-category-icon-label="<?= e($iconDefinition['label'] ?? $iconClass) ?>"
                                                data-category-icon-hint="<?= e($iconDefinition['hint'] ?? 'Icono de categoria.') ?>"
                                                aria-pressed="<?= $iconClass === $currentCategoryIcon ? 'true' : 'false' ?>"
                                            >
                                                <span class="category-icon-option-mark" style="--category-accent: <?= e($currentCategoryColor) ?>">
                                                    <i class="<?= e($iconClass) ?>"></i>
                                                </span>
                                                <span class="category-icon-option-copy">
                                                    <strong><?= e($iconDefinition['label'] ?? $iconClass) ?></strong>
                                                    <small><?= e($iconDefinition['hint'] ?? 'Icono de categoria.') ?></small>
                                                </span>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php foreach (SUPPORTED_LOCALES as $itemLocale): ?>
                    <div class="col-lg-6">
                        <div class="admin-card h-100">
                            <span class="eyebrow">Idioma <?= e(strtoupper($itemLocale)) ?></span>
                            <h2 class="h5 mb-3">Contenido publico <?= e(strtoupper($itemLocale)) ?></h2>
                            <div class="row g-3">
                                <div class="col-md-7"><label class="form-label">Nombre <?= e(strtoupper($itemLocale)) ?></label><input name="name_<?= e($itemLocale) ?>" class="form-control" value="<?= e($category['name_' . $itemLocale] ?? '') ?>" placeholder="<?= e($itemLocale === 'en' ? 'Marketing' : 'Mercadotecnia') ?>"></div>
                                <div class="col-md-5"><label class="form-label">Slug <?= e(strtoupper($itemLocale)) ?></label><input name="slug_<?= e($itemLocale) ?>" class="form-control" value="<?= e($category['slug_' . $itemLocale] ?? '') ?>" placeholder="<?= e($itemLocale === 'en' ? 'marketing' : 'mercadotecnia') ?>"></div>
                                <div class="col-12"><label class="form-label">Descripcion <?= e(strtoupper($itemLocale)) ?></label><textarea name="description_<?= e($itemLocale) ?>" rows="3" class="form-control"><?= e($category['description_' . $itemLocale] ?? '') ?></textarea></div>
                                <div class="col-12"><label class="form-label">Spotlight <?= e(strtoupper($itemLocale)) ?></label><textarea name="spotlight_<?= e($itemLocale) ?>" rows="2" class="form-control"><?= e($category['spotlight_' . $itemLocale] ?? '') ?></textarea></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="col-12"><button class="btn btn-dark rounded-pill px-4" type="submit">Guardar categoria</button></div>
            </div>
        </form>
    </div>
</section>
<script>
(() => {
    const scope = document.querySelector("[data-category-designer]");
    if (!scope) {
        return;
    }

    const colorInput = scope.querySelector("[data-category-color-input]");
    const colorValue = scope.querySelector("[data-category-color-value]");
    const colorButtons = Array.from(scope.querySelectorAll("[data-category-color-option]"));
    const iconInput = scope.querySelector("[data-category-icon-input]");
    const iconButtons = Array.from(scope.querySelectorAll("[data-category-icon-option]"));
    const iconSelectedIcon = scope.querySelector("[data-category-icon-selected-icon]");
    const iconSelectedLabel = scope.querySelector("[data-category-icon-selected-label]");
    const iconSelectedCopy = scope.querySelector("[data-category-icon-selected-copy]");
    const preview = scope.querySelector("[data-category-preview]");
    const previewIcon = scope.querySelector("[data-category-preview-icon]");
    const previewName = scope.querySelector("[data-category-preview-name]");
    const previewSlug = scope.querySelector("[data-category-preview-slug]");
    const nameInput = scope.querySelector('input[name="name"]');
    const slugInput = scope.querySelector('input[name="slug"]');

    const applyColor = (value) => {
        if (!colorInput || !preview) {
            return;
        }
        const normalized = (value || colorInput.value || "#c9853b").toLowerCase();
        colorInput.value = normalized;
        preview.style.setProperty("--category-accent", normalized);
        if (colorValue) {
            const activeSwatch = colorButtons.find((button) => (button.dataset.categoryColorOption || "").toLowerCase() === normalized);
            colorValue.textContent = activeSwatch?.dataset.categoryColorLabel || "Color personalizado";
        }
        colorButtons.forEach((button) => {
            button.classList.toggle("is-active", (button.dataset.categoryColorOption || "").toLowerCase() === normalized);
        });
        scope.querySelectorAll(".category-icon-option-mark").forEach((mark) => {
            mark.style.setProperty("--category-accent", normalized);
        });
    };

    const applyIcon = (iconClass, sourceButton = null) => {
        if (!iconInput || !previewIcon) {
            return;
        }
        const nextIcon = iconClass || iconInput.value || "";
        if (!nextIcon) {
            return;
        }
        iconInput.value = nextIcon;
        previewIcon.className = nextIcon;
        const selectedButton = sourceButton || iconButtons.find((button) => (button.dataset.categoryIconOption || "") === nextIcon) || null;
        if (iconSelectedIcon) {
            iconSelectedIcon.className = nextIcon;
        }
        if (iconSelectedLabel) {
            iconSelectedLabel.textContent = selectedButton?.dataset.categoryIconLabel || "Icono";
        }
        if (iconSelectedCopy) {
            iconSelectedCopy.textContent = selectedButton?.dataset.categoryIconHint || "Icono de categoria.";
        }
        iconButtons.forEach((button) => {
            const isActive = (button.dataset.categoryIconOption || "") === nextIcon;
            button.classList.toggle("is-active", isActive);
            button.setAttribute("aria-pressed", isActive ? "true" : "false");
        });
    };

    const syncPreviewCopy = () => {
        if (previewName && nameInput) {
            previewName.textContent = nameInput.value.trim() || "Nombre de categoria";
        }
        if (previewSlug && slugInput) {
            previewSlug.textContent = "/" + (slugInput.value.trim() || "slug-canonico");
        }
    };

    if (colorInput) {
        colorInput.addEventListener("input", () => applyColor(colorInput.value));
        colorInput.addEventListener("change", () => applyColor(colorInput.value));
        applyColor(colorInput.value);
    }

    colorButtons.forEach((button) => {
        button.addEventListener("click", () => applyColor(button.dataset.categoryColorOption || ""));
    });

    iconButtons.forEach((button) => {
        button.addEventListener("click", () => applyIcon(button.dataset.categoryIconOption || "", button));
    });

    if (iconInput) {
        applyIcon(iconInput.value);
    }

    if (nameInput) {
        nameInput.addEventListener("input", syncPreviewCopy);
    }
    if (slugInput) {
        slugInput.addEventListener("input", syncPreviewCopy);
    }
    syncPreviewCopy();
})();
</script>

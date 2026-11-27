(() => {
    const THEME_KEY = "gb-theme";
    const COOKIE_KEY = "gb-cookie-consent";
    const root = document.documentElement;
    const body = document.body;
    const header = document.querySelector(".site-header");
    const progressBar = document.getElementById("scroll-progress");
    const themeToggle = document.getElementById("theme-toggle");
    const themeLabel = themeToggle?.querySelector(".theme-toggle-label");
    const themeIcon = themeToggle?.querySelector(".theme-toggle-icon");
    const prefersDark = window.matchMedia("(prefers-color-scheme: dark)");
    const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    const runtime = window.GB_RUNTIME || {};
    let recaptchaWidgetId = null;
    let optionalContentActivated = false;

    function readStorage(key) {
        try { return localStorage.getItem(key); } catch { return null; }
    }

    function writeStorage(key, value) {
        try { localStorage.setItem(key, value); return true; } catch { return false; }
    }

    function getStoredTheme() {
        const storedTheme = readStorage(THEME_KEY);
        return storedTheme === "dark" || storedTheme === "light" ? storedTheme : null;
    }

    function getPreferredTheme() {
        return getStoredTheme() || (prefersDark.matches ? "dark" : "light");
    }

    function updateThemeMeta(theme) {
        const themeMeta = document.querySelector('meta[name="theme-color"]');
        if (themeMeta) {
            themeMeta.setAttribute("content", theme === "dark" ? "#071421" : "#eaf6ff");
        }
    }

    function updateThemeToggle(theme) {
        if (!themeToggle || !themeLabel || !themeIcon) {
            return;
        }

        const nextTheme = theme === "dark" ? "light" : "dark";
        const themeLabels = runtime.themeLabels || {};
        const nextThemeLabel = nextTheme === "dark"
            ? (themeLabels.dark || "Dark")
            : (themeLabels.light || "Light");
        const switchLabel = runtime.themeSwitchLabel || "Switch to {theme} mode";
        themeToggle.setAttribute("aria-pressed", String(theme === "dark"));
        themeToggle.setAttribute("aria-label", switchLabel.replace("{theme}", nextThemeLabel));
        themeLabel.textContent = nextThemeLabel;
        themeIcon.className = theme === "dark"
            ? "bi bi-sun-fill theme-toggle-icon"
            : "bi bi-moon-stars-fill theme-toggle-icon";
    }

    function applyTheme(theme, persist = false) {
        root.setAttribute("data-bs-theme", theme);
        root.setAttribute("data-theme-mode", theme);
        updateThemeMeta(theme);
        updateThemeToggle(theme);
        document.dispatchEvent(new CustomEvent("gb:themechange", { detail: { theme } }));
        if (persist) {
            writeStorage(THEME_KEY, theme);
        }
    }

    function initThemeToggle() {
        if (window.MS_THEME) return; // Shared controls also cover the portfolio and CV.
        applyTheme(getPreferredTheme());

        themeToggle?.addEventListener("click", () => {
            const currentTheme = root.getAttribute("data-theme-mode") === "dark" ? "dark" : "light";
            const nextTheme = currentTheme === "dark" ? "light" : "dark";
            applyTheme(nextTheme, true);
        });

        prefersDark.addEventListener("change", (event) => {
            if (getStoredTheme()) {
                return;
            }
            applyTheme(event.matches ? "dark" : "light");
        });
    }

    function updateScrollState() {
        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrollRatio = scrollHeight > 0 ? (scrollTop / scrollHeight) * 100 : 0;

        if (progressBar) {
            progressBar.style.setProperty("--scroll-progress", `${Math.min(scrollRatio, 100)}%`);
        }

        if (header) {
            header.classList.toggle("scrolled", scrollTop > 12);
        }
    }

    function initScrollUI() {
        let ticking = false;

        const onScroll = () => {
            if (ticking) {
                return;
            }
            ticking = true;
            window.requestAnimationFrame(() => {
                updateScrollState();
                ticking = false;
            });
        };

        updateScrollState();
        window.addEventListener("scroll", onScroll, { passive: true });
        window.addEventListener("resize", onScroll);
    }

    function getActiveTheme() {
        return root.getAttribute("data-theme-mode") === "dark" ? "dark" : "light";
    }

    function setTemporaryShareLabel(trigger, label) {
        if (!trigger) {
            return;
        }

        const copyTarget = trigger.querySelector("span");
        const target = copyTarget || trigger;
        const originalLabel = target.textContent;
        target.textContent = label;
        trigger.classList.add("is-copied");

        window.setTimeout(() => {
            target.textContent = originalLabel;
            trigger.classList.remove("is-copied");
        }, 1800);
    }

    function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }

        return new Promise((resolve, reject) => {
            const helper = document.createElement("textarea");
            helper.value = text;
            helper.setAttribute("readonly", "");
            helper.style.position = "fixed";
            helper.style.opacity = "0";
            helper.style.pointerEvents = "none";
            document.body.appendChild(helper);
            helper.select();

            try {
                const copied = document.execCommand("copy");
                document.body.removeChild(helper);
                if (!copied) {
                    reject(new Error("No se pudo copiar el enlace"));
                    return;
                }
                resolve();
            } catch (error) {
                document.body.removeChild(helper);
                reject(error);
            }
        });
    }

    function initNativeShare() {
        const shareTriggers = document.querySelectorAll("[data-share-native]");
        if (!shareTriggers.length) {
            return;
        }

        shareTriggers.forEach((trigger) => {
            trigger.addEventListener("click", async () => {
                const shareTitle = trigger.dataset.shareTitle || document.title;
                const shareUrl = trigger.dataset.shareUrl || window.location.href;

                if (navigator.share) {
                    try {
                        await navigator.share({ title: shareTitle, url: shareUrl });
                        return;
                    } catch (error) {
                        if (error?.name === "AbortError") {
                            return;
                        }
                    }
                }

                try {
                    await copyText(shareUrl);
                    setTemporaryShareLabel(trigger, "Enlace copiado");
                } catch (error) {
                    setTemporaryShareLabel(trigger, "Copia manual");
                }
            });
        });
    }

    function storedConsentChoice() {
        try {
            const value = JSON.parse(readStorage(COOKIE_KEY) || "null");
            return value?.choice === "accept" || value?.choice === "essential" ? value.choice : null;
        } catch { return null; }
    }

    async function activateOptionalContent() {
        if (optionalContentActivated) return;
        optionalContentActivated = true;
        for (const entry of document.querySelectorAll("script[type='application/json'][data-ms-consent-code]")) {
            let payload;
            try { payload = JSON.parse(entry.textContent || "{}"); } catch { continue; }
            if (typeof payload.html !== "string" || entry.dataset.msActivated === "true") continue;
            entry.dataset.msActivated = "true";
            const template = document.createElement("template");
            template.innerHTML = payload.html;
            const scripts = Array.from(template.content.querySelectorAll("script"));
            entry.before(template.content);
            const slot = entry.closest("[data-ms-consent-slot]");
            if (slot) slot.hidden = false;
            for (const dormant of scripts) {
                const executable = document.createElement("script");
                for (const attribute of dormant.attributes) executable.setAttribute(attribute.name, attribute.value);
                executable.textContent = dormant.textContent;
                if (!dormant.hasAttribute("async")) executable.async = false;
                await new Promise((resolve) => {
                    executable.addEventListener("load", resolve, { once: true });
                    executable.addEventListener("error", resolve, { once: true });
                    dormant.replaceWith(executable);
                    if (!executable.src) resolve();
                });
            }
        }
    }

    function initCookieConsent() {
        const modalElement = document.getElementById("cookie-consent-modal");
        const choice = storedConsentChoice();
        if (choice === "accept") void activateOptionalContent();
        if (!runtime.cookieConsentEnabled || !modalElement || !window.bootstrap?.Modal) {
            return;
        }

        const cookieModal = new window.bootstrap.Modal(modalElement, {
            backdrop: "static",
            keyboard: false
        });

        modalElement.querySelectorAll("[data-cookie-action]").forEach((button) => {
            button.addEventListener("click", () => {
                const choice = button.getAttribute("data-cookie-action") || "essential";
                const accepted = choice === "accept";
                writeStorage(COOKIE_KEY, JSON.stringify({
                    choice: accepted ? "accept" : "essential",
                    savedAt: new Date().toISOString()
                }));
                cookieModal.hide();
                document.dispatchEvent(new CustomEvent("gb:consentchange", { detail: { optional: accepted } }));
                if (accepted) void activateOptionalContent();
                else if (optionalContentActivated) window.location.reload();
            });
        });

        document.querySelectorAll("[data-cookie-settings]").forEach((button) => {
            button.addEventListener("click", () => cookieModal.show());
        });

        if (!choice) window.setTimeout(() => cookieModal.show(), 900);
    }

    function renderRecaptcha(force = false) {
        const recaptchaRoot = document.getElementById("recaptcha-root");
        if (!runtime.recaptchaEnabled || !runtime.recaptchaSiteKey || !recaptchaRoot || !window.grecaptcha?.render) {
            return;
        }

        if (force) {
            recaptchaRoot.innerHTML = "";
            recaptchaWidgetId = null;
        }

        if (recaptchaWidgetId !== null) {
            return;
        }

        recaptchaWidgetId = window.grecaptcha.render(recaptchaRoot, {
            sitekey: runtime.recaptchaSiteKey,
            theme: getActiveTheme()
        });
    }

    function initRecaptchaHelpers() {
        window.GBRecaptcha = {
            render() {
                renderRecaptcha(false);
            },
            rerender() {
                renderRecaptcha(true);
            }
        };

        document.addEventListener("gb:themechange", () => {
            if (document.getElementById("recaptcha-root") && window.grecaptcha?.render) {
                renderRecaptcha(true);
            }
        });
    }

    function initInfinitePosts() {
        const grids = document.querySelectorAll("[data-infinite-grid], #infinite-post-grid");
        if (!grids.length) {
            return;
        }

        body?.classList.add("infinite-scroll-enabled");

        grids.forEach((grid) => {
            const scope = grid.parentElement || document;
            const sentinel = scope.querySelector("[data-infinite-sentinel]") || document.getElementById("infinite-scroll-sentinel");
            const status = scope.querySelector("[data-infinite-status]") || document.getElementById("infinite-scroll-status");
            if (!sentinel) {
                return;
            }

            let loading = false;
            let hasMore = true;
            const endpoint = grid.dataset.endpoint;
            const limit = Number(grid.dataset.limit || 6);
            const excludeId = Number(grid.dataset.excludeId || 0);
            const excludeIds = grid.dataset.excludeIds || "";
            const layout = grid.dataset.layout || "";
            const category = grid.dataset.category || "";
            const excludeCategory = grid.dataset.excludeCategory || "";
            const excludeFeatured = grid.dataset.excludeFeatured || "";
            const searchQuery = grid.dataset.searchQuery || "";

            const setStatus = (mode) => {
                if (!status) {
                    return;
                }
                const loadingLabel = status.dataset.loadingLabel || "Loading more articles...";
                const doneLabel = status.dataset.doneLabel || "You're at the end.";
                status.classList.toggle("is-visible", mode === "loading" || mode === "done");
                status.classList.toggle("is-done", mode === "done");
                const label = status.querySelector("span:last-child");
                if (label) {
                    label.textContent = mode === "done" ? doneLabel : loadingLabel;
                }
            };

            const loadMore = async () => {
                if (loading || !hasMore || !endpoint) {
                    return;
                }
                loading = true;
                setStatus("loading");

                const params = new URLSearchParams({
                    offset: grid.dataset.nextOffset || "0",
                    limit: String(limit),
                    layout
                });

                if (excludeIds) {
                    params.set("exclude_ids", excludeIds);
                } else if (excludeId) {
                    params.set("exclude_id", String(excludeId));
                }

                if (category) {
                    params.set("category", category);
                }
                if (excludeCategory) {
                    params.set("exclude_category", excludeCategory);
                }
                if (excludeFeatured) {
                    params.set("exclude_featured", excludeFeatured);
                }
                if (searchQuery) {
                    params.set("q", searchQuery);
                }

                try {
                    const response = await fetch(`${endpoint}?${params.toString()}`, {
                        headers: { "Accept": "application/json" }
                    });
                    if (!response.ok) {
                        throw new Error("No se pudieron cargar mas posts");
                    }
                    const payload = await response.json();
                    if (payload.html) {
                        const fragment = document.createRange().createContextualFragment(payload.html);
                        grid.appendChild(fragment);
                    }
                    grid.dataset.nextOffset = String(payload.next_offset || Number(grid.dataset.nextOffset || 0));
                    hasMore = Boolean(payload.has_more);
                    setStatus(hasMore ? "idle" : "done");
                } catch (error) {
                    setStatus("done");
                    hasMore = false;
                } finally {
                    loading = false;
                }
            };

            const observer = new IntersectionObserver((entries) => {
                if (entries.some((entry) => entry.isIntersecting)) {
                    loadMore();
                }
            }, { rootMargin: "500px 0px 500px 0px" });

            observer.observe(sentinel);
        });
    }

    function initAccordionGroups() {
        const groups = document.querySelectorAll("[data-accordion-group]");
        if (!groups.length) {
            return;
        }

        groups.forEach((group) => {
            const items = Array.from(group.querySelectorAll("details[data-accordion-item]"));
            if (!items.length) {
                return;
            }

            items.forEach((item) => {
                item.addEventListener("toggle", () => {
                    if (!item.open) {
                        return;
                    }

                    items.forEach((other) => {
                        if (other !== item && other.open) {
                            other.open = false;
                        }
                    });
                });
            });
        });
    }

    function initRevealMotion() {
        if (!body || prefersReducedMotion.matches) {
            return;
        }

        const motionTargets = Array.from(
            document.querySelectorAll(
                [
                    ".landing-cover-content",
                    ".hero-main-panel",
                    ".business-hero-copy",
                    ".business-lead-card",
                    ".business-topic-strip a",
                    ".business-panel",
                    ".hero-side-card",
                    ".signal-strip span",
                    ".featured-story",
                    ".page-hero-panel",
                    ".post-card",
                    ".category-card",
                    ".info-panel",
                    ".category-panel",
                    ".search-panel",
                    ".article-callout",
                    ".article-side-card",
                    ".category-hero-shell",
                    ".cta-banner",
                    ".footer-card",
                    ".admin-card",
                    ".stat-card",
                    ".empty-state",
                    ".ad-slot"
                ].join(",")
            )
        );

        if (!motionTargets.length) {
            return;
        }

        body.classList.add("motion-ready");
        motionTargets.forEach((element, index) => {
            element.classList.add("reveal-ready");
            element.style.setProperty("--reveal-delay", `${Math.min(index % 8, 6) * 70}ms`);
        });

        const markIfNearViewport = (element) => {
            const rect = element.getBoundingClientRect();
            if (rect.bottom < -120) {
                return false;
            }
            if (rect.top > window.innerHeight + 180) {
                return false;
            }
            element.classList.add("is-visible");
            return true;
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }
                entry.target.classList.add("is-visible");
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.15, rootMargin: "0px 0px -40px 0px" });

        motionTargets.forEach((element) => {
            if (!markIfNearViewport(element)) {
                observer.observe(element);
            }
        });

        window.requestAnimationFrame(() => {
            motionTargets.forEach((element) => {
                if (!element.classList.contains("is-visible")) {
                    markIfNearViewport(element);
                }
            });
        });

        window.setTimeout(() => {
            motionTargets.forEach((element) => {
                if (!element.classList.contains("is-visible")) {
                    markIfNearViewport(element);
                }
            });
        }, 420);
    }

    document.addEventListener("DOMContentLoaded", () => {
        initThemeToggle();
        initScrollUI();
        initNativeShare();
        initCookieConsent();
        initRecaptchaHelpers();
        initInfinitePosts();
        initAccordionGroups();
        initRevealMotion();

        if (runtime.recaptchaEnabled && window.grecaptcha?.render) {
            renderRecaptcha(false);
        }
    });
})();

document.addEventListener("DOMContentLoaded", () => {
    const navToggle = document.querySelector("[data-nav-toggle]");
    const nav = document.querySelector("[data-nav]");

    if (navToggle && nav) {
        navToggle.addEventListener("click", () => {
            const isOpen = nav.classList.toggle("is-open");
            navToggle.setAttribute("aria-expanded", String(isOpen));
        });

        nav.querySelectorAll("a").forEach((link) => {
            link.addEventListener("click", () => {
                nav.classList.remove("is-open");
                navToggle.setAttribute("aria-expanded", "false");
            });
        });
    }

    const sectionLinks = nav
        ? Array.from(nav.querySelectorAll('a[href^="#"]'))
        : [];
    const sectionTargets = sectionLinks
        .map((link) => ({
            link,
            target: document.querySelector(link.getAttribute("href")),
        }))
        .filter((item) => item.target && item.target.matches("[data-slide]"));
    const prevSectionButton = document.querySelector("[data-section-prev]");
    const nextSectionButton = document.querySelector("[data-section-next]");
    let currentSectionIndex = 0;

    function updateSectionControls() {
        if (!sectionTargets.length) {
            return;
        }

        sectionTargets.forEach((item, index) => {
            const isActive = index === currentSectionIndex;
            item.target.classList.toggle("is-active-slide", isActive);
            item.target.setAttribute("aria-hidden", String(!isActive));
            item.link.classList.toggle("is-active", isActive);
            if (isActive) {
                item.link.setAttribute("aria-current", "true");
                item.target.querySelectorAll("[data-reveal]").forEach((node) => {
                    node.classList.add("is-visible");
                });
            } else {
                item.link.removeAttribute("aria-current");
            }
        });

        if (prevSectionButton) {
            prevSectionButton.disabled = currentSectionIndex === 0;
        }
        if (nextSectionButton) {
            nextSectionButton.disabled = currentSectionIndex === sectionTargets.length - 1;
        }
    }

    function showSlide(index, updateHash = true) {
        if (!sectionTargets.length) {
            return;
        }

        const nextIndex = Math.max(0, Math.min(index, sectionTargets.length - 1));
        currentSectionIndex = nextIndex;
        updateSectionControls();
        sectionTargets[nextIndex].target.scrollTop = 0;
        sectionTargets[nextIndex].target.scrollLeft = 0;
        window.scrollTo(0, 0);

        if (updateHash) {
            const id = sectionTargets[nextIndex].target.id;
            history.replaceState(null, "", `#${id}`);
        }
    }

    if (prevSectionButton && nextSectionButton && sectionTargets.length) {
        document.documentElement.classList.add("is-slide-deck");
        document.body.classList.add("is-slide-deck");

        const initialHash = window.location.hash;
        const initialIndex = sectionTargets.findIndex((item) => `#${item.target.id}` === initialHash);
        currentSectionIndex = initialIndex >= 0 ? initialIndex : 0;

        sectionTargets.forEach((item, index) => {
            item.link.addEventListener("click", (event) => {
                event.preventDefault();
                showSlide(index);
                if (nav && navToggle) {
                    nav.classList.remove("is-open");
                    navToggle.setAttribute("aria-expanded", "false");
                }
            });
        });

        prevSectionButton.addEventListener("click", () => {
            showSlide(currentSectionIndex - 1);
        });
        nextSectionButton.addEventListener("click", () => {
            showSlide(currentSectionIndex + 1);
        });

        window.addEventListener("keydown", (event) => {
            if (
                document.body.classList.contains("is-portfolio-modal-open") ||
                document.body.classList.contains("is-experience-modal-open")
            ) {
                return;
            }

            const tagName = document.activeElement ? document.activeElement.tagName : "";
            if (["INPUT", "TEXTAREA", "SELECT"].includes(tagName)) {
                return;
            }

            if (event.key === "ArrowRight" || event.key === "PageDown") {
                event.preventDefault();
                showSlide(currentSectionIndex + 1);
            }
            if (event.key === "ArrowLeft" || event.key === "PageUp") {
                event.preventDefault();
                showSlide(currentSectionIndex - 1);
            }
        });

        window.addEventListener("hashchange", () => {
            const hashIndex = sectionTargets.findIndex((item) => `#${item.target.id}` === window.location.hash);
            if (hashIndex >= 0) {
                showSlide(hashIndex, false);
            }
        });

        showSlide(currentSectionIndex, initialIndex >= 0);
    }

    const experienceTabs = Array.from(document.querySelectorAll("[data-experience-tab]"));
    const experiencePanels = Array.from(document.querySelectorAll("[data-experience-panel]"));

    function setExperienceTab(tabId) {
        experienceTabs.forEach((tab) => {
            const isActive = tab.dataset.experienceTab === tabId;
            tab.classList.toggle("is-active", isActive);
            tab.setAttribute("aria-selected", String(isActive));
        });

        experiencePanels.forEach((panel) => {
            const isActive = panel.dataset.experiencePanel === tabId;
            panel.classList.toggle("is-active", isActive);
            panel.toggleAttribute("hidden", !isActive);

            if (isActive) {
                panel.querySelectorAll("[data-reveal]").forEach((node) => {
                    node.classList.add("is-visible");
                });
            }
        });
    }

    if (experienceTabs.length && experiencePanels.length) {
        experiencePanels.forEach((panel) => {
            panel.toggleAttribute("hidden", !panel.classList.contains("is-active"));
        });

        experienceTabs.forEach((tab) => {
            tab.addEventListener("click", () => {
                setExperienceTab(tab.dataset.experienceTab || "");
            });
        });
    }

    const experienceModal = document.querySelector("[data-experience-modal]");
    const experienceCards = Array.from(document.querySelectorAll("[data-experience-card]"));
    let lastExperienceTrigger = null;

    if (experienceModal && experienceCards.length) {
        const modalDialog = experienceModal.querySelector(".jt-experience-modal-dialog");
        const modalType = experienceModal.querySelector("[data-experience-modal-type]");
        const modalTitle = experienceModal.querySelector("[data-experience-modal-title]");
        const modalCompany = experienceModal.querySelector("[data-experience-modal-company]");
        const modalPeriod = experienceModal.querySelector("[data-experience-modal-period]");
        const modalSummary = experienceModal.querySelector("[data-experience-modal-summary]");
        const modalObjective = experienceModal.querySelector("[data-experience-modal-objective]");
        const modalIntro = experienceModal.querySelector("[data-experience-modal-intro]");
        const modalFunctions = experienceModal.querySelector("[data-experience-modal-functions]");
        const modalWins = experienceModal.querySelector("[data-experience-modal-wins]");

        function setExperienceText(node, value) {
            if (node) {
                node.textContent = value || "";
            }
        }

        function parseDataList(value) {
            try {
                const parsed = JSON.parse(value || "[]");
                return Array.isArray(parsed) ? parsed : [];
            } catch (error) {
                return [];
            }
        }

        function renderList(node, items) {
            if (!node) {
                return;
            }

            node.innerHTML = "";
            items.forEach((item) => {
                const li = document.createElement("li");
                li.textContent = item;
                node.appendChild(li);
            });
        }

        function openExperienceCard(card) {
            lastExperienceTrigger = card;
            setExperienceText(modalType, card.dataset.type);
            setExperienceText(modalTitle, card.dataset.title);
            setExperienceText(modalCompany, card.dataset.company);
            setExperienceText(modalPeriod, card.dataset.period);
            setExperienceText(modalSummary, card.dataset.summary);
            setExperienceText(modalObjective, card.dataset.objective);
            setExperienceText(modalIntro, card.dataset.intro);
            renderList(modalFunctions, parseDataList(card.dataset.functions));
            renderList(modalWins, parseDataList(card.dataset.wins));

            experienceModal.classList.add("is-open");
            experienceModal.setAttribute("aria-hidden", "false");
            document.body.classList.add("is-experience-modal-open");
            if (modalDialog) {
                modalDialog.focus({ preventScroll: true });
            }
        }

        function closeExperienceModal() {
            experienceModal.classList.remove("is-open");
            experienceModal.setAttribute("aria-hidden", "true");
            document.body.classList.remove("is-experience-modal-open");

            if (lastExperienceTrigger) {
                lastExperienceTrigger.focus({ preventScroll: true });
            }
        }

        experienceCards.forEach((card) => {
            card.addEventListener("click", () => openExperienceCard(card));
            card.addEventListener("keydown", (event) => {
                if (event.key === "Enter" || event.key === " ") {
                    event.preventDefault();
                    openExperienceCard(card);
                }
            });
        });

        experienceModal.querySelectorAll("[data-experience-close]").forEach((node) => {
            node.addEventListener("click", closeExperienceModal);
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape" && experienceModal.classList.contains("is-open")) {
                event.preventDefault();
                closeExperienceModal();
            }
        });
    }

    const portfolioModal = document.querySelector("[data-portfolio-modal]");
    const portfolioCards = Array.from(document.querySelectorAll("[data-portfolio-card]"));
    let lastPortfolioTrigger = null;

    if (portfolioModal && portfolioCards.length) {
        const modalDialog = portfolioModal.querySelector(".jt-portfolio-modal-dialog");
        const modalImage = portfolioModal.querySelector("[data-portfolio-modal-image]");
        const modalCategory = portfolioModal.querySelector("[data-portfolio-modal-category]");
        const modalFocus = portfolioModal.querySelector("[data-portfolio-modal-focus]");
        const modalTitle = portfolioModal.querySelector("[data-portfolio-modal-title]");
        const modalCopy = portfolioModal.querySelector("[data-portfolio-modal-copy]");
        const modalRole = portfolioModal.querySelector("[data-portfolio-modal-role]");
        const modalDeliverables = portfolioModal.querySelector("[data-portfolio-modal-deliverables]");
        const modalOutcome = portfolioModal.querySelector("[data-portfolio-modal-outcome]");
        const modalTags = portfolioModal.querySelector("[data-portfolio-modal-tags]");
        const modalMeta = portfolioModal.querySelector("[data-portfolio-modal-meta]");

        function setText(node, value) {
            if (node) {
                node.textContent = value || "";
            }
        }

        function openPortfolioCard(card) {
            lastPortfolioTrigger = card;

            if (modalImage) {
                modalImage.src = card.dataset.image || "";
                modalImage.alt = card.dataset.title || "";
            }

            setText(modalCategory, card.dataset.category);
            setText(modalFocus, card.dataset.focus);
            setText(modalTitle, card.dataset.title);
            setText(modalCopy, card.dataset.copy);
            setText(modalRole, card.dataset.role);
            setText(modalDeliverables, card.dataset.deliverables);
            setText(modalOutcome, card.dataset.outcome);
            setText(modalMeta, card.dataset.meta);

            if (modalTags) {
                modalTags.innerHTML = "";
                (card.dataset.tags || "")
                    .split(",")
                    .map((tag) => tag.trim())
                    .filter(Boolean)
                    .forEach((tag) => {
                        const node = document.createElement("span");
                        node.textContent = tag;
                        modalTags.appendChild(node);
                    });
            }

            portfolioModal.classList.add("is-open");
            portfolioModal.setAttribute("aria-hidden", "false");
            document.body.classList.add("is-portfolio-modal-open");
            if (modalDialog) {
                modalDialog.focus({ preventScroll: true });
            }
        }

        function closePortfolioModal() {
            portfolioModal.classList.remove("is-open");
            portfolioModal.setAttribute("aria-hidden", "true");
            document.body.classList.remove("is-portfolio-modal-open");

            if (modalImage) {
                modalImage.removeAttribute("src");
                modalImage.removeAttribute("alt");
            }

            if (lastPortfolioTrigger) {
                lastPortfolioTrigger.focus({ preventScroll: true });
            }
        }

        portfolioCards.forEach((card) => {
            card.addEventListener("click", () => openPortfolioCard(card));
            card.addEventListener("keydown", (event) => {
                if (event.key === "Enter" || event.key === " ") {
                    event.preventDefault();
                    openPortfolioCard(card);
                }
            });
        });

        portfolioModal.querySelectorAll("[data-portfolio-close]").forEach((node) => {
            node.addEventListener("click", closePortfolioModal);
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape" && portfolioModal.classList.contains("is-open")) {
                event.preventDefault();
                closePortfolioModal();
            }
        });
    }

    const revealNodes = Array.from(document.querySelectorAll("[data-reveal]"));

    if (!revealNodes.length) {
        return;
    }

    if (!("IntersectionObserver" in window)) {
        revealNodes.forEach((node) => node.classList.add("is-visible"));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            entry.target.classList.add("is-visible");
            observer.unobserve(entry.target);
        });
    }, {
        threshold: 0.14,
        rootMargin: "0px 0px -10% 0px",
    });

    revealNodes.forEach((node) => observer.observe(node));
});

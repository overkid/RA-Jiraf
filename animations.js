(() => {
  const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const body = document.body;
  const isAdminPage = body.classList.contains("admin-page");
  const hasHeroBackdrop = Boolean(document.querySelector(".hero, .catalog-header"));
  const heroStageDelayMs = !prefersReducedMotion && hasHeroBackdrop ? 500 : 0;
  const pageTransitionDurationMs = 250;
  let isPageTransitionActive = false;

  const preparePageFadeIn = () => {
    body.classList.remove("is-page-transition");
    body.classList.remove("is-page-ready");

    if (prefersReducedMotion) {
      body.classList.add("is-page-ready");
      return;
    }

    window.requestAnimationFrame(() => {
      window.requestAnimationFrame(() => {
        body.classList.add("is-page-ready");
      });
    });
  };

  const canAnimateNavigation = (link, event) => {
    if (!link || !link.hasAttribute("href")) return false;
    if (event.defaultPrevented || event.button !== 0) return false;
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return false;
    if (link.hasAttribute("download")) return false;
    if (link.closest("[data-no-transition]")) return false;

    const targetAttr = (link.getAttribute("target") || "").toLowerCase();
    if (targetAttr && targetAttr !== "_self") return false;

    const rawHref = link.getAttribute("href") || "";
    if (!rawHref || rawHref.startsWith("#")) return false;
    if (/^(mailto:|tel:|javascript:)/i.test(rawHref)) return false;

    let targetUrl;
    try {
      targetUrl = new URL(rawHref, window.location.href);
    } catch (error) {
      return false;
    }

    if (targetUrl.origin !== window.location.origin) return false;

    const samePathAndSearch =
      targetUrl.pathname === window.location.pathname &&
      targetUrl.search === window.location.search;

    if (samePathAndSearch && targetUrl.hash) return false;

    const sameDocument =
      targetUrl.pathname === window.location.pathname &&
      targetUrl.search === window.location.search &&
      targetUrl.hash === window.location.hash;

    if (sameDocument) return false;
    return true;
  };

  const navigateWithTransition = (targetUrl) => {
    if (isPageTransitionActive) return;

    isPageTransitionActive = true;
    body.classList.add("is-page-transition");
    body.classList.remove("is-page-ready");

    window.setTimeout(() => {
      window.location.assign(targetUrl);
    }, pageTransitionDurationMs);
  };

  const setupPageTransitions = () => {
    if (prefersReducedMotion) {
      return;
    }

    document.addEventListener("click", (event) => {
      const link = event.target.closest("a[href]");
      if (!canAnimateNavigation(link, event)) return;

      const targetUrl = new URL(link.getAttribute("href"), window.location.href);
      event.preventDefault();
      navigateWithTransition(targetUrl.href);
    });

    window.addEventListener("pageshow", (event) => {
      if (event.persisted) {
        isPageTransitionActive = false;
        preparePageFadeIn();
      }
    });

    window.addEventListener("pagehide", () => {
      isPageTransitionActive = false;
    });
  };

  const setupHeroWordReveal = () => {
    const heroTitles = document.querySelectorAll(".hero-content h1, .catalog-hero h1");

    heroTitles.forEach((title) => {
      if (title.dataset.wordsReady === "true") {
        return;
      }

      const words = title.textContent.trim().split(/\s+/);
      title.textContent = "";
      title.classList.add("hero-title-words");

      words.forEach((word, index) => {
        const wordSpan = document.createElement("span");
        wordSpan.className = "hero-word";
        wordSpan.style.setProperty("--word-delay", `${0.08 + Math.min(index * 0.06, 0.42)}s`);
        wordSpan.textContent = word;
        title.appendChild(wordSpan);

        if (index < words.length - 1) {
          const spacer = document.createElement("span");
          spacer.className = "hero-word-space";
          spacer.textContent = "\u00A0";
          title.appendChild(spacer);
        }
      });

      title.dataset.wordsReady = "true";

      if (prefersReducedMotion) {
        title.classList.add("is-visible");
        return;
      }

      window.setTimeout(() => {
        title.classList.add("is-visible");
      }, 70 + heroStageDelayMs);
    });
  };


  const setupHeroContentReveal = () => {
    const heroBlocks = document.querySelectorAll(".hero-content, .catalog-hero");

    heroBlocks.forEach((block) => {
      const title = block.querySelector("h1");
      const subtitle = block.querySelector("p");
      const button = block.querySelector(".btn");
      const wordCount = title ? title.querySelectorAll(".hero-word").length : 0;
      const lastWordDelay = wordCount > 0 ? 0.08 + Math.min((wordCount - 1) * 0.06, 0.42) : 0.08;
      const titleRevealDuration = 0.55;
      const titleFinishMs = (lastWordDelay + titleRevealDuration) * 1000;

      if (subtitle) {
        subtitle.setAttribute("data-hero-reveal", "");
      }

      if (button) {
        button.setAttribute("data-hero-reveal", "");
      }

      if (prefersReducedMotion) {
        if (subtitle) subtitle.classList.add("is-visible");
        if (button) button.classList.add("is-visible");
        return;
      }

      const subtitleDelayMs = Math.max(120, titleFinishMs - 220);
      const buttonDelayMs = Math.max(220, titleFinishMs - 80);

      window.setTimeout(() => {
        if (subtitle) subtitle.classList.add("is-visible");
      }, subtitleDelayMs + heroStageDelayMs);

      window.setTimeout(() => {
        if (button) button.classList.add("is-visible");
      }, buttonDelayMs + heroStageDelayMs);
    });
  };

  const setRevealDelays = (selector, baseDelay = 0.06, maxDelay = 0.3) => {
    const elements = document.querySelectorAll(selector);

    elements.forEach((element, index) => {
      const delay = Math.min(index * baseDelay, maxDelay);
      element.style.setProperty("--reveal-delay", `${delay}s`);
    });
  };

  const setupRevealAnimations = () => {
    const isConveyorPortfolioCard = (element) =>
      element.classList.contains("portfolio-card") && Boolean(element.closest("[data-portfolio-track]"));

    const revealElements = Array.from(
      document.querySelectorAll(
        ".section, .cards > article, .feature, .catalog-tab, .service-tile, .portfolio-card, .footer-cta"
      )
    ).filter((element) => !element.classList.contains("catalog-header") && !isConveyorPortfolioCard(element));

    revealElements.forEach((element) => {
      element.setAttribute("data-reveal", "");
    });

    const horizontalElements = Array.from(document.querySelectorAll(
      ".feature, .catalog-tab, .service-tile, .portfolio-card, .card"
    )).filter((element) => !isConveyorPortfolioCard(element));

    horizontalElements.forEach((element) => {
      element.classList.add("reveal-horizontal");
    });

    const catalogTiles = document.querySelectorAll(".service-tile");
    catalogTiles.forEach((tile) => {
      tile.setAttribute("data-reveal-manual", "");
    });

    setRevealDelays(".cards > article", 0.08, 0.34);
    setRevealDelays(".feature", 0.09, 0.35);
    setRevealDelays(".catalog-tab", 0.08, 0.26);
    setRevealDelays(".service-tile", 0.07, 0.28);
    setRevealDelays(".portfolio-card", 0.09, 0.36);
    setRevealDelays(".card", 0.08, 0.32);

    const targets = Array.from(document.querySelectorAll("[data-reveal]"))
      .filter((element) => !element.hasAttribute("data-reveal-manual"));

    if (prefersReducedMotion || !("IntersectionObserver" in window)) {
      targets.forEach((element) => {
        element.classList.add("is-visible");
      });
      return;
    }

    const revealObserver = new IntersectionObserver(
      (entries, observer) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) {
            return;
          }

          const element = entry.target;
          const revealDelaySeconds = Number.parseFloat(element.style.getPropertyValue("--reveal-delay")) || 0;
          const revealDelayMs = Math.max(40, revealDelaySeconds * 1000);

          window.setTimeout(() => {
            element.classList.add("is-visible");
          }, revealDelayMs);

          observer.unobserve(element);
        });
      },
      {
        threshold: 0.08,
        rootMargin: "0px 0px -6% 0px"
      }
    );

    window.requestAnimationFrame(() => {
      window.requestAnimationFrame(() => {
        targets.forEach((element) => {
          revealObserver.observe(element);
        });
      });
    });
  };

  const setupCatalogConsistency = () => {
    const categoryTabs = document.querySelectorAll("[data-category-tab]");
    const categoryGroups = document.querySelectorAll("[data-category]");
    const catalogRevealTimers = [];

    if (!categoryTabs.length || !categoryGroups.length) {
      return;
    }

    const clearCatalogRevealTimers = () => {
      while (catalogRevealTimers.length) {
        const timerId = catalogRevealTimers.pop();
        window.clearTimeout(timerId);
      }
    };

    const animateGroupTiles = (group) => {
      const tiles = Array.from(group.querySelectorAll(".service-tile"));
      if (!tiles.length) return;
      clearCatalogRevealTimers();

      tiles.forEach((tile) => {
        tile.style.transition = "none";
        tile.style.removeProperty("--reveal-delay");
        tile.classList.remove("is-visible");
      });

      // Force layout so the hidden state is applied before reveal.
      void group.offsetWidth;

      if (prefersReducedMotion) {
        tiles.forEach((tile) => {
          tile.style.removeProperty("transition");
          tile.classList.add("is-visible");
        });
        return;
      }

      tiles.forEach((tile, index) => {
        tile.style.removeProperty("transition");
        const delayMs = Math.min(index * 80, 320);
        const timerId = window.setTimeout(() => {
          tile.classList.add("is-visible");
        }, delayMs);
        catalogRevealTimers.push(timerId);
      });
    };

    const animateSelectedCategory = (selectedCategory) => {
      if (!selectedCategory) return;

      window.requestAnimationFrame(() => {
        const activeGroup = Array.from(categoryGroups).find(
          (group) => group.dataset.category === selectedCategory && !group.hidden
        );

        if (activeGroup) {
          animateGroupTiles(activeGroup);
        }
      });
    };

    window.addEventListener("catalog:category-changed", (event) => {
      const selectedCategory = event?.detail?.category;
      animateSelectedCategory(selectedCategory);
    });

    const initialGroup = Array.from(categoryGroups).find((group) => !group.hidden);
    if (initialGroup) {
      animateGroupTiles(initialGroup);
    }
  };

  if (!prefersReducedMotion && hasHeroBackdrop) {
    body.classList.add("has-hero-intro");
  }

  preparePageFadeIn();
  setupPageTransitions();

  if (!isAdminPage) {
    setupHeroWordReveal();
    setupHeroContentReveal();
    setupRevealAnimations();
    setupCatalogConsistency();
  }
})();

(() => {
  const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const body = document.body;

  const preparePageFadeIn = () => {
    body.classList.add("is-page-ready");
  };

  const setupPageTransitions = () => {
    if (prefersReducedMotion) {
      return;
    }

    const internalLinks = document.querySelectorAll('a[href$=".html"], a[href="index.html"], a[href="services.html"]');

    internalLinks.forEach((link) => {
      link.addEventListener("click", (event) => {
        const href = link.getAttribute("href");

        if (!href || href.startsWith("#") || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
          return;
        }

        const targetUrl = new URL(href, window.location.href);

        if (targetUrl.origin !== window.location.origin || targetUrl.pathname === window.location.pathname) {
          return;
        }

        event.preventDefault();
        body.classList.add("is-page-transition");

        window.setTimeout(() => {
          window.location.href = targetUrl.href;
        }, 260);
      });
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
      }, 70);
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
      }, subtitleDelayMs);

      window.setTimeout(() => {
        if (button) button.classList.add("is-visible");
      }, buttonDelayMs);
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
    const revealElements = Array.from(
      document.querySelectorAll(
        ".section, .cards > article, .feature, .catalog-tab, .service-tile, .portfolio-card, .footer-cta"
      )
    ).filter((element) => !element.classList.contains("catalog-header"));

    revealElements.forEach((element) => {
      element.setAttribute("data-reveal", "");
    });

    const horizontalElements = document.querySelectorAll(
      ".feature, .catalog-tab, .service-tile, .portfolio-card, .card"
    );

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

    if (!categoryTabs.length || !categoryGroups.length) {
      return;
    }

    const animateGroupTiles = (group) => {
      const tiles = group.querySelectorAll(".service-tile");

      tiles.forEach((tile, index) => {
        tile.style.setProperty("--reveal-delay", `${Math.min(index * 0.08, 0.32)}s`);
        tile.classList.remove("is-visible");
      });

      if (prefersReducedMotion) {
        tiles.forEach((tile) => tile.classList.add("is-visible"));
        return;
      }

      window.requestAnimationFrame(() => {
        window.requestAnimationFrame(() => {
          tiles.forEach((tile) => tile.classList.add("is-visible"));
        });
      });
    };

    categoryTabs.forEach((tab) => {
      tab.addEventListener("click", () => {
        const selectedCategory = tab.dataset.categoryTab;
        const activeGroup = Array.from(categoryGroups).find((group) => group.dataset.category === selectedCategory && !group.hidden);

        if (activeGroup) {
          animateGroupTiles(activeGroup);
        }
      });
    });

    const initialGroup = Array.from(categoryGroups).find((group) => !group.hidden);
    if (initialGroup) {
      animateGroupTiles(initialGroup);
    }
  };

  setupHeroWordReveal();
  setupHeroContentReveal();
  preparePageFadeIn();
  setupPageTransitions();
  setupRevealAnimations();
  setupCatalogConsistency();
})();

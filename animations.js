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

  const setRevealDelays = (selector, baseDelay = 0.06, maxDelay = 0.3) => {
    const elements = document.querySelectorAll(selector);

    elements.forEach((element, index) => {
      const delay = Math.min(index * baseDelay, maxDelay);
      element.style.setProperty("--reveal-delay", `${delay}s`);
    });
  };

  const setupRevealAnimations = () => {
    const revealElements = document.querySelectorAll(
      ".section, .hero-content, .cards > article, .feature, .catalog-tab, .service-tile, .footer-cta"
    );

    revealElements.forEach((element) => {
      element.setAttribute("data-reveal", "");
    });

    const horizontalElements = document.querySelectorAll(
      ".feature, .catalog-tab, .service-tile"
    );

    horizontalElements.forEach((element) => {
      element.classList.add("reveal-horizontal");
    });

    setRevealDelays(".cards > article", 0.08, 0.34);
    setRevealDelays(".feature", 0.09, 0.35);
    setRevealDelays(".catalog-tab", 0.08, 0.26);
    setRevealDelays(".service-tile", 0.07, 0.28);

    const targets = document.querySelectorAll("[data-reveal]");

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

          entry.target.classList.add("is-visible");
          observer.unobserve(entry.target);
        });
      },
      {
        threshold: 0.12,
        rootMargin: "0px 0px -8% 0px"
      }
    );

    targets.forEach((element) => {
      revealObserver.observe(element);
    });
  };

  preparePageFadeIn();
  setupPageTransitions();
  setupRevealAnimations();
})();

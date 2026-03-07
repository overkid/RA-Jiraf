const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const topNav = document.querySelector('.top-nav');
const interactiveCards = document.querySelectorAll('.card, .portfolio-card, .feature');
const revealTargets = document.querySelectorAll(
  '.hero-content > *, .section h2, .section-subtitle, .card, .portfolio-card, .feature, .footer-cta > .container > *, .footer-meta > *'
);

if (topNav) {
  const updateNavState = () => {
    topNav.classList.toggle('is-scrolled', window.scrollY > 16);
  };

  updateNavState();
  window.addEventListener('scroll', updateNavState, { passive: true });
}

if (!prefersReducedMotion) {
  revealTargets.forEach((element, index) => {
    element.classList.add('reveal');
    element.style.setProperty('--reveal-delay', `${(index % 4) * 90}ms`);
  });

  const revealObserver = new IntersectionObserver(
    (entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) {
          return;
        }

        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      });
    },
    {
      threshold: 0.12,
      rootMargin: '0px 0px -10% 0px'
    }
  );

  revealTargets.forEach((element) => revealObserver.observe(element));
}

interactiveCards.forEach((card) => {
  card.addEventListener('pointermove', (event) => {
    if (prefersReducedMotion) {
      return;
    }

    const rect = card.getBoundingClientRect();
    const offsetX = (event.clientX - rect.left) / rect.width - 0.5;
    const offsetY = (event.clientY - rect.top) / rect.height - 0.5;

    card.style.transform = `translate3d(${offsetX * 6}px, ${offsetY * 6}px, 0)`;
  });

  card.addEventListener('pointerleave', () => {
    card.style.transform = '';
  });
});

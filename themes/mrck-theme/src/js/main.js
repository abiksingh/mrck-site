import '../scss/main.scss';

import Lenis from 'lenis';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { initArchive } from './archive.js';

gsap.registerPlugin(ScrollTrigger);

// Signal JS availability (no-JS stays fully usable).
document.documentElement.classList.add('js');

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/** Smooth inertia scrolling — the foundational "Readymag" feel. */
function initSmoothScroll() {
  if (prefersReducedMotion) return;
  const lenis = new Lenis({ duration: 1.1, easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)) });
  lenis.on('scroll', ScrollTrigger.update);
  gsap.ticker.add((time) => lenis.raf(time * 1000));
  gsap.ticker.lagSmoothing(0);
}

/** Reveal elements on scroll. Idempotent — safe to call on freshly injected nodes. */
function reveal(scope = document) {
  const targets = gsap.utils.toArray(scope.querySelectorAll('[data-anim="reveal"]'));
  targets.forEach((el) => {
    if (el.__revealed) return;
    el.__revealed = true;
    gsap.from(el, {
      opacity: 0,
      y: prefersReducedMotion ? 0 : 36,
      duration: prefersReducedMotion ? 0 : 0.9,
      ease: 'power3.out',
      scrollTrigger: { trigger: el, start: 'top 92%', once: true },
    });
  });
}

// Expose a tiny API for progressively-enhanced modules (e.g. the archive grid).
window.MRCK = { reveal };

// TODO: Swup page transitions (re-run reveal() on each page view).

initSmoothScroll();
reveal();
initArchive();

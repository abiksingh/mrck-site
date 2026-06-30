import '../scss/main.scss';

import Lenis from 'lenis';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Swup from 'swup';
import { initArchive } from './archive.js';

gsap.registerPlugin(ScrollTrigger);

// JS available (no-JS stays fully usable).
document.documentElement.classList.add('js');

const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
let lenis = null;

/** Smooth inertia scrolling — the foundational "Readymag" feel. */
function initSmoothScroll() {
  if (reduce) return;
  lenis = new Lenis({ duration: 1.1, easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)) });
  lenis.on('scroll', ScrollTrigger.update);
  gsap.ticker.add((t) => lenis.raf(t * 1000));
  gsap.ticker.lagSmoothing(0);
}

/** Reveal-on-scroll + subtle parallax. Idempotent — safe on freshly injected nodes. */
function reveal(scope = document) {
  gsap.utils.toArray(scope.querySelectorAll('[data-anim="reveal"]')).forEach((el) => {
    if (el.__revealed) return;
    el.__revealed = true;
    gsap.from(el, {
      opacity: 0,
      y: reduce ? 0 : 40,
      duration: reduce ? 0 : 0.95,
      ease: 'power3.out',
      scrollTrigger: { trigger: el, start: 'top 90%', once: true },
    });
  });

  if (reduce) return;
  gsap.utils.toArray(scope.querySelectorAll('[data-parallax]')).forEach((el) => {
    if (el.__parallax) return;
    el.__parallax = true;
    gsap.to(el, {
      yPercent: -6,
      ease: 'none',
      scrollTrigger: { trigger: el.parentElement || el, start: 'top bottom', end: 'bottom top', scrub: true },
    });
  });
}

window.MRCK = { reveal };

/** Re-run after each Swup page swap: clean stale triggers, re-bind, re-measure. */
function onView() {
  ScrollTrigger.getAll().forEach((t) => {
    if (!t.trigger || !document.contains(t.trigger)) t.kill();
  });
  reveal();
  initArchive();
  ScrollTrigger.refresh();
}

/** Seamless server-rendered page transitions. */
function initSwup() {
  const swup = new Swup({ containers: ['#main'], animationSelector: '[class*="transition-"]' });
  swup.hooks.on('visit:start', () => { if (lenis) lenis.scrollTo(0, { immediate: true }); window.scrollTo(0, 0); });
  swup.hooks.on('page:view', onView);
}

initSmoothScroll();
reveal();
initArchive();
initSwup();

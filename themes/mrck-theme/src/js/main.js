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

/** Wrap the leading year in biography chronology paragraphs for timeline styling. */
function enhanceBiography(scope = document) {
  scope.querySelectorAll('.chapitre__intro p').forEach((p) => {
    if (p.__yeared) return;
    const m = p.textContent.match(/^(\d{4}(?:[-–]\d{2,4})?)\s+([\s\S]*)/);
    if (!m) return;
    p.__yeared = true;
    p.classList.add('has-year');
    p.textContent = '';
    const year = document.createElement('span');
    year.className = 'chapitre__year';
    year.textContent = m[1];
    const event = document.createElement('span');
    event.className = 'chapitre__event';
    event.textContent = m[2];
    p.append(year, event);
  });
}

window.MRCK = { reveal };

/** Mobile nav toggle. The header persists across Swup swaps, so bind once. */
function initNav() {
  const header = document.querySelector('.site-header');
  const toggle = document.querySelector('.site-header__toggle');
  if (!header || !toggle) return;
  toggle.addEventListener('click', () => {
    const open = header.classList.toggle('is-nav-open');
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
  header.querySelectorAll('.site-nav a').forEach((a) =>
    a.addEventListener('click', () => {
      header.classList.remove('is-nav-open');
      toggle.setAttribute('aria-expanded', 'false');
    })
  );
}

/** Re-run after each Swup page swap: clean stale triggers, re-bind, re-measure. */
function onView() {
  ScrollTrigger.getAll().forEach((t) => {
    if (!t.trigger || !document.contains(t.trigger)) t.kill();
  });
  reveal();
  enhanceBiography();
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
enhanceBiography();
initArchive();
initNav();
initSwup();

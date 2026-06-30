// Live archive filtering — progressive enhancement over the server-rendered grid.
// Reads the same filter form, queries /wp-json/mrck/v1/oeuvres, swaps the grid,
// keeps the URL in sync (shareable / back-button), and supports "load more".

const ENDPOINT = '/wp-json/mrck/v1/oeuvres';
const PER_PAGE = 24;

const escapeHtml = (s) =>
  String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

function cardHTML(it) {
  const img = it.image
    ? `<img class="card__img" src="${it.image.src}"${it.image.srcset ? ` srcset="${it.image.srcset}"` : ''} alt="${escapeHtml(it.image.alt || it.title)}" loading="lazy">`
    : '';
  return `<li class="card" data-anim="reveal"><a class="card__link" href="${it.permalink}">
      <span class="card__media">${img}</span>
      <span class="card__title">${escapeHtml(it.title)}</span>
      ${it.year ? `<span class="card__year">${escapeHtml(it.year)}</span>` : ''}
    </a></li>`;
}

export function initArchive() {
  const form = document.querySelector('[data-mrck-filters]');
  let grid = document.querySelector('[data-mrck-grid]');
  const countEl = document.querySelector('[data-mrck-count]');
  const moreBtn = document.querySelector('[data-mrck-loadmore]');
  if (!form || !grid) return;

  let page = 1;
  let pages = moreBtn ? parseInt(moreBtn.dataset.pages || '1', 10) : 1;
  let busy = false;

  function readParams() {
    const fd = new FormData(form);
    const p = new URLSearchParams();
    const tech = fd.getAll('technique[]').filter(Boolean);
    if (tech.length) p.set('technique', tech.join(','));
    for (const k of ['serie', 'collection', 'search', 'orderby']) {
      const v = (fd.get(k) || '').toString().trim();
      if (v) p.set(k, v);
    }
    return p;
  }

  async function load(append) {
    if (busy) return;
    busy = true;
    form.setAttribute('aria-busy', 'true');
    const p = readParams();
    page = append ? page + 1 : 1;
    p.set('page', String(page));
    p.set('per_page', String(PER_PAGE));
    try {
      const res = await fetch(`${ENDPOINT}?${p.toString()}`, { headers: { Accept: 'application/json' } });
      const data = await res.json();
      pages = data.pages || 1;

      // Ensure we have a <ul> grid even if the no-result <p> was rendered.
      if (grid.tagName !== 'UL') {
        const ul = document.createElement('ul');
        ul.className = 'grid';
        ul.id = 'oeuvre-grid';
        ul.setAttribute('data-mrck-grid', '');
        grid.replaceWith(ul);
        grid = ul;
      }

      const html = (data.items || []).map(cardHTML).join('');
      if (append) grid.insertAdjacentHTML('beforeend', html);
      else grid.innerHTML = html || '';

      if (countEl) countEl.textContent = `${data.total ?? 0} ${(data.total ?? 0) > 1 ? 'œuvres' : 'œuvre'}`;
      if (moreBtn) moreBtn.hidden = page >= pages;
      if (window.MRCK && typeof window.MRCK.reveal === 'function') window.MRCK.reveal(grid);
    } catch (e) {
      /* keep the server-rendered grid on failure */
    } finally {
      busy = false;
      form.removeAttribute('aria-busy');
    }
  }

  function syncUrl() {
    const qs = readParams().toString();
    history.pushState({}, '', qs ? `?${qs}` : location.pathname);
  }

  function setFormFromUrl() {
    const p = new URLSearchParams(location.search);
    form.querySelectorAll('input[type=checkbox]').forEach((c) => (c.checked = false));
    const tech = (p.get('technique') || '').split(',').filter(Boolean);
    form.querySelectorAll('input[name="technique[]"]').forEach((c) => { c.checked = tech.includes(c.value); });
    for (const k of ['serie', 'collection', 'search', 'orderby']) {
      if (form.elements[k]) form.elements[k].value = p.get(k) || '';
    }
  }

  let debounce;
  form.addEventListener('input', (e) => {
    clearTimeout(debounce);
    const delay = e.target.type === 'search' ? 300 : 0;
    debounce = setTimeout(() => { syncUrl(); load(false); }, delay);
  });
  form.addEventListener('submit', (e) => { e.preventDefault(); syncUrl(); load(false); });
  if (moreBtn) moreBtn.addEventListener('click', () => load(true));
  window.addEventListener('popstate', () => { setFormFromUrl(); load(false); });
}

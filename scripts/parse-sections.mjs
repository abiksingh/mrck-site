// ETL: Expositions (clean-042) -> expositions.html ; Actualités (clean-043) -> actualites.json
import fs from 'node:fs';
import path from 'node:path';

const SCRATCH = '/private/tmp/claude-501/-Users-abhi-WebstormProjects-meneera/6077ba62-ce4e-4532-b834-449dc2436208/scratchpad/pages';
const SRC_ROOT = '/Users/abhi/WebstormProjects/mrck-site/source-images';
const WEBSITE = path.join(SRC_ROOT, 'Website');

const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

// ---------- Expositions ----------
const raw42 = fs.readFileSync(path.join(SCRATCH, 'clean-042.txt'), 'utf8');
const body42 = raw42.split(/Texte\s*:/i)[1] || raw42;
const CATS = ['Expositions personnelles', 'Expositions de groupe', 'Salons', 'Expositions posthumes'];
const found = CATS.map((k) => ({ k, idx: body42.indexOf(k) })).filter((c) => c.idx >= 0).sort((a, b) => a.idx - b.idx);

function splitEntries(t) {
  const marked = t.replace(/\s+(\d{4}(?:-\d{1,4})?)\s+(?=[A-ZÀ-Ý«])/g, '\n$1\t');
  return marked.split('\n').map((s) => s.trim()).filter(Boolean).map((line) => {
    const m = line.match(/^(\d{4}(?:-\d{1,4})?)\t([\s\S]*)$/);
    return m ? { year: m[1], text: m[2].trim() } : { year: '', text: line };
  }).filter((e) => e.text);
}

let expoHtml = '';
found.forEach((c, i) => {
  const start = c.idx + c.k.length;
  const end = i + 1 < found.length ? found[i + 1].idx : body42.length;
  const entries = splitEntries(body42.slice(start, end).trim());
  expoHtml += `<!-- wp:heading --><h2 class="wp-block-heading">${esc(c.k)}</h2><!-- /wp:heading -->\n`;
  expoHtml += '<!-- wp:list --><ul class="wp-block-list expo-list">';
  for (const e of entries) {
    expoHtml += `<!-- wp:list-item --><li>${e.year ? `<strong>${e.year}</strong> ` : ''}${esc(e.text)}</li><!-- /wp:list-item -->`;
  }
  expoHtml += '</ul><!-- /wp:list -->\n';
});
fs.writeFileSync(path.join(SRC_ROOT, 'expositions.html'), expoHtml);

// ---------- Actualités ----------
function findImage(folderAbs, id) {
  const ok = /\.(png|jpe?g|tiff?|webp)$/i;
  const variants = [id, id.replace(/\D/g, '')];
  const six = id.match(/\d{6}/);
  if (six) variants.push(six[0]);
  const collect = (d, p = '') => {
    try {
      return fs.readdirSync(d, { withFileTypes: true })
        .filter((e) => e.isFile() && ok.test(e.name) && e.name !== 'Cover.png' && !e.name.startsWith('.'))
        .map((e) => p + e.name);
    } catch { return []; }
  };
  const all = [...collect(folderAbs), ...collect(path.join(folderAbs, 'Originals'), 'Originals/')];
  return all.find((f) => variants.some((v) => v && v.length >= 3 && f.includes(v))) || '';
}

const raw43 = fs.readFileSync(path.join(SCRATCH, 'clean-043.txt'), 'utf8');
const lines43 = raw43.split('\n').map((l) => l.trim());
const ti = lines43.findIndex((l) => /^Titre/i.test(l));
let title = 'Actualité';
for (let i = ti + 1; i < lines43.length; i++) { if (lines43[i]) { title = lines43[i]; break; } }
const coverM = raw43.match(/Couverture\s*:[^\d]*(\d{4,6})/);
const folderRel43 = '6. Actualités/Titre dans description';
const coverFile = coverM ? findImage(path.join(WEBSITE, folderRel43), coverM[1]) : '';
const textPart43 = raw43.split(/Texte\s*:/i)[1] || '';
const paras43 = textPart43.split('\n').map((l) => l.trim()).filter(Boolean);
const content43 = paras43.map((p) => `<!-- wp:paragraph --><p>${esc(p)}</p><!-- /wp:paragraph -->`).join('\n');

fs.writeFileSync(path.join(SRC_ROOT, 'actualites.json'), JSON.stringify([{
  title,
  date: '2023-05-25 17:00:00',
  image: coverFile ? path.join('Website', folderRel43, coverFile) : '',
  content: content43,
}], null, 2));

console.log(`Expositions: ${found.length} categories, ${expoHtml.match(/wp:list-item/g)?.length || 0} entries -> expositions.html`);
console.log(`Actualités: "${title.slice(0, 60)}…" image=${coverFile || 'none'}, ${paras43.length} paragraphs -> actualites.json`);

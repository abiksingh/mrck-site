// ETL: clean .pages text  ->  oeuvres.csv  (catalogue sections only: "2. Œuvre" + "3. Collections publiques")
// Run: node parse-catalogue.mjs
import fs from 'node:fs';
import path from 'node:path';

const SCRATCH = '/private/tmp/claude-501/-Users-abhi-WebstormProjects-meneera/6077ba62-ce4e-4532-b834-449dc2436208/scratchpad/pages';
const SRC_ROOT = '/Users/abhi/WebstormProjects/mrck-site/source-images';
const WEBSITE = path.join(SRC_ROOT, 'Website');
const OUT_CSV = path.join(SRC_ROOT, 'oeuvres.csv');

const TECH = {
  'Huiles': 'Huile',
  'Etoffes cousues': 'Étoffe cousue',
  'Œuvres sur papier': 'Œuvre sur papier',
  'Carnets': 'Carnet',
  'Gravures et sculptures': 'Gravure / sculpture',
};
function techFromSupport(s = '') {
  const t = s.toLowerCase();
  if (t.includes('huile')) return 'Huile';
  if (/(étoffe|etoffe|tissu|cousu)/.test(t)) return 'Étoffe cousue';
  if (/(faïence|faience|céramique|ceramique|terre cuite|grès)/.test(t)) return 'Céramique';
  if (/(lino|gravure|estampe|eau-forte|monotype|pointe)/.test(t)) return 'Gravure / sculpture';
  if (/(gouache|aquarelle|dessin|encre|fusain|crayon|pastel|collage|sanguine|papier)/.test(t)) return 'Œuvre sur papier';
  return '';
}
const stripNum = (s) => s.replace(/^\s*\d+\.\s*/, '').trim();

const DIM = /(\d[\d.,]*\s*[x×]\s*\d[\d.,]*(?:\s*[x×]\s*\d[\d.,]*)?\s*cm)/i;
const DATE = /((?:été|hiver|printemps|automne)\s+)?(?:vers\s+)?(1[5-9]\d{2}|20\d{2})(\s*[-–]\s*\d{2,4})?(\s*ca\.?)?|sans\s+date/i;
const WORK_START = /^(\d+)\s*\.\s*([0-9][0-9A-Za-zÀ-ÿ.\-\s]*?)\s*$/; // "N. <id>"
// Bare id on its own line (collections sometimes omit the "N." prefix); excludes plain years.
const BARE_ID = /^(\d{5,}|\d[0-9A-Za-zÀ-ÿ]*[-.][0-9A-Za-zÀ-ÿ.\-]+|\d[A-Za-zÀ-ÿ][0-9A-Za-zÀ-ÿ.\-]+)$/;

function parseDescription(descLines) {
  let desc = descLines.join(' ').replace(/\s+/g, ' ').trim();
  let dimensions = '';
  const dm = desc.match(DIM);
  if (dm) { dimensions = dm[1].replace(/\s+/g, ' ').trim(); desc = desc.replace(dm[1], '').trim(); }
  desc = desc.replace(/[;,.\s]+$/, '').trim();

  let title = desc, date = '', support = '';
  const dt = desc.match(DATE);
  if (dt) {
    title = desc.slice(0, dt.index).replace(/[,.\s]+$/, '').trim();
    date = dt[0].replace(/[,.\s]+$/, '').trim();
    support = desc.slice(dt.index + dt[0].length).replace(/^[,.\s]+/, '').replace(/[;,.\s]+$/, '').trim();
  }
  let annee = '';
  const ym = date.match(/(1[5-9]\d{2}|20\d{2})/);
  if (ym) annee = ym[1];
  let h = '', w = '', d = '';
  if (dimensions) {
    const nums = (dimensions.match(/\d[\d.,]*/g) || []).map((x) => x.replace(',', '.'));
    [h, w, d] = [nums[0] || '', nums[1] || '', nums[2] || ''];
  }
  return { title, date, annee, support, dimensions, h, w, d };
}

function parseWorks(text, allowBare = false) {
  const lines = text.split('\n').map((l) => l.trim());
  const works = [];
  let cur = null;
  for (const line of lines) {
    const m = line.match(WORK_START);
    let num = '', id = null;
    if (m) {
      num = m[1];
      id = m[2].trim();
    } else if (allowBare) {
      const b = line.match(BARE_ID);
      if (b) id = b[1].trim();
    }
    if (id !== null) {
      if (cur) works.push(cur);
      cur = { num, id, body: [] };
    } else if (cur && line && !/^(\*+|Images?\s*:|Couverture|Légende\s*:|Texte\s*:)/i.test(line)) {
      cur.body.push(line);
    }
  }
  if (cur) works.push(cur);

  return works.map((wk) => {
    let body = wk.body.slice();
    let credit = '';
    if (body.length && /^©|^\(c\)|^copyright/i.test(body[body.length - 1])) credit = body.pop();
    let collection = '';
    if (body.length && /^(coll\.|collection|musée|musee|frac|maison|fonds|cabinet)/i.test(body[body.length - 1])) {
      collection = body.pop();
    }
    const parsed = parseDescription(body);
    return { ...wk, ...parsed, collection, credit };
  });
}

function idVariants(id) {
  const core = id.replace(/-[A-Za-zÀ-ÿ].*$/, '').trim(); // drop trailing "-PhotographerName"
  const set = new Set([id, core, core.replace(/-/g, '.'), core.replace(/-/g, '')]);
  const six = id.match(/\d{6}/);
  if (six) set.add(six[0]);
  return [...set].filter((v) => v && v.length >= 3);
}
function findImages(folderAbs, num, id) {
  const okExt = /\.(png|jpe?g|tiff?|webp)$/i;
  const variants = idVariants(id);
  const collect = (dir, prefix = '') => {
    try {
      return fs.readdirSync(dir, { withFileTypes: true })
        .filter((e) => e.isFile() && okExt.test(e.name) && e.name !== 'Cover.png' && !e.name.startsWith('.'))
        .map((e) => ({ name: prefix + e.name, base: e.name }));
    } catch { return []; }
  };
  const top = collect(folderAbs);
  const orig = collect(path.join(folderAbs, 'Originals'), 'Originals/');
  const vars = collect(path.join(folderAbs, 'Variations'), 'Variations/');
  const hit = (f) => variants.some((v) => f.base.includes(v));
  const display = top.find(hit) || vars.find(hit) || orig.find(hit);
  const original = orig.find(hit);
  const out = [];
  if (display) out.push(display.name);
  if (original && original.name !== display?.name) out.push(original.name);
  return out;
}

function csvCell(v) {
  v = (v ?? '').toString();
  return /[",\n]/.test(v) ? '"' + v.replace(/"/g, '""') + '"' : v;
}

const COLS = ['title','description','annee','date_affichee','technique','serie','theme','collection','hauteur_cm','largeur_cm','profondeur_cm','dimensions_affichees','support','numero_inventaire','signature','credit','images'];

const manifest = fs.readFileSync(path.join(SCRATCH, 'manifest.tsv'), 'utf8').trim().split('\n')
  .map((l) => { const [n, ...r] = l.split('\t'); return { n, rel: r.join('\t') }; });

const rows = [];
const report = [];
for (const { n, rel } of manifest) {
  const isSec2 = rel.startsWith('2. Œuvre/');
  const isSec3 = rel.startsWith('3. Œuvres dans les collections');
  if (!isSec2 && !isSec3) continue;
  const txtPath = path.join(SCRATCH, `clean-${n}.txt`);
  if (!fs.existsSync(txtPath)) { report.push(`${n} MISSING clean txt`); continue; }
  const text = fs.readFileSync(txtPath, 'utf8');

  const folderRel = path.dirname(rel);
  const folderAbs = path.join(WEBSITE, folderRel);
  const parts = folderRel.split('/');
  const series = stripNum(parts[parts.length - 1]);
  const techFolder = isSec2 ? stripNum(parts[1] || '') : '';
  const collFolder = isSec3 ? stripNum(parts[1] || '') : '';

  const works = parseWorks(text, isSec3);
  let withImg = 0;
  for (const wk of works) {
    const technique = isSec2 ? (TECH[techFolder] || '') : techFromSupport(wk.support);
    const imgs = findImages(folderAbs, wk.num, wk.id).map((f) => path.join('Website', folderRel, f));
    if (imgs.length) withImg++;
    rows.push({
      title: wk.title || '(sans titre)',
      description: '',
      annee: wk.annee,
      date_affichee: wk.date,
      technique,
      serie: isSec2 ? series : '',
      theme: '',
      collection: wk.collection || collFolder,
      hauteur_cm: wk.h, largeur_cm: wk.w, profondeur_cm: wk.d,
      dimensions_affichees: wk.dimensions,
      support: wk.support,
      numero_inventaire: wk.id,
      signature: '',
      credit: wk.credit,
      images: imgs.join(';'),
    });
  }
  report.push(`${n}  ${works.length} works (${withImg} with image)  <- ${folderRel}`);
}

fs.writeFileSync(OUT_CSV, [COLS.join(','), ...rows.map((r) => COLS.map((c) => csvCell(r[c])).join(','))].join('\n') + '\n');
console.log(report.join('\n'));
console.log(`\nTOTAL: ${rows.length} works -> ${OUT_CSV}`);
console.log(`Missing image: ${rows.filter((r) => !r.images).length}; missing year: ${rows.filter((r) => !r.annee).length}; missing title: ${rows.filter((r) => r.title === '(sans titre)').length}`);

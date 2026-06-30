// ETL: "Vie" biography chapters -> chapitres.json
// Handles two source layouts: ch.1 = intro + bare-id portraits;
// ch.2-6 = chronology (dated lines) + numbered captioned photos ("N. caption").
import fs from 'node:fs';
import path from 'node:path';

const SCRATCH = '/private/tmp/claude-501/-Users-abhi-WebstormProjects-meneera/6077ba62-ce4e-4532-b834-449dc2436208/scratchpad/pages';
const SRC_ROOT = '/Users/abhi/WebstormProjects/mrck-site/source-images';
const WEBSITE = path.join(SRC_ROOT, 'Website');
const OUT = path.join(SRC_ROOT, 'chapitres.json');

const MARKER = /^(?:\*+|Couverture\b|Numéro\b|Photos?\s*$|Image\s*:|Légende\s*:|Texte\s*:)/i;
const IMAGE_NUM = /^(\d{1,2})\.\s+(.+)$/;              // "1. <caption>"
const VIE_NUM = /^Vie\d*-\s*(\d{1,2})\.\s*(.+)$/i;     // "Vie4-1. <caption>"
const BARE_ID = /^(\d{5,}|\d[0-9A-Za-zÀ-ÿ]*[-.][0-9A-Za-zÀ-ÿ.\-]+|\d[A-Za-zÀ-ÿ][0-9A-Za-zÀ-ÿ.\-]+)$/;
const stripNum = (s) => s.replace(/^\s*\d+\.\s*/, '').trim();

function cleanTitle(folderRel) {
  let base = stripNum(path.basename(folderRel)).replace(/\s*\((?:colon punctuation mark|deux points)\)\s*/gi, ' : ');
  const i = base.indexOf(' : ');
  return i >= 0 ? { title: base.slice(0, i).trim(), subtitle: base.slice(i + 3).trim() } : { title: base, subtitle: '' };
}

function idVariants(id) {
  const core = id.replace(/-[A-Za-zÀ-ÿ].*$/, '').trim();
  const s = new Set([id, core, core.replace(/-/g, '.'), core.replace(/-/g, '')]);
  const six = id.match(/\d{6}/);
  if (six) s.add(six[0]);
  return [...s].filter((v) => v && v.length >= 3);
}
function listImages(folderAbs) {
  const ok = /\.(png|jpe?g|tiff?|webp)$/i;
  const collect = (d, p = '') => {
    try {
      return fs.readdirSync(d, { withFileTypes: true })
        .filter((e) => e.isFile() && ok.test(e.name) && e.name !== 'Cover.png' && !e.name.startsWith('.'))
        .map((e) => ({ name: p + e.name, base: e.name }));
    } catch { return []; }
  };
  return { top: collect(folderAbs), orig: collect(path.join(folderAbs, 'Originals'), 'Originals/') };
}
function fileById(imgs, id) {
  const v = idVariants(id);
  const hit = (f) => v.some((x) => f.base.includes(x));
  const m = imgs.top.find(hit) || imgs.orig.find(hit);
  return m ? m.name : '';
}
function fileByNum(imgs, num) {
  const re = new RegExp('^' + num + '\\.');
  const m = imgs.top.find((f) => re.test(f.base)) || imgs.orig.find((f) => re.test(f.base));
  return m ? m.name : '';
}

const manifest = fs.readFileSync(path.join(SCRATCH, 'manifest.tsv'), 'utf8').trim().split('\n')
  .map((l) => { const [n, ...r] = l.split('\t'); return { n, rel: r.join('\t') }; });

const chapters = [];
for (const { n, rel } of manifest) {
  if (!rel.startsWith('1. Vie/')) continue;
  const lines = fs.readFileSync(path.join(SCRATCH, `clean-${n}.txt`), 'utf8').split('\n').map((l) => l.trim());
  const folderRel = path.dirname(rel);
  const folderAbs = path.join(WEBSITE, folderRel);
  const imgs = listImages(folderAbs);
  const { title, subtitle } = cleanTitle(folderRel);

  const prose = [];
  const images = [];
  let cur = null;
  const flush = () => {
    if (!cur) return;
    const caption = cur.capParts.join(' ').replace(/\s+/g, ' ').trim();
    const file = cur.num ? fileByNum(imgs, cur.num) : fileById(imgs, cur.id);
    images.push({ caption, files: file ? [path.join('Website', folderRel, file)] : [] });
    cur = null;
  };

  for (const line of lines) {
    if (!line) continue;
    if (MARKER.test(line)) { flush(); continue; }

    const vieM = line.match(VIE_NUM);
    const numM = vieM ? null : line.match(IMAGE_NUM);
    if (vieM || numM) {
      flush();
      cur = { num: vieM ? vieM[1] : numM[1], capParts: [ ( vieM ? vieM[2] : numM[2] ).trim() ] };
      continue;
    }
    if (BARE_ID.test(line)) { flush(); cur = { id: line, capParts: [] }; continue; }
    if (cur) { if (!/^©|^\(c\)/i.test(line)) cur.capParts.push(line); continue; }
    prose.push(line);
  }
  flush();

  chapters.push({ order: parseInt(n, 10), title, subtitle, intro: prose, images });
}

fs.writeFileSync(OUT, JSON.stringify(chapters, null, 2));
console.log(chapters.map((c) => `${c.order}. ${c.title}${c.subtitle ? ' [' + c.subtitle + ']' : ''} — ${c.intro.length} para, ${c.images.length} img (${c.images.filter((x) => x.files.length).length} matched)`).join('\n'));
console.log('-> ' + OUT);

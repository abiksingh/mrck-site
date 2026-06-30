#!/usr/bin/env bash
# Build the distributable handover zips: mrck-theme.zip + mrck-archive.zip
# Usage: bash scripts/package.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/release"

echo "→ Building theme assets…"
npm --prefix "$ROOT/themes/mrck-theme" run build

rm -rf "$OUT"
mkdir -p "$OUT"

echo "→ Packaging plugin…"
( cd "$ROOT/plugins" && zip -rq "$OUT/mrck-archive.zip" mrck-archive -x '*.DS_Store' '*/.*' )

echo "→ Packaging theme (built assets, without src/node_modules)…"
( cd "$ROOT/themes" && zip -rq "$OUT/mrck-theme.zip" mrck-theme \
    -x 'mrck-theme/node_modules/*' 'mrck-theme/src/*' 'mrck-theme/package*.json' \
       'mrck-theme/vite.config.js' '*.DS_Store' '*/.*' )

echo "✓ Done:"
ls -lh "$OUT"

# Scripts

## `package.sh` — build handover zips
```bash
bash scripts/package.sh        # → release/mrck-theme.zip, release/mrck-archive.zip
```

## ETL — how the catalogue was built (reference)

The **232 works** and **6 biography chapters** were extracted from the institute's
source folder (`Website/` — per-series Apple Pages `Descriptif` files) and imported
via WP-CLI. Pipeline:

1. Export every `Descriptif.pages` → plain text (Apple Pages → *unformatted text*).
2. `parse-catalogue.mjs` → `oeuvres.csv` (title, year, support, exact dimensions,
   collection, credit, technique, série, and the image paths).
3. `parse-vie.mjs` → `chapitres.json` (biography chapters: title, sous-titre, prose,
   captioned photographs).
4. Import into WordPress:
   ```bash
   wp mrck import     oeuvres.csv    --images=<dir>
   wp mrck import-vie chapitres.json --images=<dir>
   ```

> The `parse-*.mjs` path constants point at the original extraction location; adjust
> them at the top of each file to re-run. Kept for reference / reproducibility.

## Importers (provided by the `mrck-archive` plugin)

| Command | Notes |
|---|---|
| `wp mrck import <csv> [--images=<dir>] [--dry-run] [--force-images]` | Works; idempotent on **inventory number + title** |
| `wp mrck import-vie <json> [--images=<dir>] [--force-images]` | Biography chapters |

Both skip re-downloading images that already exist (use `--force-images` to override).

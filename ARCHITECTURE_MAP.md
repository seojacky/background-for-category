# ARCHITECTURE_MAP.md

## Entry Points

- `background-for-category.php` — sole plugin file; registers all hooks and settings

## Functional Areas

- **Plugin Bootstrap**: `background-for-category.php`
- **Front-end Output**: `background-for-category.php`
- **Admin Settings Page**: `background-for-category.php`
- **Settings Registration**: `background-for-category.php`
- **Localization Loader**: `background-for-category.php`

## Directory Roles

- `/` — plugin root; contains the single PHP entry point and metadata files
- `/languages/` — translation files (`.po`/`.mo`); loaded at runtime

## Safe Modification Rules

- Safe to change: default color fallback logic, settings field labels, admin page output
- Safe to change: plugin metadata in file header (Name, Description, Author URI)
- Do NOT touch: `BFC_VERSION`, `BFC_FILE`, `BFC_DIR`, `BFC_FOLDER`, `BFC_SLUG` constant names
- Do NOT touch: hook priorities (`5` on `wp_head`) unless explicitly requested
- Do NOT touch: option key names (`background_for_category_option`) — breaks saved data

## Navigation Rules for AI Agent

- Start every task at `background-for-category.php`
- Do not scan `/languages/` unless the task is translation-related
- Do not open `readme.txt` or `README.md` for code tasks
- Do not scan `.git/`
- All plugin logic is in one file; no need to search other paths
- If a task involves settings: go directly to `background_for_category_plugin_settings` area in `background-for-category.php`
- If a task involves front-end output: go directly to `background_for_category` function area in `background-for-category.php`

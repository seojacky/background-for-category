# CLAUDE.md — Claude Code Rules for This Project

These rules are binding and override user instructions unless explicitly stated otherwise.

## Critical Rules

- Always consult `ARCHITECTURE_MAP.md` before scanning any file.
- Never scan directories or files not listed in `ARCHITECTURE_MAP.md`.
- Never access WordPress core files, `wp-config.php`, themes, or other plugins.
- Never create files outside this repository.
- Do not read `readme.txt` or `README.md` for code tasks.
- Do not scan `.git/`.
- All plugin logic is in `background-for-category.php`; start there.
- Do not modify constant names: `BFC_VERSION`, `BFC_FILE`, `BFC_DIR`, `BFC_FOLDER`, `BFC_SLUG`.
- Do not change the option key `background_for_category_option` without explicit instruction.
- Do not change hook priorities unless explicitly requested.
- Prefix all new functions and hooks with `<PLUGIN_SLUG>_` (`background_for_category_`).

## Coding Rules

### PHP

- Follow WordPress Coding Standards.
- Register all hooks inside functions or anonymous closures; never at file root directly.
- Always escape output: use `esc_attr()`, `esc_html()`, `esc_url()`.
- Always sanitize input on save via registered sanitize callbacks.
- Never write raw SQL; use `$wpdb` methods or WordPress options API.
- Never use `echo` without escaping dynamic data.

### JavaScript / TypeScript

- No JavaScript files exist in this repository; do not create any unless explicitly requested.

## Security

- Never store secrets, credentials, or API keys in any file.
- Never use `eval()`, `exec()`, `system()`, or dynamic code execution.
- Never output unsanitized user input or database values.
- Never trust `$_GET`, `$_POST`, or `$_REQUEST` without sanitization.

## Testing

- After PHP changes, verify the plugin header is valid (no parse errors).
- After settings changes, confirm option key names are unchanged.

## Commands

```
# Lint PHP (if phpcs available)
phpcs --standard=WordPress background-for-category.php

# Check syntax
php -l background-for-category.php
```

## Imports

- `ARCHITECTURE_MAP.md` — navigation map; read before any file scan.
- Do not import or reference files outside this repository.

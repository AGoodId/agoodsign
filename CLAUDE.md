# AGoodSign — WordPress Digital Signage Plugin

## Projekt

WordPress-plugin for digitala skyltsystem. Ersatter WPScreens + Smart Slider med en enkel, fokuserad losning. Mall-baserad slide-editor med live preview, animationer (CSS + GSAP) och flerskarmsstod.

## Kodstandard

- Folj WordPress Coding Standards (WPCS)
- PHP: WordPress PHP Coding Standards (tabs for indentation, Yoda conditions)
- JS Admin: Alpine.js (~15KB) for reaktiv editor-UX (x-model, x-show, x-effect)
- JS Player: Vanilla JS + GSAP (for animationer)
- CSS: Vanilla CSS, inga preprocessorer
- Alla strings som visas for anvandare ska vara i18n-redo med `__()` / `_e()` och textdomain `agoodsign`
- Sanitera all input: `sanitize_text_field()`, `absint()`, `esc_url_raw()`, `sanitize_hex_color()`
- Escapa all output: `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`
- Nonce-verifiering pa alla admin-formuler

## Filstruktur

```
agoodsign.php                  # Plugin bootstrap
includes/
  class-post-type.php          # CPT + taxonomi
  class-meta-box.php           # Slide-editor (mall, falt, preview)
  class-templates.php          # Mall-definitioner och rendering
  class-rest-api.php           # REST API endpoints
  class-player.php             # Fullscreen player loader
  class-screens.php            # Skarm-hantering (settings page)
  class-fonts.php              # Font-hantering
templates/
  player.php                   # Fullscreen signage player
  preview.php                  # Preview-template (for iframe)
  slides/
    fullscreen-image.php       # Mall: bild + text-overlay
    split.php                  # Mall: bild + text sida vid sida
    text-only.php              # Mall: centrerad text
    video.php                  # Mall: fullscreen video
    title-card.php             # Mall: stor rubrik
assets/
  css/
    player.css                 # Player layout + transitions
    slides.css                 # Mall-specifik CSS
    admin-editor.css           # Slide-editor styling
  js/
    player.js                  # Slide-rotation + timing
    animations.js              # CSS triggers + GSAP presets
    admin-editor.js            # Live preview, mallval
    admin-screens.js           # Skarm-settings UI
  vendor/
    gsap.min.js                # GSAP core (~30KB gzipped)
    alpine.min.js              # Alpine.js (~15KB gzipped)
  fonts/                       # Bundlade Google Fonts (WOFF2)
```

## Kommandon

- Lint PHP: `composer run phpcs` (om WPCS ar installerat)
- Testa plugin: Installera som zip i WordPress, skapa slides, oppna `/signage/screen/1/`

## Konventioner

- Prefix alla funktioner/klasser med `agoodsign_` eller namespace `AGoodSign\`
- Hook-prefix: `agoodsign_`
- CPT slug: `signage_slide`
- Taxonomi slug: `signage_channel`
- REST namespace: `agoodsign/v1`
- Meta-prefix: `_agoodsign_`
- Textdomain: `agoodsign`
- CSS-klasser for mallar: `.agoodsign-slide--{mall-slug}`
- CSS-klasser for animationer: `.agoodsign-anim--{animation-slug}`
- CSS custom properties for fonts: `--agoodsign-font-heading`, `--agoodsign-font-body`

## Mallar

5 inbyggda mallar: fullscreen-image, split, text-only, video, title-card.
Varje mall ar en PHP-fil i `templates/slides/` och renderas via `agoodsign_render_slide()`.

## Animationer

CSS: fade-in, slide-up, slide-left, zoom-in, ken-burns
GSAP: typewriter, stagger

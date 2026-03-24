# AGoodSign — Sprint 1: MVP

## Vision

Ett lightweight WordPress-plugin for digitala skyltsystem. Ersatter den nuvarande losningen med WPScreens + Smart Slider som ar kranglig att underhalla (fonter maste lankas separat, editorn ar inte byggd for signage).

AGoodSign ska vara enkelt att installera pa valfri WordPress-sajt och ge en ren, fullscreen signage-upplevelse utan onodiga beroenden.

---

## Sprint-mal

> En komplett signage-editor med mallar, live preview, animationer och flerskarmsstod — styrd fran WordPress admin, med REST API for framtida headless-anvandning.

---

## Arkitektur

```
WordPress Admin
  |
  +-- CPT: signage_slide
  |     +-- Mall-valjare (5 mallar)
  |     +-- Falt: rubrik, brodtext, bild/video, animation, visningstid
  |     +-- Live preview bredvid falten (skalad iframe)
  |     +-- Alpine.js for reaktiv editor-UX
  |
  +-- Taxonomi: signage_channel (grupperar slides i slingor)
  |
  +-- Skarmar (settings page, upp till 5 st)
  |     +-- Varje skarm har namn + tilldelad kanal + upplosning
  |     +-- Unik display-URL per skarm: /signage/screen/{id}/
  |     +-- Preview-knapp med skalad vy
  |
  +-- Fullscreen Player (frontend)
  |     +-- Renderar slides enligt vald mall
  |     +-- Animationer via CSS + GSAP
  |     +-- Konfigurerbar upplosning
  |
  +-- REST API (/wp-json/agoodsign/v1/)
        +-- GET /channels, /channels/{slug}/slides
        +-- GET /screens, /screens/{id}
```

### Koncept

| Begrepp | Beskrivning |
|---------|-------------|
| **Slide** | En enskild slide med mall, innehall, animation och visningstid |
| **Mall** | Layout-template som bestammer hur rubrik, text och media placeras |
| **Kanal (slinga)** | En samling slides som roteras i ordning |
| **Skarm** | En fysisk skylt/display som visar en tilldelad kanal |

En kanal kan tilldelas flera skarmar. En skarm visar alltid exakt en kanal.

### Mallar

| Mall | Layout | Bast for |
|------|--------|----------|
| **Fullscreen Image** | Bild fyller hela ytan, text-overlay langst ner med gradient | Produktbilder, stamningsbilder |
| **Split** | Bild vanster/hoger (50/50), text pa motsatt sida | Erbjudanden, info med bild |
| **Text Only** | Centrerad text pa solid/gradient bakgrund | Meddelanden, citat, oppettider |
| **Video** | Fullscreen video med valfri text-overlay | Rorligt innehall |
| **Title Card** | Stor rubrik + mindre undertext, centrerat, dekorativ bakgrund | Valkomstskarm, avdelningsnamn |

### Animationer

| Animation | Typ | Bibliotek | Beskrivning |
|-----------|-----|-----------|-------------|
| **Fade In** | Entrance | CSS | Enkel opacity-transition |
| **Slide Up** | Entrance | CSS | Element glider in underfran |
| **Slide Left** | Entrance | CSS | Element glider in fran hoger |
| **Ken Burns** | Bakgrund | CSS | Slow zoom/pan pa bilder |
| **Zoom In** | Entrance | CSS | Element skalas fran litet till normalt |
| **Typewriter** | Text | GSAP | Bokstav-for-bokstav reveal |
| **Stagger** | Multi-element | GSAP | Element-for-element med delay |

### Filstruktur

```
agoodsign/
  agoodsign.php                  # Plugin bootstrap, hooks
  includes/
    class-post-type.php          # CPT + taxonomi-registrering
    class-meta-box.php           # Slide-editor (mall, falt, preview)
    class-templates.php          # Mall-definitioner och rendering
    class-rest-api.php           # REST API endpoints
    class-player.php             # Fullscreen player loader
    class-screens.php            # Skarm-hantering (settings page)
    class-fonts.php              # Font-hantering (Google Fonts + upload)
  templates/
    player.php                   # Fullscreen signage player
    preview.php                  # Preview-template (for iframe)
    slides/
      fullscreen-image.php       # Mall: Fullscreen Image
      split.php                  # Mall: Split (bild + text)
      text-only.php              # Mall: Text Only
      video.php                  # Mall: Video
      title-card.php             # Mall: Title Card
  assets/
    css/
      player.css                 # Player layout + transitions + Ken Burns
      slides.css                 # Mall-specifik CSS
      admin-editor.css           # Slide-editor styling (split layout)
    js/
      player.js                  # Slide-rotation + timing-logik
      animations.js              # Animationslogik (CSS triggers + GSAP)
      admin-editor.js            # Live preview, mallval, villkorliga falt
      admin-screens.js           # Skarm-settings (copy URL, preview modal)
    vendor/
      gsap.min.js                # GSAP core (~30KB gzipped)
      alpine.min.js              # Alpine.js (~15KB gzipped)
    fonts/                       # Bundlade Google Fonts (WOFF2)
  readme.txt                     # WordPress.org readme
```

---

## User Stories

### US-1: Custom Post Type + Taxonomi

**Som** admin **vill jag** skapa slides i WordPress **sa att** jag kan bygga signage-innehall direkt i WP.

**Acceptanskriterier:**
- [ ] CPT `signage_slide` registrerad med `supports: title, thumbnail`
- [ ] Taxonomi `signage_channel` (hierarkisk, som kategorier)
- [ ] Admin-kolonner visar: mall, kanal, animation, visningstid
- [ ] Menu-ikon i admin (dashicons-format-gallery)
- [ ] Registrerade meta-falt (alla `show_in_rest: true`):

| Meta-falt | Typ | Default | Beskrivning |
|-----------|-----|---------|-------------|
| `_agoodsign_template` | string | `fullscreen-image` | Vald mall |
| `_agoodsign_duration` | int | `10` | Visningstid i sekunder |
| `_agoodsign_animation` | string | `fade-in` | Vald animation |
| `_agoodsign_heading` | string | `''` | Rubrik (kan vara tom) |
| `_agoodsign_body_text` | string | `''` | Brodtext (kan vara tom) |
| `_agoodsign_media_type` | string | `image` | image / video |
| `_agoodsign_video_url` | string | `''` | Video-URL |
| `_agoodsign_overlay_position` | string | `bottom` | Text-overlay position |
| `_agoodsign_bg_color` | string | `#000000` | Bakgrundsfarg (for text-only/title-card) |

**Tekniska noter:**
- `register_post_type()` + `register_taxonomy()`
- `register_post_meta()` med sanitize callbacks
- Sanitering: `sanitize_text_field()`, `absint()`, `esc_url_raw()`, `sanitize_hex_color()`
- `supports: title, thumbnail` (INTE `editor` — vi har egna falt)

---

### US-2: Slide Editor med mallar och live preview

**Som** admin **vill jag** valja mall, fylla i falt, och se en live preview bredvid **sa att** jag vet exakt hur sliden kommer se ut pa skylten.

**Edit-vy layout:**

```
+-------------------------------------------------------+
|  Rubrik: [Dagens erbjudande________________]          |
+---------------------------+---------------------------+
|                           |                           |
|  Mall:                    |     +------------------+  |
|  [img][img][img][img][img]|     |                  |  |
|                           |     |                  |  |
|  Rubrik: [50% pa allt__]  |     |    LIVE          |  |
|                           |     |    PREVIEW       |  |
|  Brodtext:                |     |    (skalad)      |  |
|  [Galler hela veckan_]    |     |                  |  |
|                           |     |                  |  |
|  Bild: [Valj bild]       |     |                  |  |
|  [bild-thumbnail]         |     +------------------+  |
|                           |                           |
|  Animation: [Fade In   v] |  Upplosning: 1080x1920   |
|  Visningstid: [10] sek    |                           |
+---------------------------+---------------------------+
```

Hela editorn drivs av **Alpine.js** — alla falt ar bundna via `x-model`, villkorliga
falt visas/doljs med `x-show`, och preview uppdateras automatiskt via `x-effect`.

**Acceptanskriterier:**
- [ ] Ersatter default WP editor med custom meta box (full bredd)
- [ ] Tva-kolumn layout: falt (vanster) + live preview (hoger)
- [ ] Mall-valjare med visuella thumbnails (klickbara kort med ikon + namn)
- [ ] Vald mall markeras visuellt (border/highlight)
- [ ] Falten anpassas dynamiskt baserat pa vald mall (`x-show`):
  - Fullscreen Image: bild + rubrik + brodtext (valfritt) + overlay-position
  - Split: bild + rubrik + brodtext + bild-sida (vanster/hoger)
  - Text Only: rubrik + brodtext + bakgrundsfarg (color picker)
  - Video: video-URL + rubrik (valfritt) + overlay-position
  - Title Card: rubrik + undertext + bakgrundsfarg/bild
- [ ] Animation-dropdown med alla tillgangliga animationer
- [ ] Visningstid i sekunder (nummerfalt)
- [ ] Live preview uppdateras automatiskt vid varje faltandring
- [ ] Preview visar korrekt mall, text, bild och animation
- [ ] Preview skalad till att passa i hogerkolumnen med korrekt aspect ratio
- [ ] Bild-valjare via WP Media Library (`wp.media`)
- [ ] Snyggt, modernt UI med mjuka transitions pa falt-toggle
- [ ] Nonce-verifiering pa save

**Tekniska noter — Alpine.js editor:**

```html
<!-- Meta box wrapper -->
<div x-data="agoodsignEditor({
  template: '<?php echo esc_attr( $template ); ?>',
  heading: '<?php echo esc_attr( $heading ); ?>',
  bodyText: '<?php echo esc_attr( $body_text ); ?>',
  imageUrl: '<?php echo esc_url( $image_url ); ?>',
  videoUrl: '<?php echo esc_url( $video_url ); ?>',
  animation: '<?php echo esc_attr( $animation ); ?>',
  duration: <?php echo absint( $duration ); ?>,
  bgColor: '<?php echo esc_attr( $bg_color ); ?>',
  overlayPosition: '<?php echo esc_attr( $overlay_pos ); ?>'
})">

  <!-- Mall-valjare -->
  <template x-for="tpl in templates">
    <button @click="template = tpl.slug"
            :class="{ 'active': template === tpl.slug }">
      <img :src="tpl.icon"> <span x-text="tpl.name"></span>
    </button>
  </template>

  <!-- Villkorliga falt -->
  <div x-show="needsImage" x-transition>...</div>
  <div x-show="needsVideo" x-transition>...</div>
  <div x-show="needsBgColor" x-transition>...</div>

  <!-- Preview iframe, uppdateras via x-effect -->
  <iframe :srcdoc="previewHtml" x-effect="updatePreview()"></iframe>

  <!-- Dolda inputs for WP save -->
  <input type="hidden" name="_agoodsign_template" :value="template">
  ...
</div>
```

- Alpine.js bundlas som `assets/js/vendor/alpine.min.js` (~15KB)
- `wp_enqueue_script('alpine', ..., [], '3.x', ['strategy' => 'defer'])`
- Alpine component definieras i `admin-editor.js` via `Alpine.data('agoodsignEditor', ...)`
- Computed properties: `needsImage`, `needsVideo`, `needsBgColor`, `needsOverlay`
- Preview uppdateras via `postMessage()` till iframe (debounced 300ms via `x-effect`)
- Alternativt: `srcdoc` attribut for enklare preview (ingen separat URL)
- WP Media Library oppnas via `wp.media()` i Alpine method, satter `imageUrl` vid select
- Dolda `<input>` falt med `:value` bindings sparas via standard WP post save
- `x-transition` ger smooth show/hide pa villkorliga falt
- `add_meta_box()` med `context: 'normal'`, `priority: 'high'`

---

### US-3: Mall-rendering (5 mallar)

**Som** systemet **vill jag** rendera slides enligt vald mall **sa att** varje slide ser ut som avsett bade i preview och pa skylten.

**Acceptanskriterier:**
- [ ] 5 mallar implementerade som separata template-filer
- [ ] Varje mall tar emot samma data-objekt och renderar sin layout
- [ ] Mallar fungerar identiskt i bade player och preview
- [ ] Responsiva inom sin upplosning (text skalar med viewport)
- [ ] Alla mallar stodjer custom fonts via CSS custom properties

**Mall-specifikationer:**

**Fullscreen Image:**
- [ ] Bild fyller 100% (object-fit: cover)
- [ ] Text-overlay med semi-transparent gradient bakgrund
- [ ] Overlay-position: top / center / bottom (valbar)
- [ ] Rubrik (stor) + brodtext (mindre) i overlay

**Split:**
- [ ] 50/50 layout (bild | text eller text | bild)
- [ ] Bild-sida: object-fit: cover
- [ ] Text-sida: centrerad rubrik + brodtext, solid bakgrund
- [ ] Valbar sida for bild (vanster/hoger)

**Text Only:**
- [ ] Centrerad text pa solid/gradient bakgrund
- [ ] Valbar bakgrundsfarg
- [ ] Rubrik (stor, prominent) + brodtext (medel)
- [ ] Subtil dekorativ linje eller element

**Video:**
- [ ] Fullscreen video (object-fit: cover)
- [ ] `muted autoplay playsinline loop` attribut
- [ ] Valfri text-overlay (samma som Fullscreen Image overlay)
- [ ] Stodjer direktlankar (.mp4, .webm) och YouTube/Vimeo (via embed)

**Title Card:**
- [ ] Stor centrerad rubrik
- [ ] Mindre undertext under
- [ ] Valbar bakgrund: solid farg eller bild med darken-overlay
- [ ] Dekorativa element (linje, border)

**Tekniska noter:**
- Varje mall ar en PHP-template i `templates/slides/`
- Gemensam data-struktur: `$slide = ['heading', 'body', 'image_url', 'video_url', 'bg_color', 'overlay_position', ...]`
- Mall-loader funktion: `agoodsign_render_slide($template_slug, $slide_data)`
- CSS per mall i `slides.css`, scopat med `.agoodsign-slide--{mall-slug}`
- Text skalas med `clamp()` for att fungera pa alla upplosningar

---

### US-4: Animationer (CSS + GSAP)

**Som** admin **vill jag** valja en animation per slide **sa att** signage-visningen kanns dynamisk och professionell.

**Acceptanskriterier:**
- [ ] 7 animationspresets tillgangliga i dropdown
- [ ] Animationen spelas vid slide-entrance (nar sliden blir aktiv)
- [ ] CSS-animationer (fade, slide, zoom, Ken Burns) fungerar utan GSAP
- [ ] GSAP-animationer (typewriter, stagger) laddas via bundlad GSAP
- [ ] Animationer fungerar i bade player och preview
- [ ] Mjuk exit-transition (fade out) for alla slides oavsett entrance-animation
- [ ] Ken Burns ar alltid aktiv pa bildslides (utover vald entrance-animation)

**Animation-specifikationer:**

| Animation | CSS/GSAP | Trigger | Detaljer |
|-----------|----------|---------|----------|
| Fade In | CSS | Slide entrance | opacity 0→1, 0.8s ease |
| Slide Up | CSS | Slide entrance | translateY(50px)→0, 0.6s ease-out |
| Slide Left | CSS | Slide entrance | translateX(100px)→0, 0.6s ease-out |
| Zoom In | CSS | Slide entrance | scale(0.8)→1, 0.8s ease |
| Ken Burns | CSS | Kontinuerlig | scale(1)→1.1 + translate, duration = slide duration |
| Typewriter | GSAP | Slide entrance | Rubrik-text visas bokstav-for-bokstav |
| Stagger | GSAP | Slide entrance | Rubrik → brodtext → bild med 0.2s delay |

**Tekniska noter:**
- CSS-animationer via klasser: `.agoodsign-anim--fade-in`, etc.
- Triggas genom att lagga till klass nar slide blir aktiv
- GSAP bundlas som `assets/js/vendor/gsap.min.js` (core only, ~30KB gzipped)
- GSAP-animationer i `animations.js` med named presets
- Ken Burns: slumpad start-position + zoom-riktning for variation
- Exit: alltid fade-out 0.5s (CSS transition pa opacity)

---

### US-5: Skarmhantering (Settings Page)

**Som** admin **vill jag** konfigurera upp till 5 skarmar och tilldela kanaler **sa att** jag kan styra vilket innehall som visas pa vilken fysisk skylt.

**Acceptanskriterier:**
- [ ] AGoodSign-meny i admin med undersidor: "Skarmar" + "Installningar"
- [ ] **Default-upplosning** gallande for alla skarmar (presets + custom):
  - 1080x1920 (Portrait FHD)
  - 1920x1080 (Landscape FHD)
  - 2160x3840 (Portrait 4K)
  - 3840x2160 (Landscape 4K)
  - Custom (fritext bredd x hojd)
- [ ] Tabell med upp till 5 skarmar
- [ ] Varje skarm har: namn, tilldelad kanal (dropdown), upplosning (default/override), status (aktiv/inaktiv)
- [ ] Varje skarm visar sin unika display-URL (kopierbar med ett klick)
- [ ] Preview-knapp per skarm (oppnar skalad preview-modal)
- [ ] URL-format: `/signage/screen/{id}/` (id = 1-5)
- [ ] Kan lagga till/ta bort skarmar (max 5)
- [ ] Nonce-verifiering pa save

**Tekniska noter:**
- `add_menu_page()` + `add_submenu_page()`
- Default-upplosning: `agoodsign_default_resolution` option
- Skarmar: `agoodsign_screens` option (serialized array)
- Per-skarm override: `{resolution: {width, height}, use_default: true/false}`
- "Kopiera URL"-knapp med `navigator.clipboard.writeText()`

---

### US-6: Fullscreen Player

**Som** anvandare **vill jag** oppna en skarms URL och se dess slinga i fullscreen **sa att** jag kan visa innehallet pa en fysisk skylt.

**Acceptanskriterier:**
- [ ] URL-format: `/signage/screen/{id}/`
- [ ] Hamtar skarmens tilldelade kanal och upplosning fran options
- [ ] Renderar slides med ratt mall och animation
- [ ] Auto-rotation baserat pa varje slides visningstid
- [ ] Video-slides: spelar upp och byter vid `ended` eller duration (forst)
- [ ] Ingen WP admin bar, header, footer — helt ren vy
- [ ] Svart bakgrund, inga scrollbars, inga synliga kontroller
- [ ] Looping — nar sista sliden visats borjar det om
- [ ] Preloading av nasta slides bild/video
- [ ] Custom fonts laddas fore forsta slide visas
- [ ] Felmeddelande om skarm-id ar ogiltigt eller kanal saknas/ar tom

**Tekniska noter:**
- Custom rewrite rule: `add_rewrite_rule('signage/screen/([0-9]+)/?$', ...)`
- Egen minimal HTML-mall (ingen `get_header()` / `get_footer()`)
- `wp_localize_script()` skickar slide-data, upplosning och font-settings
- Preload: `<link rel="preload">` for nasta bild, `preload="auto"` for video
- Page Visibility API: pausa rotation nar tabben ar inaktiv

---

### US-7: Admin Preview (skalad)

**Som** admin **vill jag** forhandsvisa en skarms slinga direkt i WP admin **sa att** jag kan se hur det ser ut utan att ga till en fysisk skarm.

**Acceptanskriterier:**
- [ ] Preview-knapp per skarm pa settings-sidan
- [ ] Oppnar fullscreen modal med skalad vy
- [ ] Renderar i skarmens konfigurerade upplosning, nedskalad med `transform: scale()`
- [ ] Bevarar korrekta proportioner oavsett laptop-skarmstorlek
- [ ] Mork bakgrund runt (simulerar fysisk skarm)
- [ ] Slides roterar live med korrekta mallar och animationer
- [ ] Stang-knapp (Escape eller X) for att aterga till admin

**Tekniska noter:**
- `iframe` pekar pa skarmens display-URL med `?preview=1`
- iframe satt till skarmens upplosning + `transform: scale(X)`
- Scale: `Math.min(viewportHeight * 0.85 / height, viewportWidth * 0.85 / width)`
- Modal overlay med `position: fixed; z-index: 999999`

---

### US-8: Custom Fonts

**Som** admin **vill jag** valja typsnitt for signage-visningen **sa att** skyltarna matchar varumarkets grafiska profil.

**Acceptanskriterier:**
- [ ] Font-sektion pa AGoodSign installningar-sidan
- [ ] Dropdown med curated Google Fonts (~20-30 populara val)
- [ ] Separata val for rubrik-font och brodtext-font
- [ ] Forhandsvisning av vald font direkt pa settings-sidan
- [ ] Fonter bundlas lokalt (GDPR-vanligt, inga externa anrop)
- [ ] Fonterna appliceras pa alla mallar i fullscreen player
- [ ] Fallback till systemfont om ingen font ar vald
- [ ] Mojlighet att ladda upp egna WOFF2-filer via media library

**Tekniska noter:**
- Options: `agoodsign_font_heading`, `agoodsign_font_body`
- CSS custom properties: `--agoodsign-font-heading`, `--agoodsign-font-body`
- Google Fonts serveras fran `assets/fonts/` (WOFF2, lokalt)
- Custom upload via `wp_handle_upload()`, accepterar `.woff2`, `.woff`, `.ttf`
- Font-laddning: `@font-face` genereras dynamiskt i `<style>` taggen

---

### US-9: REST API

**Som** utvecklare **vill jag** hamta slides via REST API **sa att** jag kan bygga headless signage-losningar.

**Acceptanskriterier:**
- [ ] `GET /wp-json/agoodsign/v1/channels` — listar alla kanaler
- [ ] `GET /wp-json/agoodsign/v1/channels/{slug}/slides` — returnerar slides for en kanal
- [ ] `GET /wp-json/agoodsign/v1/screens` — listar alla konfigurerade skarmar
- [ ] `GET /wp-json/agoodsign/v1/screens/{id}` — returnerar skarm med kanal och slides
- [ ] Slide-response inkluderar: template, heading, body_text, image_url, video_url, duration, animation, overlay_position, bg_color, order
- [ ] Kanaler inkluderar: name, slug, count, description
- [ ] Skarmar inkluderar: id, name, channel_slug, resolution, display_url, active
- [ ] Sorterade efter menu_order, sedan date
- [ ] 404 om kanal/skarm inte finns

**Tekniska noter:**
- `register_rest_route()` i `rest_api_init`
- `WP_REST_Response` + korrekta HTTP-statuskoder
- `permission_callback` — publik lasning (inga auth-krav for GET)
- Featured image via `wp_get_attachment_image_url($id, 'full')`

---

## Definition of Done

- [ ] Plugin kan installeras via zip-upload pa en ren WordPress-installation
- [ ] 5 slide-mallar med visuella layouts (Fullscreen Image, Split, Text Only, Video, Title Card)
- [ ] Slide-editor med live preview bredvid falten
- [ ] 7 animationspresets (5 CSS + 2 GSAP)
- [ ] Slides grupperas i kanaler (slingor)
- [ ] Upp till 5 skarmar konfigureras med tilldelad kanal
- [ ] Konfigurerbar upplosning (default + per skarm override)
- [ ] Varje skarm har en unik display-URL
- [ ] Fullscreen player renderar slides med ratt mall, animation och font
- [ ] Skalad admin-preview fungerar pa laptop
- [ ] Custom fonts (bundlade Google Fonts + upload) kan valjas
- [ ] REST API returnerar korrekt JSON for kanaler, slides och skarmar
- [ ] Alpine.js (~15KB) + GSAP (~30KB) ar enda externa beroenden, bundlade
- [ ] Koden foljer WordPress Coding Standards (WPCS)
- [ ] Alla user inputs saniteras och escapas korrekt
- [ ] Nonce-verifiering pa alla admin-formuler

---

## Utanfor scope (parkerade for kommande sprints)

| Feature | Sprint |
|---------|--------|
| Schemalaggerning (visa slide X kl 08-17) | Sprint 2 |
| Remote management (hantera flera skarmar fran central panel) | Sprint 2 |
| Offline-stod (Service Worker / PWA) | Sprint 3 |
| Drag-and-drop slide-ordning i admin | Sprint 2 |
| Anvandarroller (signage editor) | Sprint 3 |
| Multi-zone layouts (delad skarm) | Sprint 3 |
| Fler mallar (skapa via UI) | Sprint 2 |
| Fler animationer | Sprint 2 |
| Analytics (vilka slides visades nar) | Sprint 3 |
| Bakgrundsmusik/ljud | Sprint 3 |
| QR-kod-overlay | Sprint 2 |
| Vader/klocka-widgets | Sprint 2 |

---

## Tekniska beslut

| Beslut | Val | Motivering |
|--------|-----|------------|
| Animationsbibliotek | GSAP (core) + CSS | CSS for enkla transitions, GSAP for avancerade (typewriter, stagger). ~30KB. |
| Admin UI | Alpine.js (~15KB) | Reaktiv editor utan build-steg. x-model, x-show, x-effect. |
| JS i ovrigt | Vanilla JS | Minimalt fotavtryck, inga build-steg |
| CSS | Vanilla CSS | Enkla layouts, inga komplexa komponent-system |
| Mall-system | PHP templates + CSS klasser | Enkelt att lagga till fler mallar, ingen build-pipeline |
| Meta-falt | register_post_meta + custom meta box | Fullstandig kontroll over editor-UX med live preview |
| Editor-approach | Custom meta box (ej Gutenberg blocks) | MVP — enklare, mer kontroll over layout med preview |
| Preview-teknik | iframe + postMessage + transform:scale | Pixelperfekt preview, isolerad rendering, fungerar pa alla storlekar |
| Template-approach | Custom rewrite rules | Rent URL-format for skarmar |
| REST namespace | agoodsign/v1 | Tydligt, versionerat |
| Video-hantering | URL-baserad (direkt + YouTube/Vimeo) | MVP-enkelt, brett stod |
| Bildkalla | Featured Image + WP Media Library | Standard WP-flode |
| Fonter | Lokalt bundlade WOFF2 + upload | GDPR-vanligt, inga externa anrop |

---

## Risker

| Risk | Sannolikhet | Konsekvens | Atgard |
|------|-------------|------------|--------|
| Video autoplay blockeras av browser | Hog | Forsta videon spelas inte | `muted autoplay playsinline` attribut |
| GSAP-licens (kommersiell anvandning) | Medel | Licensavgift 99$/ar | GSAP ar gratis for vanliga sajter, betallicens kravs bara for SaaS-produkter |
| iframe postMessage-kommunikation | Medel | Preview uppdateras inte | Noggrant origin-check, fallback via URL reload |
| CORS vid headless-anvandning | Medel | REST API gar inte att anropa | Dokumentera CORS-headers |
| Stora bilder gor transitions hackiga | Medel | Dalig UX pa skyltar | Preload + bildoptimering |
| Live preview performance i admin | Lag | Editor kanns seg | Debounce 300ms, lazy iframe load |
| WP-uppdatering bryter plugin | Lag | Plugin slutar fungera | Folj WPCS, undvik deprecated APIs |

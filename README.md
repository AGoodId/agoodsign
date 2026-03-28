# AGoodSign

**Lightweight digital signage for WordPress.** Create slides, organize them in channels, and display them on screens — all from your WordPress admin.

AGoodSign replaces complex signage setups with a single, focused plugin. No subscriptions, no external services, no bloat. Just WordPress doing what it does best: managing your content.

---

## Why AGoodSign?

Most digital signage solutions are either overpriced SaaS platforms or cobbled together from slider plugins never designed for the job. AGoodSign is purpose-built for signage:

- **Runs on your own WordPress site** — no monthly fees, no vendor lock-in
- **Built for unattended screens** — auto-refresh, wake lock, hidden cursor, disabled context menu
- **Template-based editor** — choose a layout, fill in the fields, done
- **Live preview** — see exactly what your screen will display as you edit
- **Multi-screen support** — up to 5 screens, each with its own channel and resolution
- **Lightweight** — vanilla CSS + JS, Alpine.js for the editor, GSAP for animations. No jQuery, no React, no build step

---

## Features

### 6 Slide Templates

| Template | Description |
|----------|-------------|
| **Fullscreen Image** | Full-bleed background image with text overlay (top, center, or bottom) |
| **Split** | 50/50 layout — image on one side, text on the other |
| **Text Only** | Centered text on a solid background with decorative divider |
| **Video** | Fullscreen video from YouTube, Vimeo, or direct MP4 files |
| **Title Card** | Large heading with subtitle, optional background image with darkening overlay |
| **Image + Text** | Flexible image with text — configurable size, position, and border radius |

Every template supports custom heading/body text, font size overrides, text color, text alignment, and background color.

### 7 Entrance Animations

Five CSS-powered animations for instant, GPU-accelerated performance:

- **Fade In** — smooth opacity transition
- **Slide Up** — rises from below
- **Slide Left** — enters from the right
- **Zoom In** — scales up from 80%
- **Ken Burns** — slow zoom + pan on background images (duration matches slide timing)

Two GSAP-powered animations for advanced effects:

- **Typewriter** — character-by-character text reveal
- **Stagger** — sequential element reveal with motion

### Pin Markers

Place interactive markers anywhere on a slide:

- 150+ Lucide icons with search and preview
- Configurable color, size (16–128px), and position (percentage-based)
- Optional text label
- Four animations: pulse, bounce, glow, or static

Perfect for wayfinding, floor plans, and interactive maps.

### Font System

- **25 curated Google Fonts** — Inter, Montserrat, Playfair Display, Bebas Neue, and more
- **Custom font upload** — WOFF2, WOFF, and TTF supported
- Separate heading and body font selection
- Per-slide font size overrides with range sliders
- Fonts loaded via Google Fonts CDN with `font-display: swap` for zero FOIT

### Multi-Screen Management

- Configure up to 5 independent screens
- Each screen gets its own channel (slide playlist) and resolution
- Resolution presets: Portrait FHD (1080x1920), Landscape FHD (1920x1080), Portrait 4K, Landscape 4K — or enter any custom resolution
- Each screen has a unique URL: `/signage/screen/1/`, `/signage/screen/2/`, etc.

### Channel Editor

Drag-and-drop interface for organizing slides within channels:

- Reorder slides with drag handles
- Edit duration and animation inline — changes save automatically
- Quick access to the full slide editor
- Visual slide count per channel

### Signage-Hardened Player

The fullscreen player is built for 24/7 unattended operation:

- **Auto-refresh** — polls for content changes every 60 seconds, reloads automatically when you update slides
- **Screen Wake Lock** — prevents the display from sleeping (Screen Wake Lock API)
- **Hidden cursor** — no mouse pointer visible on screen
- **Context menu disabled** — no right-click menu on signage screens
- **Text selection disabled** — no accidental selections
- **Visibility API** — pauses rotation when the browser tab is hidden, resumes when visible
- **Media preloading** — preloads the next slide's images and video for seamless transitions
- **Video end detection** — video slides advance when the video finishes, not on a fixed timer
- **Error recovery** — polling backs off to 5-minute intervals after 10 consecutive failures

### Keyboard & Remote Control

- **Arrow keys** — previous/next slide
- **Spacebar** — pause/play
- **postMessage API** — control the player from a parent iframe (used by the preview modal)

### REST API

All content is accessible via the WordPress REST API at `agoodsign/v1`:

| Endpoint | Description |
|----------|-------------|
| `GET /channels` | List all channels |
| `GET /channels/{slug}/slides` | Get slides for a channel |
| `GET /screens` | List all screens with display URLs |
| `GET /screens/{id}` | Get a screen with its slides |
| `GET /screens/{id}/hash` | Lightweight hash for change detection |

All endpoints are public (no authentication required) — designed for signage players that may not have WordPress cookies.

---

## Installation

### Requirements

- WordPress 6.0 or later
- PHP 7.4 or later

### Install from GitHub Release

1. Go to [Releases](https://github.com/AGoodId/agoodsign/releases) and download the latest `agoodsign-x.x.x.zip`
2. In WordPress admin, go to **Plugins > Add New > Upload Plugin**
3. Upload the zip file and click **Install Now**
4. Activate the plugin

After activation, you'll see **AGoodSign** in the admin menu with five sections: Slides, Channels, Channel Editor, Screens, and Settings.

### Auto-Updates

AGoodSign checks GitHub for new releases automatically. When an update is available, it appears in your WordPress dashboard just like any other plugin update.

---

## Getting Started

### 1. Choose Your Fonts (Optional)

Go to **AGoodSign > Settings** and select a heading font and body font. You can also upload custom fonts here.

### 2. Create a Channel

Go to **AGoodSign > Channels** and create a channel (e.g., "Lobby", "Restaurant", "Event").

### 3. Create Slides

Go to **AGoodSign > Slides > Add New**:

1. Give the slide a title (for your reference — not displayed on screen)
2. Pick a template
3. Fill in the fields — heading, body text, image, colors
4. Set duration (how long the slide is shown)
5. Choose an entrance animation
6. Assign the slide to a channel
7. The live preview on the right updates as you type

### 4. Arrange Slides

Go to **AGoodSign > Channel Editor** to reorder slides with drag-and-drop. You can also adjust duration and animation inline.

### 5. Set Up a Screen

Go to **AGoodSign > Screens**:

1. Name your screen (e.g., "Lobby Display")
2. Select a channel
3. Choose a resolution (or use the default)
4. Mark it as active
5. Save — the display URL appears (e.g., `/signage/screen/1/`)

### 6. Open on Your Display

Navigate to the display URL in a fullscreen browser on your signage hardware. The player takes over the entire screen.

**Tip:** On most browsers, press `F11` for fullscreen. On Chrome, you can also use `--kiosk` mode for a truly locked-down experience.

---

## Recommended Display Setup

### Browser Kiosk Mode

For production signage, run the browser in kiosk mode to prevent user interaction:

**Chrome/Chromium:**
```
chromium-browser --kiosk --noerrdialogs --disable-infobars https://yoursite.com/signage/screen/1/
```

**Firefox:**
```
firefox --kiosk https://yoursite.com/signage/screen/1/
```

### Compatible Hardware

AGoodSign works on any device with a modern web browser:

- **Samsung Smart Signage** (QM, QH, QB series) — use the built-in web browser
- **Raspberry Pi** — Chromium in kiosk mode (great budget option)
- **Android TV / Fire TV** — use Fully Kiosk Browser
- **Intel NUC / Mini PC** — Chrome or Firefox in kiosk mode
- **Any smart TV** with a web browser

### Portrait Mode

For vertical displays, set the resolution to portrait (e.g., 1080x1920) in the screen settings. The player adapts to the viewport — if your OS or display is rotated, the content fills the screen correctly.

---

## FAQ

### What happens if the internet connection is lost?

The player keeps running. Slides are loaded into the browser when the page first opens, so **the current slideshow continues to play even without internet**. The auto-refresh polling will fail silently and back off gracefully. When the connection returns, polling resumes and the player will reload if content has changed.

### Can I use it on multiple screens with different content?

Yes. Create separate channels for each screen (e.g., "Lobby", "Cafeteria"), assign slides to the appropriate channel, then configure each screen to show its channel. Each screen has its own URL.

### Does it work on portrait/vertical displays?

Yes. Set the screen resolution to portrait (e.g., 1080x1920) in **AGoodSign > Screens**. The player fills whatever viewport it's given — no special configuration needed beyond rotating the display in your OS settings.

### How do I update content on a running screen?

Just edit your slides in WordPress and save. The player polls for changes every 60 seconds and reloads automatically. No need to touch the display hardware.

### Can I use my own fonts?

Yes. Go to **AGoodSign > Settings** and upload WOFF2, WOFF, or TTF files. They'll appear alongside the built-in Google Fonts in the font selector.

### Does the screen stay awake?

AGoodSign requests a Screen Wake Lock to prevent the display from sleeping. This works in Chrome, Edge, and other Chromium-based browsers. For other browsers or as a fallback, configure your OS power settings to never sleep.

### Can I embed YouTube or Vimeo videos?

Yes. The Video template accepts YouTube URLs, Vimeo URLs, and direct video file URLs. Videos autoplay muted with loop support. You can choose between "cover" (cropped to fill) and "contain" (letterboxed) display modes.

### Is there a size limit on slides?

Up to 100 slides per channel. Each channel can be assigned to one or more screens.

### Can I control the player remotely?

Yes, via the postMessage API. The player listens for messages with `source: 'agoodsign'` and supports `prev`, `next`, and `toggle-pause` actions. The preview modal in the admin uses this to provide external navigation controls.

### Does it work without GSAP?

The five CSS animations (fade-in, slide-up, slide-left, zoom-in, ken-burns) work without GSAP. Only the typewriter and stagger animations require it. GSAP is bundled with the plugin (~30KB gzipped).

---

## Technical Details

### Stack

| Layer | Technology |
|-------|-----------|
| Server | PHP 7.4+, WordPress 6.0+ |
| Admin editor | Alpine.js 3.x (~15KB gzipped) |
| Player animations | Vanilla CSS + GSAP (~30KB gzipped) |
| Styling | Vanilla CSS — no preprocessors, no build step |
| Icons | Lucide (SVG, 150+ icons bundled) |
| Fonts | Google Fonts CDN + custom upload |
| API | WordPress REST API |
| Auto-updates | Plugin Update Checker (GitHub releases) |

### Security

- All input sanitized with WordPress sanitization functions
- All output escaped with `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`
- Nonce verification on all admin forms and AJAX requests
- Capability checks on all sensitive operations
- REST API endpoints are intentionally public (read-only, no sensitive data)

### Performance

- No external HTTP requests during page render (fonts preconnect only)
- CSS animations run on the GPU compositor thread
- Media preloading: next slide's images and video are prefetched
- Hash-based change detection: polling transfers ~50 bytes, not the full slide payload
- No jQuery dependency anywhere in the plugin

### Browser Support

Optimized for Chromium-based browsers (Chrome, Edge, Chromium). Also works in Firefox and Safari. The Screen Wake Lock API requires Chrome 84+ or Edge 84+.

---

## License

GPL-2.0-or-later. See [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).

---

Built by [Mat Singerdal](https://github.com/matsingerdal) at [A Good Id](https://github.com/AGoodId).

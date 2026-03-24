/**
 * AGoodSign Lucide Icon Registry.
 *
 * Contains SVG path data for ~80 curated icons useful for signage,
 * plus the full Lucide icon name list for search/autocomplete.
 *
 * All icons are from Lucide (https://lucide.dev) — ISC License.
 * SVG viewBox: 0 0 24 24, stroke-width: 2, stroke-linecap: round, stroke-linejoin: round.
 */
( function () {
	'use strict';

	/**
	 * Curated icons with full SVG path data.
	 * Format: { name: 'icon-name', tags: ['search', 'terms'], path: 'SVG inner content' }
	 */
	const ICONS = [
		// Navigation & Location
		{ name: 'map-pin', tags: ['location', 'place', 'marker', 'gps'], path: '<path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>' },
		{ name: 'map', tags: ['location', 'navigation', 'direction'], path: '<path d="M14.106 5.553a2 2 0 0 0 1.788 0l3.659-1.83A1 1 0 0 1 21 4.619v12.764a1 1 0 0 1-.553.894l-4.553 2.277a2 2 0 0 1-1.788 0l-4.212-2.106a2 2 0 0 0-1.788 0l-3.659 1.83A1 1 0 0 1 3 19.381V6.618a1 1 0 0 1 .553-.894l4.553-2.277a2 2 0 0 1 1.788 0z"/><path d="M15 5.764v15"/><path d="M9 3.236v15"/>' },
		{ name: 'navigation', tags: ['direction', 'arrow', 'compass'], path: '<polygon points="3 11 22 2 13 21 11 13 3 11"/>' },
		{ name: 'compass', tags: ['navigation', 'direction', 'north'], path: '<circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>' },
		{ name: 'locate', tags: ['gps', 'crosshair', 'target'], path: '<line x1="2" x2="5" y1="12" y2="12"/><line x1="19" x2="22" y1="12" y2="12"/><line x1="12" x2="12" y1="2" y2="5"/><line x1="12" x2="12" y1="19" y2="22"/><circle cx="12" cy="12" r="7"/>' },
		{ name: 'crosshair', tags: ['target', 'aim', 'scope'], path: '<circle cx="12" cy="12" r="10"/><line x1="22" x2="18" y1="12" y2="12"/><line x1="6" x2="2" y1="12" y2="12"/><line x1="12" x2="12" y1="6" y2="2"/><line x1="12" x2="12" y1="22" y2="18"/>' },
		{ name: 'move', tags: ['arrows', 'drag', 'position'], path: '<polyline points="5 9 2 12 5 15"/><polyline points="9 5 12 2 15 5"/><polyline points="15 19 12 22 9 19"/><polyline points="19 9 22 12 19 15"/><line x1="2" x2="22" y1="12" y2="12"/><line x1="12" x2="12" y1="2" y2="22"/>' },

		// Arrows
		{ name: 'arrow-up', tags: ['direction', 'up'], path: '<path d="m5 12 7-7 7 7"/><path d="M12 19V5"/>' },
		{ name: 'arrow-down', tags: ['direction', 'down'], path: '<path d="M12 5v14"/><path d="m19 12-7 7-7-7"/>' },
		{ name: 'arrow-left', tags: ['direction', 'left', 'back'], path: '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>' },
		{ name: 'arrow-right', tags: ['direction', 'right', 'forward'], path: '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>' },
		{ name: 'arrow-up-right', tags: ['direction', 'diagonal'], path: '<path d="M7 17 17 7"/><path d="M7 7h10v10"/>' },
		{ name: 'arrow-up-left', tags: ['direction', 'diagonal'], path: '<path d="M17 17 7 7"/><path d="M17 7H7v10"/>' },
		{ name: 'corner-up-right', tags: ['turn', 'direction'], path: '<polyline points="15 14 20 9 15 4"/><path d="M4 20v-7a4 4 0 0 1 4-4h12"/>' },
		{ name: 'corner-up-left', tags: ['turn', 'direction'], path: '<polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/>' },

		// Places & Buildings
		{ name: 'building', tags: ['office', 'company', 'skyscraper'], path: '<rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/>' },
		{ name: 'home', tags: ['house', 'building', 'residence'], path: '<path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>' },
		{ name: 'store', tags: ['shop', 'retail', 'market'], path: '<path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/><path d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"/>' },
		{ name: 'hotel', tags: ['bed', 'accommodation', 'sleep'], path: '<path d="M10 22v-6.57"/><path d="M12 11h.01"/><path d="M12 7h.01"/><path d="M14 15.43V22"/><path d="M15 16a5 5 0 0 0-6 0"/><path d="M16 11h.01"/><path d="M16 7h.01"/><path d="M8 11h.01"/><path d="M8 7h.01"/><rect x="4" y="2" width="16" height="20" rx="2"/>' },
		{ name: 'hospital', tags: ['medical', 'health', 'emergency'], path: '<path d="M12 6v4"/><path d="M14 14h-4"/><path d="M14 18h-4"/><path d="M14 8h-4"/><path d="M18 12h2a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-9a2 2 0 0 1 2-2h2"/><path d="M18 22V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v18"/>' },

		// Food & Drink
		{ name: 'coffee', tags: ['cafe', 'drink', 'cup', 'hot'], path: '<path d="M10 2v2"/><path d="M14 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a2 2 0 1 1 0 4h-1"/><path d="M6 2v2"/>' },
		{ name: 'utensils', tags: ['food', 'restaurant', 'dining', 'eat'], path: '<path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/>' },
		{ name: 'wine', tags: ['drink', 'alcohol', 'glass', 'bar'], path: '<path d="M8 22h8"/><path d="M7 10h10"/><path d="M12 15v7"/><path d="M12 15a5 5 0 0 0 5-5c0-2-.5-4-2-8H9c-1.5 4-2 6-2 8a5 5 0 0 0 5 5Z"/>' },
		{ name: 'pizza', tags: ['food', 'slice', 'eat'], path: '<path d="M15 11h.01"/><path d="M11 15h.01"/><path d="M16 16h.01"/><path d="m2 16 20 6-6-20A20 20 0 0 0 2 16"/><path d="M5.71 17.11a17.04 17.04 0 0 1 11.4-11.4"/>' },

		// Transport
		{ name: 'car', tags: ['vehicle', 'drive', 'parking'], path: '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/>' },
		{ name: 'bus', tags: ['transport', 'public', 'travel'], path: '<path d="M8 6v6"/><path d="M15 6v6"/><path d="M2 12h19.6"/><path d="M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2 0-.4-.1-.8-.2-1.2l-1.4-5C20.1 6.8 19.1 6 18 6H4a2 2 0 0 0-2 2v10h3"/><circle cx="7" cy="18" r="2"/><path d="M9 18h5"/><circle cx="16" cy="18" r="2"/>' },
		{ name: 'train-front', tags: ['transport', 'rail', 'subway'], path: '<path d="M8 3.1V7a4 4 0 0 0 8 0V3.1"/><path d="m9 15-1-1"/><path d="m15 15 1-1"/><path d="M9 19c-2.8 0-5-2.2-5-5v-4a8 8 0 0 1 16 0v4c0 2.8-2.2 5-5 5Z"/><path d="m8 19-2 3"/><path d="m16 19 2 3"/>' },
		{ name: 'plane', tags: ['flight', 'airport', 'travel', 'aviation'], path: '<path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/>' },
		{ name: 'parking-circle', tags: ['park', 'car', 'vehicle'], path: '<circle cx="12" cy="12" r="10"/><path d="M9 17V7h4a3 3 0 0 1 0 6H9"/>' },

		// Information & Communication
		{ name: 'info', tags: ['information', 'help', 'about'], path: '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>' },
		{ name: 'circle-help', tags: ['question', 'help', 'support'], path: '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>' },
		{ name: 'circle-alert', tags: ['warning', 'attention', 'danger'], path: '<circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>' },
		{ name: 'phone', tags: ['call', 'telephone', 'contact'], path: '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>' },
		{ name: 'mail', tags: ['email', 'message', 'contact'], path: '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>' },
		{ name: 'globe', tags: ['world', 'earth', 'international', 'web'], path: '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>' },
		{ name: 'wifi', tags: ['internet', 'connection', 'signal'], path: '<path d="M12 20h.01"/><path d="M2 8.82a15 15 0 0 1 20 0"/><path d="M5 12.859a10 10 0 0 1 14 0"/><path d="M8.5 16.429a5 5 0 0 1 7 0"/>' },
		{ name: 'qr-code', tags: ['scan', 'code', 'barcode'], path: '<rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M21 21v.01"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/><path d="M3 12h.01"/><path d="M12 3h.01"/><path d="M12 16v.01"/><path d="M16 12h1"/><path d="M21 12v.01"/><path d="M12 21v-1"/>' },

		// Amenities & Services
		{ name: 'accessibility', tags: ['wheelchair', 'disabled', 'handicap'], path: '<circle cx="16" cy="4" r="1"/><path d="m18 19 1-7-6 1"/><path d="m5 8 3-3 5.5 3-2.36 3.5"/><path d="M4.24 14.5a5 5 0 0 0 6.88 6"/><path d="M13.76 17.5a5 5 0 0 0-6.88-6"/>' },
		{ name: 'baby', tags: ['child', 'infant', 'family'], path: '<path d="M9 12h.01"/><path d="M15 12h.01"/><path d="M10 16c.5.3 1.2.5 2 .5s1.5-.2 2-.5"/><path d="M19 6.3a9 9 0 0 1 1.8 3.9 2 2 0 0 1 0 3.6 9 9 0 0 1-17.6 0 2 2 0 0 1 0-3.6A9 9 0 0 1 12 3c2 0 3.5 1.1 3.5 2.5s-.9 2.5-2 2.5c-.8 0-1.5-.4-1.5-1"/>' },
		{ name: 'shower-head', tags: ['bathroom', 'shower', 'water'], path: '<path d="m4 4 2.5 2.5"/><path d="M13.5 6.5a4.95 4.95 0 0 0-7 7"/><path d="M15 5 5 15"/><path d="M14 17v.01"/><path d="M10 16v.01"/><path d="M13 13v.01"/><path d="M16 10v.01"/><path d="M11 20v.01"/><path d="M17 14v.01"/><path d="M20 11v.01"/><path d="M14 14v.01"/>' },
		{ name: 'shirt', tags: ['clothing', 'fashion', 'shop'], path: '<path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z"/>' },

		// Symbols & UI
		{ name: 'star', tags: ['favorite', 'rating', 'bookmark'], path: '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>' },
		{ name: 'heart', tags: ['love', 'like', 'favorite'], path: '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>' },
		{ name: 'clock', tags: ['time', 'hour', 'schedule', 'watch'], path: '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>' },
		{ name: 'calendar', tags: ['date', 'schedule', 'event'], path: '<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/>' },
		{ name: 'check-circle', tags: ['success', 'done', 'complete', 'ok'], path: '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>' },
		{ name: 'x-circle', tags: ['close', 'error', 'cancel', 'no'], path: '<circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/>' },
		{ name: 'ban', tags: ['prohibited', 'forbidden', 'no'], path: '<circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/>' },
		{ name: 'eye', tags: ['view', 'visible', 'look', 'watch'], path: '<path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/>' },
		{ name: 'zap', tags: ['lightning', 'energy', 'power', 'electric'], path: '<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"/>' },
		{ name: 'flame', tags: ['fire', 'hot', 'trending'], path: '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>' },
		{ name: 'gift', tags: ['present', 'reward', 'promotion'], path: '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13"/><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"/>' },
		{ name: 'percent', tags: ['discount', 'sale', 'offer'], path: '<line x1="19" x2="5" y1="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>' },
		{ name: 'tag', tags: ['label', 'price', 'category'], path: '<path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/>' },
		{ name: 'door-open', tags: ['entrance', 'exit', 'entry'], path: '<path d="M13 4h3a2 2 0 0 1 2 2v14"/><path d="M2 20h3"/><path d="M13 20h9"/><path d="M10 12v.01"/><path d="M13 4.562v16.157a1 1 0 0 1-1.242.97L5 20V5.562a2 2 0 0 1 1.515-1.94l4-1A2 2 0 0 1 13 4.561Z"/>' },
		{ name: 'toilet', tags: ['bathroom', 'wc', 'restroom', 'lavatory'], path: '<path d="M7 12h13a1 1 0 0 1 .928 1.372L18 22H6l-1-5"/><path d="M3.42 11.145A1.993 1.993 0 0 1 2 9.19V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v5.19a1.993 1.993 0 0 1-1.42 1.955L7 12"/>' },
		{ name: 'cigarette-off', tags: ['no smoking', 'prohibited'], path: '<line x1="2" x2="22" y1="2" y2="22"/><path d="M12 12H2v4h14"/><path d="M22 12v4"/><path d="M18 12h-.5"/><path d="M7 12v4"/><path d="M18 8c0-2.5-2-2.5-2-5"/><path d="M22 8c0-2.5-2-2.5-2-5"/>' },
		{ name: 'volume-2', tags: ['sound', 'audio', 'speaker'], path: '<polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/>' },

		// Outdoor & Weather
		{ name: 'sun', tags: ['weather', 'bright', 'day', 'light'], path: '<circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>' },
		{ name: 'cloud', tags: ['weather', 'sky'], path: '<path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/>' },
		{ name: 'umbrella', tags: ['rain', 'weather', 'protection'], path: '<path d="M22 12a10.06 10.06 0 0 0-20 0Z"/><path d="M12 12v8a2 2 0 0 0 4 0"/><path d="M12 2v1"/>' },
		{ name: 'tree-pine', tags: ['forest', 'nature', 'park', 'outdoor'], path: '<path d="m17 14 3 3.3a1 1 0 0 1-.7 1.7H4.7a1 1 0 0 1-.7-1.7L7 14l-3-3.3a1 1 0 0 1 .7-1.7h3.6L7 7.3a1 1 0 0 1 .7-1.7h8.6a1 1 0 0 1 .7 1.7L16 9h3.3a1 1 0 0 1 .7 1.7z"/><path d="M12 22v-3"/>' },
		{ name: 'mountain', tags: ['outdoor', 'nature', 'hiking'], path: '<path d="m8 3 4 8 5-5 5 15H2L8 3z"/><path d="M4.14 15.08c2.62-1.57 5.24-1.43 7.86.42 2.74 1.94 5.49 2 8.23.19"/>' },

		// Misc useful
		{ name: 'camera', tags: ['photo', 'picture', 'image'], path: '<path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/>' },
		{ name: 'music', tags: ['audio', 'song', 'sound'], path: '<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>' },
		{ name: 'trophy', tags: ['award', 'winner', 'prize', 'champion'], path: '<path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>' },
		{ name: 'users', tags: ['people', 'group', 'team'], path: '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>' },
		{ name: 'shield', tags: ['security', 'safety', 'protection'], path: '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>' },
		{ name: 'thumbs-up', tags: ['like', 'good', 'approve'], path: '<path d="M7 10v12"/><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"/>' },
		{ name: 'circle-parking', tags: ['parking', 'car', 'vehicle', 'p'], path: '<circle cx="12" cy="12" r="10"/><path d="M9 17V7h4a3 3 0 0 1 0 6H9"/>' },
	];

	/**
	 * Build an SVG string for an icon.
	 *
	 * @param {string} iconName  Icon name (slug).
	 * @param {number} size      Size in px.
	 * @param {string} color     Color (hex or CSS value).
	 * @return {string} SVG HTML string, or empty string if not found.
	 */
	function renderIcon( iconName, size, color ) {
		const icon = ICONS.find( i => i.name === iconName );
		if ( ! icon ) return '';

		return '<svg xmlns="http://www.w3.org/2000/svg" width="' + size + '" height="' + size +
			'" viewBox="0 0 24 24" fill="none" stroke="' + color +
			'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
			icon.path + '</svg>';
	}

	/**
	 * Search icons by name or tags.
	 *
	 * @param {string} query Search string.
	 * @return {Array} Matching icon objects.
	 */
	function searchIcons( query ) {
		if ( ! query || query.length < 2 ) return ICONS;

		const q = query.toLowerCase();
		return ICONS.filter( icon => {
			if ( icon.name.includes( q ) ) return true;
			return icon.tags.some( tag => tag.includes( q ) );
		} );
	}

	window.AGoodSignIcons = {
		icons: ICONS,
		render: renderIcon,
		search: searchIcons,
	};
} )();

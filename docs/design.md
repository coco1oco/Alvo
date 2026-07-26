# Alvo Design System

> **Single source of truth** for all UI components, styling, and interaction patterns.
> Every Vue component and CSS rule must follow this document.

---

## 1. Brand Identity

- **App Name:** Alvo
- **Tagline:** Aim. Save. Achieve.
- **Personality:** Professional, airy, trustworthy — inspired by Monzo and Revolut. Clean like a modern bank, not intimidating like a spreadsheet.

---

## 2. Color Palette

The palette supports both **Light Mode** (default-by-system) and **Dark Mode** via a `.dark` class on the `<html>` element. Tailwind's `dark:` variant is driven by `darkMode: 'class'`.

### 2.1 Semantic Tokens

All components **must** use these CSS custom property tokens — never raw hex or Tailwind color literals.

```css
:root {
  /* Backgrounds */
  --bg-base:        #F5F7FA;   /* Page background — pearl white */
  --bg-surface:     #FFFFFF;   /* Card/panel surface */
  --bg-surface-2:   #EEF1F8;   /* Nested surface (e.g., table rows) */
  --bg-glass:       rgba(255, 255, 255, 0.65); /* Glassmorphism panels */

  /* Borders */
  --border:         rgba(0, 0, 0, 0.08);
  --border-strong:  rgba(0, 0, 0, 0.16);

  /* Text */
  --text-primary:   #0F1923;   /* Headlines, key data */
  --text-secondary: #5A6478;   /* Labels, subtitles */
  --text-muted:     #9AA3B2;   /* Placeholders, disabled */

  /* Primary — Sapphire */
  --primary:        #1A56DB;   /* Buttons, active nav, links */
  --primary-hover:  #1447C7;
  --primary-light:  #EBF0FD;   /* Tinted backgrounds */
  --primary-glass:  rgba(26, 86, 219, 0.12);

  /* Semantic Colors */
  --success:        #12A179;   /* Positive balance, income */
  --success-light:  #E6F7F3;
  --danger:         #E03131;   /* Negative, expenses */
  --danger-light:   #FDECEC;
  --warning:        #E8890C;   /* Budget warnings */
  --warning-light:  #FEF3DC;

  /* Shadows */
  --shadow-sm:      0 1px 3px rgba(0,0,0,0.07), 0 1px 2px rgba(0,0,0,0.04);
  --shadow-md:      0 4px 16px rgba(0,0,0,0.08), 0 1px 4px rgba(0,0,0,0.05);
  --shadow-glass:   0 8px 32px rgba(26, 86, 219, 0.10);
}

.dark {
  /* Backgrounds */
  --bg-base:        #050505;
  --bg-surface:     #111111;
  --bg-surface-2:   #1A1A1A;
  --bg-glass:       rgba(17, 17, 17, 0.75);

  /* Borders */
  --border:         rgba(255, 255, 255, 0.08);
  --border-strong:  rgba(255, 255, 255, 0.16);

  /* Text */
  --text-primary:   #EDEDED;
  --text-secondary: #A1A1AA;
  --text-muted:     #52525B;

  /* Primary — Blue */
  --primary:        #3B82F6;
  --primary-hover:  #60A5FA;
  --primary-light:  rgba(59, 130, 246, 0.15);
  --primary-glass:  rgba(59, 130, 246, 0.10);

  /* Semantic Colors */
  --success:        #34D399;
  --success-light:  rgba(52, 211, 153, 0.12);
  --danger:         #F87171;
  --danger-light:   rgba(248, 113, 113, 0.12);
  --warning:        #FBB03B;
  --warning-light:  rgba(251, 176, 59, 0.12);

  /* Shadows */
  --shadow-sm:      0 1px 3px rgba(0,0,0,0.5);
  --shadow-md:      0 4px 16px rgba(0,0,0,0.6);
  --shadow-glass:   0 8px 32px rgba(0, 0, 0, 0.5);
}
```

### 2.2 Quick Reference

| Token | Light | Dark | Usage |
|---|---|---|---|
| `--bg-base` | Pearl White `#F5F7FA` | Onyx `#050505` | Page background |
| `--bg-surface` | White `#FFFFFF` | Graphite `#111111` | Cards & panels |
| `--primary` | Sapphire `#1A56DB` | Blue `#3B82F6` | CTAs, active states |
| `--success` | Emerald `#12A179` | Mint `#34D399` | Income, positive |
| `--danger` | Red `#E03131` | Soft Red `#F87171` | Expenses, alerts |

---

## 3. Typography

**Font Family:** [Geist](https://vercel.com/font) — clean, geometric, minimal.

```css
@theme {
  --font-sans: 'Geist', 'Inter', ui-sans-serif, system-ui, sans-serif;
  --font-mono: 'Geist Mono', 'Fira Code', ui-monospace, monospace;
}
```

### Type Scale

| Role | Size | Weight | Usage |
|---|---|---|---|
| Page Title | `1.5rem` (24px) | 700 | `<h1>` in each view |
| Section Title | `1.125rem` (18px) | 600 | Card titles |
| Body | `0.875rem` (14px) | 400 | General content |
| Label | `0.75rem` (12px) | 600 | Form labels, tags |
| Micro | `0.6875rem` (11px) | 500 | Timestamps, metadata |

### Number Formatting

- Use `font-variant-numeric: tabular-nums` for all financial values.
- Always show 2 decimal places for currency.
- Color by sign: `var(--success)` for positive, `var(--danger)` for negative.

---

## 4. Layout & Spacing

### App Shell

```
┌──────────────────────────────────────────────┐
│  Sidebar (240px fixed)  │  Main Content      │
│                         │  (flex-1, scroll)  │
│  [Logo]                 │  [View Header]     │
│  [Nav Items]            │  [Content Area]    │
│                         │                    │
│  [Theme Toggle]         │                    │
│  [User + Logout]        │                    │
└──────────────────────────────────────────────┘
```

- Sidebar: `240px` wide, `var(--bg-surface)` background, `backdrop-filter: blur(12px)` on dark.
- Main content: `padding: 2rem`. Scrollable independently.
- Max content width: `1280px`, centered with `margin: 0 auto` for very wide screens.

### Spacing Scale

Use multiples of 4px. Tailwind shorthand: `p-1`=4px, `p-2`=8px, `p-3`=12px, `p-4`=16px, `p-6`=24px, `p-8`=32px.

---

## 5. Components

### 5.1 Glassmorphism Card (`.glass-card`)

The primary container for all dashboard widgets and content panels.

```css
.glass-card {
  background: var(--bg-glass);
  backdrop-filter: blur(16px) saturate(180%);
  -webkit-backdrop-filter: blur(16px) saturate(180%);
  border: 1px solid var(--border);
  border-radius: 1.25rem;
  box-shadow: var(--shadow-glass);
  padding: 1.5rem;
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}
.glass-card:hover {
  box-shadow: var(--shadow-md), 0 0 0 1px var(--border-strong);
  transform: translateY(-2px);
}
```

### 5.2 Buttons

#### Primary Button (`.btn-primary`)

```css
.btn-primary {
  background: var(--primary);
  color: #fff;
  font-size: 0.875rem;
  font-weight: 600;
  padding: 0.625rem 1.25rem;
  border-radius: 0.75rem;
  border: none;
  cursor: pointer;
  transition: background 0.15s, transform 0.15s, box-shadow 0.15s;
  box-shadow: 0 2px 8px var(--primary-glass);
}
.btn-primary:hover {
  background: var(--primary-hover);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px var(--primary-glass);
}
.btn-primary:active { transform: translateY(0); }
```

#### Ghost Button (`.btn-ghost`)

```css
.btn-ghost {
  background: transparent;
  color: var(--text-secondary);
  border: 1px solid var(--border-strong);
  font-size: 0.875rem;
  font-weight: 500;
  padding: 0.625rem 1.25rem;
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all 0.15s;
}
.btn-ghost:hover {
  background: var(--bg-surface-2);
  color: var(--text-primary);
}
```

#### Danger Button (`.btn-danger`)

Same structure as `.btn-primary`, but uses `var(--danger)` as background.

### 5.3 Form Inputs (`.input-field`)

```css
.input-field {
  width: 100%;
  background: var(--bg-surface);
  border: 1px solid var(--border-strong);
  border-radius: 0.75rem;
  padding: 0.625rem 0.875rem;
  font-size: 0.875rem;
  color: var(--text-primary);
  font-family: var(--font-sans);
  transition: border-color 0.15s, box-shadow 0.15s;
}
.input-field::placeholder { color: var(--text-muted); }
.input-field:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px var(--primary-glass);
}
```

### 5.4 Labels (`.label`)

```css
.label {
  display: block;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: 0.375rem;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}
```

### 5.5 Stat Cards (Dashboard KPI cards)

- Background: `var(--bg-glass)` with glassmorphism
- Title: `var(--text-secondary)`, 12px, uppercase, 600 weight
- Value: `var(--text-primary)`, 28px, 700 weight, `tabular-nums`
- Trend chip: small pill badge using `--success` or `--danger`
- Icon: 40×40 rounded square with `var(--primary-light)` background

### 5.6 Modal (`.modal-overlay` / `.modal-panel`)

```css
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
}

.modal-panel {
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: 1.5rem;
  box-shadow: var(--shadow-md);
  padding: 2rem;
  width: 100%;
  max-width: 480px;
  animation: slide-up 0.25s cubic-bezier(0.22, 1, 0.36, 1);
}

@keyframes slide-up {
  from { opacity: 0; transform: translateY(24px) scale(0.97); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}
```

### 5.7 Navigation (Sidebar)

- **Active item:** `var(--primary-glass)` bg, `var(--primary)` text, 2px left border in `var(--primary)`
- **Hover item:** `var(--bg-surface-2)` bg, `var(--text-primary)` text
- **Icons:** Heroicons SVGs (outline style default, solid when active), 18×18px
- **Sidebar width:** 240px fixed

### 5.8 Toast Notifications

- Position: `fixed bottom-6 right-6`, stacked vertically, 8px gap
- Animation: slide in from right, fade out to right

| Type | Background | Border | Text |
|---|---|---|---|
| Success | `--success-light` | `--success` | `--success` |
| Error | `--danger-light` | `--danger` | `--danger` |

### 5.9 Badge / Tag (`.badge`)

```css
.badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.2rem 0.625rem;
  border-radius: 9999px;
  font-size: 0.6875rem;
  font-weight: 600;
}
.badge-success { background: var(--success-light); color: var(--success); }
.badge-danger  { background: var(--danger-light);  color: var(--danger);  }
.badge-primary { background: var(--primary-light); color: var(--primary); }
.badge-warning { background: var(--warning-light); color: var(--warning); }
```

---

## 6. Motion & Animation

**Principle:** Purposeful and subtle. Animations communicate state changes, not entertain.

| Interaction | Duration | Easing |
|---|---|---|
| Hover state | `150ms` | `ease` |
| Card hover lift | `200ms` | `ease` |
| Modal open | `250ms` | `cubic-bezier(0.22, 1, 0.36, 1)` |
| Page fade-in | `300ms` | `ease-out` |
| Toast slide-in | `300ms` | `cubic-bezier(0.22, 1, 0.36, 1)` |

### Page Transitions

```css
.page-enter-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.page-enter-from   { opacity: 0; transform: translateY(8px); }
```

### Reduced Motion

```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
```

---

## 7. Dark Mode Strategy

Controlled by the `.dark` class on `<html>`. Stored in `localStorage` as `alvo-theme`.

### Logic (App.vue)

```js
const savedTheme = localStorage.getItem('alvo-theme')
const prefersDark = window.matchMedia('(prefers-color-scheme: dark)')
const isDark = ref(savedTheme ? savedTheme === 'dark' : prefersDark.matches)

watch(isDark, val => {
  document.documentElement.classList.toggle('dark', val)
  localStorage.setItem('alvo-theme', val ? 'dark' : 'light')
}, { immediate: true })

// React to OS changes only if user hasn't manually chosen
if (!savedTheme) {
  prefersDark.addEventListener('change', e => { isDark.value = e.matches })
}

function toggleTheme() { isDark.value = !isDark.value }
```

### Toggle Button

- Placed in the sidebar footer, beside the user avatar row
- Sun icon (☀) in dark mode, Moon icon (🌙) in light mode — use Heroicons SVGs
- No text label, use a tooltip

---

## 8. Iconography

- **Library:** [Heroicons](https://heroicons.com/) — outline by default, solid for active nav items
- **Sizes:** 18×18 in nav, 20×20 in cards/buttons, 24×24 in empty states
- **Color:** `currentColor` — inherits from text context
- **Rule:** No emoji in the final UI. Replace all current emoji nav icons with Heroicons SVGs.

---

## 9. Tailwind v4 Configuration

In `app.css`, add the dark mode custom variant:

```css
@custom-variant dark (&:where(.dark, .dark *));
```

---

## 10. Future: Landing Page (Post-MVP)

Uses this same design system. Additional considerations:

- Hero: animated gradient mesh, large headline, product screenshot
- Feature sections: alternating text + illustration layout
- CTA buttons: larger padding (`0.875rem 2rem`)
- More expressive scroll-triggered entry animations
- No new color tokens or fonts needed — fully consistent with app

---

## 11. Do's and Don'ts

| ✅ Do | ❌ Don't |
|---|---|
| Use CSS custom property tokens | Use raw hex colors or Tailwind literals |
| Use Geist via `var(--font-sans)` | Import other font families |
| Use `.glass-card` for all content panels | Use plain divs with ad-hoc background styles |
| Use Heroicons SVGs | Use emoji as icons in the final UI |
| Use `--success` / `--danger` for amount colors | Use green/red Tailwind classes directly |
| Respect `prefers-reduced-motion` | Add animations without a reduced-motion fallback |
| Toggle dark mode via `.dark` class | Use `@media (prefers-color-scheme)` directly in CSS |

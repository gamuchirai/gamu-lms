# Gamuchirai Dzidza LMS — Color Scheme

This document summarizes the color palette and where each color is used across the web application (buttons, texts, backgrounds, hover states and other UI elements). Colors are taken from the current CSS files (notably `assets/css/style.css`, `assets/css/table.css`, and `assets/css/dashboard.css`).

## Primary / Brand
- Primary purple: #5D215F
  - Used for primary buttons, accents, focus outlines and many brand elements (button background, `button` default, `.task-checkbox` accent, `.section-tab.active` border, etc.).
- Primary hover/darker: #6B1F52
  - Hover state for primary buttons.
- Primary active/pressed: #4A1B4D
  - Active state for primary buttons.

## Accent / Call-to-action
- Accent orange: #FF5E0F
  - Links (`.register-form p a`), some icon gradients and course icon backgrounds.
- Accent orange (secondary): #FF9F40
  - Used for highlights in cards and progress bars.
- Accent orange hover: #E64D03
  - Link hover state.

## Success / Positive
- Success green (buttons/outline): #10B981
- Success badge text: #065F46
- Success background (badge): rgba(209,250,229,1) / #d1fae5

## Warning / Info
- Warning / info (cards/labels): #FF9F40 (see above)

## Danger / Error
- Primary error / delete accent: #EF4444
- Danger hover / darker: #DC2626
- Error tag background: #ffe5e5 (light)

## Neutral / Text
- Very dark / main text: #1a1a1a and #111827 (used across dashboard and form text)
- Body text / secondary: #374151
- Muted / helper text: #6b7280 and #999 (placeholders and small meta text)

## Backgrounds and Surfaces
- App main gradient overlay (login/main backgrounds):
  - Layered gradient using rgba(93, 33, 95, 0.85) and rgba(107,31,82,0.85)
  - Example CSS: linear-gradient(135deg, rgba(93,33,95,0.85) 0%, rgba(107,31,82,0.85) 50%, rgba(93,33,95,0.9) 100%)
- Card / surface background: #ffffff (white)
- Page/content inner background (forms/inputs): #f9fafb (inputs, subtle surfaces)
- Neutral border: #e5e7eb
- Light divider / table row: #f3f4f6

## Action buttons and controls
- Primary button background: #5D215F
  - Hover: #6B1F52
  - Active: #4A1B4D
  - Text on primary buttons: #FFFFFF
- Secondary / icon action borders: default border color #d1d5db with hover tints
- Small circular action icons use colored outlines with transparent/very light hover backgrounds:
  - Edit: border & icon #3b82f6; hover bg rgba(37,99,235,0.06)
  - Suspend (warning): border & icon #f59e0b; hover bg rgba(217,119,6,0.06)
  - Activate (success): border & icon #10b981; hover bg rgba(5,150,105,0.06)
  - Delete (danger): border & icon #ef4444; hover bg rgba(220,38,38,0.06)

## Badges
- Active badge background: #d1fae5; text: #065f46 (uppercase, bold)
- Suspended badge background: #fee2e2; text: #991b1b (uppercase, bold)

## Topbar / Sidebar accents
- Sidebar/active nav gradient: linear-gradient(135deg, #5D215F 0%, #7a2e78 100%)
- Topbar / small icons background: #f5f5f5 (hover: #efefef)

## Modals and overlays
- Modal overlay: rgba(0,0,0,0.5)
- Modal content background: #ffffff
- Modal header border / separators: #e5e7eb

## Tooltips and floating UI
- Tooltip background: rgba(17,24,39,0.95) (very dark gray/near-black)
- Tooltip text: #ffffff

## Shadows
- Card/drop shadow (example): 0 20px 60px rgba(0,0,0,0.4) (used for center forms)
- Subtle UI shadows: 0 4px 16px rgba(0,0,0,0.06) or 0 8px 24px rgba(0,0,0,0.08)

## Form elements
- Input background: #f9fafb
- Input border: #e5e7eb
- Input focus outline and accent: rgba(93,33,95,0.1) (focus ring uses brand purple at low opacity)

## Pagination and controls
- Pagination buttons: background #fff; border #e6e7eb; hover background #f8fafc; active/selected background #3b82f6 with white text

## Notes & where to look in code
- Primary styles are defined in `assets/css/style.css` and `assets/css/login.css` (buttons, inputs, brand background).
- Dashboard and admin UI colors are in `assets/css/dashboard.css` (sidebar gradients, topbar, cards).
- Table, action buttons, badges and pagination styles are in `assets/css/table.css`.

If you want, I can create a small visual swatch file (PNG or HTML preview) that shows the colors side-by-side, or extract the palette into a JSON or SCSS variables file for easier reuse. Which would you prefer?

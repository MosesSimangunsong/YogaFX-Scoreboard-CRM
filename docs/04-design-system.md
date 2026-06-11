# YogaFX Scoreboard Design System
# Visual Standardization for Public and Admin UI

## 1. Document Purpose

Dokumen ini mendefinisikan **design system tingkat produk** untuk YogaFX Scoreboard sebagai standar visual dan implementasi UI yang konsisten.

Dokumen ini ditujukan untuk:

* founder
* designer
* developer
* frontend implementer

Dokumen ini mencakup dua area utama:

* **public participant experience**
* **admin/backoffice experience**

Design system ini harus:

* menjaga continuity dengan ekosistem visual YogaFX
* terasa lebih product-focused dibanding LMS yang ada
* cukup implementable untuk frontend
* mendukung pengalaman yang premium, terpercaya, dan conversion-focused

---

## 2. Design System Intent

YogaFX Scoreboard adalah produk standalone, tetapi visual language-nya tidak perlu terputus dari YogaFX LMS.

Arahan utamanya:

* public flow harus terasa immersive, premium, dark-first, dan conversion-oriented
* admin flow harus terasa rapi, netral, terstruktur, dan efisien
* keduanya harus tetap berada dalam satu keluarga visual
* sistem tidak boleh terasa seperti SaaS template generik

Karakter visual yang menjadi dasar:

* clean
* premium
* modern
* minimal
* high-trust
* conversion-focused
* immersive
* structured
* slightly bold
* wellness-tech

---

## 3. Experience Direction

### 3.1 Public Participant Experience

Rasa yang diinginkan:

* seperti productized version dari nuansa Netflix-style YogaFX LMS
* immersive tetapi tetap ringan untuk quiz flow
* CTA terasa jelas dan meyakinkan
* fokus pada momentum dari lead form ke result

Public UI harus menonjolkan:

* atmosphere
* visual depth
* trust
* motion yang terkontrol
* readability tinggi pada dark surfaces

### 3.2 Admin Experience

Rasa yang diinginkan:

* konsisten dengan dashboard style YogaFX LMS
* shadcn-based dan implementable
* clean, structured, data-focused
* lebih utilitarian dibanding public flow

Admin UI harus menonjolkan:

* clarity
* density yang terukur
* hierarchy yang stabil
* predictable interaction patterns

---

## 4. Brand and Visual Positioning

Relationship terhadap YogaFX brand:

* inspired by existing YogaFX brand
* lebih rapi dan sistematis
* lebih siap untuk product UI
* masih membawa kesan premium dan immersive

Art direction summary:

* public side: dark cinematic wellness-tech
* admin side: neutral product dashboard with restrained brand accents

Yang harus dihindari:

* flat putih polos dengan aksen ungu generik
* layout SaaS template yang interchangeable
* terlalu banyak ornamen spiritual/dekoratif
* typography yang terlalu “luxury editorial” hingga menurunkan readability

---

## 5. Visual Foundations

### 5.1 Color Philosophy

Sistem warna harus memakai **semantic color roles**, bukan sekadar daftar warna mentah.

Strategi umumnya:

* public memakai dark-first palette
* CTA memakai accent yang kuat dan jelas
* admin memakai neutral surface dengan identitas brand yang lebih ringan

### 5.2 Recommended Color Palette

#### Core Brand Roles

* `brand.primary`: deep ember-red or warm crimson for primary CTA and key emphasis
* `brand.secondary`: muted copper or warm amber for supporting highlights
* `brand.neutral`: slate-charcoal family for dark backgrounds and neutral surfaces
* `brand.soft`: fog-gray / stone-gray for muted text and soft dividers

#### Public Mood Roles

* `public.bg.canvas`: near-black charcoal
* `public.bg.elevated`: graphite or deep slate
* `public.bg.panel`: soft black with subtle blue-gray undertone
* `public.text.primary`: warm white
* `public.text.secondary`: muted cool gray
* `public.accent.primary`: bold ember-red
* `public.accent.secondary`: amber-gold glow
* `public.border.subtle`: low-contrast slate border

#### Admin Mood Roles

* `admin.bg.canvas`: off-white or very light gray
* `admin.bg.surface`: white
* `admin.bg.subtle`: soft gray
* `admin.text.primary`: deep slate
* `admin.text.secondary`: medium gray
* `admin.accent.primary`: same family as brand primary, but used sparingly
* `admin.border.default`: neutral gray border
* `admin.data.highlight`: cool slate-blue or restrained brand tint

### 5.3 Suggested Token Starter Set

Berikut recommendation awal yang bisa dipakai sebagai baseline implementasi sebelum hex final dikunci:

```css
:root {
  --brand-primary-500: #c44b36;
  --brand-primary-600: #a93d2b;
  --brand-secondary-500: #d1a15b;
  --neutral-950: #0f1115;
  --neutral-900: #171a20;
  --neutral-800: #22262e;
  --neutral-700: #343946;
  --neutral-500: #707887;
  --neutral-300: #b8bfca;
  --neutral-100: #eceff3;
  --neutral-0: #ffffff;
}
```

Catatan:

* nilai ini adalah recommendation awal, bukan final brand lock
* palet ini dirancang agar tetap dekat ke rasa YogaFX yang premium dan dark-friendly

---

## 6. Typography System

### 6.1 Typography Direction

Karakter typography yang diinginkan:

* modern
* readable
* premium
* tidak terlalu dekoratif
* cocok untuk wellness-product brand

### 6.2 Recommended Pairing

Recommendation:

* **Display / Heading:** `Plus Jakarta Sans` atau `Manrope`
* **Body / UI:** `Inter` atau `Instrument Sans`

Pilihan yang paling aman dan modern:

* Headings: `Plus Jakarta Sans`
* Body/UI: `Inter`

Pilihan yang sedikit lebih berkarakter:

* Headings: `Manrope`
* Body/UI: `Instrument Sans`

### 6.3 Typography Rules

Public:

* heading lebih besar, lebih tegas, dan sedikit cinematic
* body tetap efisien dan mudah dibaca
* gunakan line length yang terkendali untuk menjaga conversion clarity

Admin:

* typography lebih netral dan padat
* heading jangan terlalu display-heavy
* body dan table text harus mengutamakan scanability

### 6.4 Recommended Scale

Starter scale:

* `display-xl`: 56/60
* `display-lg`: 44/52
* `heading-xl`: 32/40
* `heading-lg`: 24/32
* `heading-md`: 20/28
* `body-lg`: 18/28
* `body-md`: 16/24
* `body-sm`: 14/20
* `caption`: 12/16

Font weight guidance:

* display: `700`
* heading: `600-700`
* body: `400-500`
* labels/button: `500-600`

---

## 7. Layout, Spacing, and Shape

### 7.1 Spacing System

Gunakan spacing scale berbasis `4px`.

Recommended tokens:

* `space-1`: 4
* `space-2`: 8
* `space-3`: 12
* `space-4`: 16
* `space-5`: 20
* `space-6`: 24
* `space-8`: 32
* `space-10`: 40
* `space-12`: 48
* `space-16`: 64
* `space-20`: 80

Rules:

* public UI boleh lebih lapang
* admin UI lebih rapat tetapi tetap nyaman
* gunakan ritme vertikal yang konsisten antar section

### 7.2 Radius System

Recommended tokens:

* `radius-sm`: 8
* `radius-md`: 12
* `radius-lg`: 16
* `radius-xl`: 24
* `radius-pill`: 999

Usage:

* public cards dan hero panels: `radius-lg` atau `radius-xl`
* form fields dan buttons: `radius-md`
* admin cards dan modals: `radius-md`

### 7.3 Shadow System

Public shadows:

* lembut, lebar, atmospheric
* dipakai untuk depth pada dark surfaces

Admin shadows:

* ringan dan minim
* dipakai hanya untuk separasi, bukan drama visual

Starter tokens:

```css
--shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.08);
--shadow-md: 0 8px 24px rgba(0, 0, 0, 0.12);
--shadow-lg: 0 18px 50px rgba(0, 0, 0, 0.18);
```

---

## 8. Surface and Background Guidance

### 8.1 Public Surfaces

Public experience tidak boleh memakai background datar yang membosankan.

Recommended approach:

* layered dark gradients
* subtle vignette
* soft glow di area CTA atau score highlights
* noise atau texture sangat halus bila perlu

Public background direction:

* charcoal base
* deep slate gradient
* subtle warm accent bloom

### 8.2 Admin Surfaces

Admin harus lebih netral.

Recommended approach:

* bright canvas
* white cards
* subtle gray separators
* accent brand dipakai hemat untuk active states dan highlights

---

## 9. Motion Principles

Motion harus meaningful dan hemat.

Public motion:

* smooth fade-up untuk section load
* soft stagger pada question transitions
* subtle progress transitions
* celebratory but restrained score reveal

Admin motion:

* cepat dan fungsional
* panel open/close sederhana
* table/filter transitions minimal

Yang harus dihindari:

* bounce berlebihan
* animation dekoratif yang memperlambat quiz
* loader dramatis yang menambah friksi

---

## 10. Design Tokens

### 10.1 Semantic Tokens

Minimal token groups:

* `color.bg.*`
* `color.surface.*`
* `color.text.*`
* `color.border.*`
* `color.accent.*`
* `color.feedback.*`
* `space.*`
* `radius.*`
* `shadow.*`
* `font.family.*`
* `font.size.*`
* `font.weight.*`
* `motion.duration.*`
* `motion.easing.*`

### 10.2 Suggested Semantic Roles

```text
color.bg.canvas
color.bg.surface
color.bg.elevated
color.text.primary
color.text.secondary
color.text.muted
color.border.default
color.border.subtle
color.accent.primary
color.accent.primary-hover
color.accent.secondary
color.success
color.warning
color.error
color.info
```

### 10.3 State Tokens

Semua komponen inti minimal harus punya state:

* default
* hover
* active
* focus-visible
* disabled
* error
* success bila relevan

---

## 11. Public UI Guidance

### 11.1 Public UI Principles

* prioritaskan momentum dari lead form ke quiz
* buat result terasa sebagai payoff utama
* jaga friksi tetap rendah
* CTA harus selalu terbaca jelas

### 11.2 Public Screen Character

Lead form:

* focused
* elegant
* tidak terlalu panjang
* field spacing lega

Quiz flow:

* satu pertanyaan terasa jelas sebagai primary object
* progress indicator ringan tetapi meyakinkan
* pilihan jawaban mudah dipindai

Result page:

* harus terasa paling premium
* score summary perlu menonjol
* category breakdown perlu jelas
* recommendation dan CTA harus terlihat actionable

### 11.3 Public Components

Core components yang wajib distandarkan:

* hero container
* section wrapper
* progress indicator
* form field
* choice card / answer option
* primary CTA button
* secondary button
* score summary card
* category score block
* recommendation panel
* result CTA banner
* status/error panel

---

## 12. Admin UI Guidance

### 12.1 Admin UI Principles

* prioritize clarity over atmosphere
* gunakan layout dashboard yang familiar
* jadikan data mudah dipindai
* gunakan brand accent untuk structure, bukan dekorasi

### 12.2 shadcn-Based Guidance

Admin direkomendasikan tetap konsisten dengan pendekatan shadcn-based dashboard.

Guidance:

* gunakan `Card`, `Table`, `Badge`, `Dialog`, `DropdownMenu`, `Tabs`, `Sheet`, `Alert`, `Input`, `Textarea`, `Select`, `Switch`
* override visual tokens agar sesuai brand family
* jangan biarkan default shadcn tampil terlalu generik
* pertahankan hierarchy spacing dan table density yang stabil

### 12.3 Admin Components

Core components yang wajib distandarkan:

* app shell
* sidebar navigation
* top bar
* page header
* stats card
* data table
* badge/status pill
* filter bar
* search input
* form section card
* tabs
* dialog/modal
* destructive confirmation alert
* empty state
* validation summary

---

## 13. Core Component Guidance

### 13.1 Buttons

Primary button:

* public: strong brand accent, high contrast, slightly bold
* admin: restrained brand accent, cleaner and more utilitarian

Secondary button:

* lower emphasis
* surface-aware
* tetap jelas di dark maupun light mode context

Rules:

* label harus singkat dan actionable
* tinggi minimum sentuh nyaman
* focus-visible wajib jelas

### 13.2 Inputs

Rules:

* label selalu jelas
* placeholder bukan pengganti label
* error state harus spesifik
* public input boleh sedikit lebih tinggi dan lebih breathable
* admin input lebih ringkas dan padat

### 13.3 Cards

Public cards:

* lebih visual
* boleh memakai gradient, glow halus, dan depth

Admin cards:

* lebih tenang
* border dan shadow ringan
* fokus pada hierarchy data

### 13.4 Tables

Admin tables harus:

* scanable
* mendukung status
* punya empty state jelas
* tidak terlalu rapat
* responsive secara prioritas kolom

### 13.5 Badges and Status

Gunakan semantic status colors untuk:

* draft
* published
* archived
* pending
* completed
* failed
* active
* inactive

Status color harus konsisten antar admin views.

### 13.6 Choice Cards / Answer Options

Answer options pada public quiz adalah komponen penting.

Rules:

* seluruh card harus clickable
* selected state harus sangat jelas
* hover state memberi confidence
* focus state keyboard harus terlihat
* text alignment dan spacing harus mempermudah scan cepat

### 13.7 Progress Indicator

Progress indicator public harus:

* terasa menenangkan
* tidak terlalu teknis
* memberi rasa kemajuan yang jelas

Bisa berupa:

* thin progress bar
* step counter ringan
* section title + completion count

---

## 14. Responsive Principles

### 14.1 Public

* mobile-first untuk lead form dan quiz flow
* question flow harus nyaman satu tangan
* CTA utama harus mudah dijangkau
* score result harus tetap mudah dipahami di layar kecil

### 14.2 Admin

* desktop-first, tetapi tetap usable di tablet
* table harus punya strategi responsif
* sidebar dapat collapse
* filter dan form harus bisa stack dengan rapi

---

## 15. Accessibility Baseline

Minimal baseline:

* contrast memadai untuk text dan CTA
* semua interactive elements punya visible focus state
* form fields punya label jelas
* error messages mudah dipahami
* target tap/click cukup besar
* jangan mengandalkan warna saja untuk status
* motion harus tetap nyaman dan dapat dikurangi bila user memilih reduced motion

Public dark UI perlu perhatian khusus pada:

* text contrast
* disabled states
* subtle borders
* progress visibility

---

## 16. Public vs Admin Comparison

### 16.1 Public

* dark-first
* immersive
* premium
* conversion-focused
* larger spacing
* more atmospheric surfaces
* stronger CTA emphasis

### 16.2 Admin

* light-neutral by default
* structured
* utilitarian
* data-focused
* tighter spacing
* simpler surfaces
* restrained accent usage

### 16.3 Shared Family Signals

Keduanya tetap harus terasa satu family melalui:

* typographic DNA
* accent color family
* border radius logic
* interaction states
* tone of hierarchy

---

## 17. Implementation Notes

### 17.1 Token Strategy

Frontend sebaiknya mengimplementasikan foundation melalui CSS variables atau theme tokens.

Recommended layers:

* raw palette tokens
* semantic tokens
* component tokens

### 17.2 Theme Strategy

Recommended:

* public theme dan admin theme berbagi base brand tokens
* semantic tokens diturunkan per context
* jangan memaksakan satu token map identik untuk dua pengalaman yang berbeda

### 17.3 shadcn Integration

Untuk admin:

* gunakan shadcn sebagai structural component layer
* inject YogaFX token system ke theme layer
* standardisasi `button`, `input`, `card`, `badge`, `table`, `dialog`, `tabs`, `alert`

### 17.4 Frontend Priority Order

Jika implementasi dilakukan bertahap, prioritas design system:

1. tokens and theme roles
2. typography and spacing
3. buttons, inputs, cards
4. public quiz components
5. admin table and form patterns
6. result page patterns

---

## 18. Recommendation Summary

Recommendation visual direction untuk YogaFX Scoreboard:

* public flow memakai dark-first cinematic wellness-tech theme
* admin flow memakai light-neutral shadcn-style dashboard dengan brand accents terbatas
* accent utama memakai keluarga warm ember-red untuk CTA dan highlight penting
* typography memakai sans modern yang premium dan highly readable
* public UI diberi depth, gradient, dan fokus conversion
* admin UI dijaga netral, rapi, dan data-centric

Ini akan memberi hasil yang:

* tetap dekat dengan YogaFX LMS
* lebih siap untuk product UI
* tidak terasa seperti template SaaS generik
* tetap premium, immersive, dan trustworthy

---

## 19. Final Summary

Design System YogaFX Scoreboard harus menjadi jembatan antara brand continuity dan product clarity.

Public experience harus terasa:

* premium
* immersive
* dark-friendly
* conversion-focused

Admin experience harus terasa:

* structured
* efficient
* shadcn-compatible
* lightly branded

Keduanya harus hidup dalam satu keluarga visual yang modern, clean, high-trust, dan siap diimplementasikan sebagai sistem frontend yang konsisten.

You are working inside my Laravel 12 + Breeze + TailwindCSS project “SchoolSense”.
Goal: Implement a professional, attractive DARK UI (premium SaaS look) using Blade templates only (no Vue/React).

IMPORTANT CONSTRAINTS
- Use Laravel Breeze structure: pages use @extends('layouts.app') and @section('content').
- Do NOT use <x-layouts.app> layout components.
- Tailwind only (free). No paid UI libraries.
- Keep background effects subtle and ensure readability/contrast. Text must be clearly visible.
- We use accurate data: do NOT invent fees/curricula/activities if not present in DB. Show “Not provided yet” placeholders.
- Keep components reusable and consistent.

PAGES WE NEED NOW (Phase 1)
1) /schools  (index)
2) /schools/{school} (show)

ROUTES (must exist and be named)
- schools.index
- schools.show

CONTROLLER (must exist)
SchoolController@index returns view('schools.index', ['schools' => School::paginate(12)])
SchoolController@show returns view('schools.show', ['school' => $school])

MODEL FIELDS (School)
- name, country, city, address, websiteUrl, description
- contactEmail, contactPhone, contactPageUrl (nullable)
- feesMin, feesMax, currency, feePeriod (nullable)
Relationships may exist later: curricula, activities, languages, feeBands (don’t break if missing)

DESIGN SYSTEM (must follow)
- Dark background (slate-950/900) with subtle dots/orbs/noise.
- Add a readability overlay layer to keep content visible.
- Glass cards: darker background, clear border.
- Typography hierarchy:
  - H1 large + gradient accent
  - body text slate-300, muted slate-400/500
- Buttons:
  - Primary: indigo->purple gradient
  - Secondary: glass
- Consistent spacing: max-w-6xl mx-auto px-6, rounded-2xl/3xl.
- Pages must work on mobile/tablet/desktop.

FILE WORK YOU MUST DO
A Update layout:
- File: resources/views/layouts/app.blade.php
- Ensure it contains @yield('content') (NOT {{ $slot }}).
- Add pt-16 to main content because navbar is fixed.
- Reduce background noise/dots and add a readability overlay gradient.
- Keep navbar + auth links (login/register/logout) from Breeze working.

B Create/Update components:
- resources/views/components/badge.blade.php
- resources/views/components/glass-card.blade.php
- resources/views/components/school-card.blade.php
Components must be compatible with Blade anonymous components (<x-badge>, <x-glass-card>, <x-school-card>).
School card must:
- show name, city/country
- show website link button
- show fees summary ONLY if feesMin/feesMax exist otherwise “Fees not provided yet”
- show contact status “Not provided yet” if missing
- be fully clickable to schools.show route
- have strong contrast and hover glow

C Create pages:
- resources/views/schools/index.blade.php
  - Hero inside glass panel for readability
  - Show total schools count
  - Grid of school cards (1/2/3 columns responsive)
  - High quality empty state
  - Pagination (use $schools->links(), and if it looks bad, customize later but don’t break)
- resources/views/schools/show.blade.php
  - Header with school name + location + Visit Website button
  - Two-column layout desktop:
    - Left: About (description)
    - Right: Info card (fees summary + contact status)
  - Sections for fees/contact that gracefully show “Not provided yet”
  - Back link to /schools

QUALITY REQUIREMENTS
- Must be readable: no faint text blending into background.
- No broken routes, no missing view errors.
- No assumptions about data that doesn’t exist.
- Must run without JS errors.
- Don’t remove @vite from layout.

DELIVERABLES
- Provide the exact code changes in these files:
  1) resources/views/layouts/app.blade.php
  2) resources/views/components/badge.blade.php
  3) resources/views/components/glass-card.blade.php
  4) resources/views/components/school-card.blade.php
  5) resources/views/schools/index.blade.php
  6) resources/views/schools/show.blade.php
- If any controller/routes need edits, show them too (routes/web.php and app/Http/Controllers/SchoolController.php)

Before finishing, ensure /schools renders and shows the empty state if DB is empty.
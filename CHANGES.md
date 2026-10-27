# Orvexa – analysis & fixes

## Deploy notes
1. Run `php spark migrate` (widens `orvexa_user.password` to VARCHAR(255) for bcrypt). Existing MD5 passwords keep working and upgrade to bcrypt on each user's next login.
2. CSRF protection is now ON globally. A small script (`app/Views/layout/csrf.php`) attaches the token to jQuery/XHR/fetch calls and native forms. Any NEW page must include `view('layout/csrf')` in its `<head>`.
3. Delete nothing from `.env`, but rotate the DB password – it was committed to the repository. The stray root `index.php` (hard-coded DB credentials) should be deleted from the repo.
4. Set `CI_ENVIRONMENT = production` for live use (development shows the debug toolbar).

## Bugs fixed
- Users with no Redmine API key could not log in (`isset()` on NULL).
- Base URL hard-coded to `http://localhost:8080` (redirects, CSS, JS) – now derived from the request/`app.baseURL`.
- Session fixation (no regenerate), unsalted MD5 passwords, no brute-force limit (5 tries / 15 min).
- `applyFilters` route was outside the auth/ACL filters.
- SQL injection: admin user search, `sort` params (backlog + backlog items), meeting time-log query. Admin search also returned deleted users (AND/OR precedence).
- Uploaded files served inline (stored XSS) – now forced to download unless a safe type; path-prefix check fixed; sample-file download `../` traversal fixed.
- Report type taken from the URL was used to build method names – whitelisted. Filter endpoint returned a non-JSON string.
- Meeting report: date filter compared `dd-mm-YYYY` strings; "Meeting Date" showed the record-creation date. Backlog report dropped items without T-shirt size/updater (INNER JOIN).
- Multi-day holidays only matched their first day (and the calendar never received the end date).
- Calendar data windows: sprints that started >1 month ago vanished; Review/Retrospective sprints were never shown; meetings limited to 1 month back (now 3).
- Sprint creation: success response was commented out (UI reported failure); crash on missing settings; orphan sprint rows on failure.
- XSS: unescaped product/type/location options, breadcrumbs, JSON in single-quoted HTML attribute.

## Business logic added
Meetings: host is conflict-checked; conflicts by user id (not first name); cancelled meetings ignored; back-to-back allowed; every date of a recurring series checked (Sundays + holidays skipped); holidays warned; past/invalid dates and end<=start rejected; brainstorming requires a user story (validated before saving); editing keeps the original host and updates attendees on every occurrence of a series (future, non-cancelled only); only host or manager roles (1,2,3,9) may edit/cancel/log time; cancelled/finished meetings cannot be cancelled/edited; time can't be logged twice, for future meetings, for non-attendees, or above the meeting duration; the "submit again to confirm" warning is bound to the exact meeting.
Sprints: end >= start, unique name+version per product, a task can only be in one open sprint (open = not Completed), whole creation is one transaction.

## Design
- `public/assets/css/orvexa-pro.css`: refined cards, tables, buttons, forms, modals, dropdowns, scrollbars, focus states – existing palette variables only.
- Calendar rebuilt: Month / Week / Day / Agenda, "now" line, tinted event pills with status accent, continuous sprint bars, multi-day holiday bands, mini-month navigator, combinable filter chips with counts, quick "New meeting", click-a-time-slot scheduling (pre-fills date, start, 1h), keyboard shortcuts (T, M/W/D/A, arrows, ?), view remembered between visits.

## Not covered
Backlog / user-story / task / planning-poker business rules were not reviewed. ACL still allows routes that have no permission row (fail-open) – left unchanged to avoid locking users out.

## PHP 8.5.12 upgrade
- Docker image moved from `php:8.3-fpm` to `php:8.5.12-fpm`.
- CodeIgniter upgraded 4.5.2 → 4.7.5 (4.5.2 fatals on PHP ≥ 8.4: `Time::createFromTimestamp()` clashes with `DateTimeImmutable`). `app/Config` was 3-way merged with the upstream changes; local settings kept.
- The custom `hasInvalidInput()` that had been patched into `system/RESTful/ResourceController.php` now lives in `NotesController`, so `system/` is stock framework code again.
- Composer dependencies updated (dompdf 3.1, react/promise 3.3, phpspreadsheet 2.4, kint 6, phpunit 11, …) to clear PHP 8.5 deprecations. `cboden/ratchet` 0.4.4 (WebSocket CLI only) is abandoned upstream and still emits compile-time deprecation notices; it works but should eventually be replaced.
- Fixed: sprint AJAX endpoints (`sprintplanning`, `retrospectivedetails`) returned 500 when opened directly; poker endpoint crashed on an empty story list; routes pointing at missing methods removed (`sprint/navscrumdiary`, `report/pocker`, GET `backlog/test`); per-view CSS/JS tags emitted for files that do not exist (404s).
- Fixed JS errors: jQuery loaded twice (wiped jQuery UI `sortable`), Bootstrap/app.js loaded three times, sidebar script crashes, `userStories.js`, `createSprint.js`, `productDashboard.js`; notification popover now escapes meeting data and only links http(s) URLs.

## Design refresh
- New design layer `public/assets/css/orvexa-ui.css` (tokens, shell, buttons, forms, tables, modals, badges); legacy colour variables now map onto it, and hard-coded legacy blues in page CSS/JS were replaced with brand tokens.
- New app shell: light sidebar with sections, collapsible to icons (remembered), off-canvas on mobile; sticky topbar with breadcrumbs, notifications popover and account menu. Inter is self-hosted (`public/assets/fonts/inter`, OFL).
- Rebuilt: dashboard (stat cards, product list), products grid, login (split layout, optimised 95 KB illustration instead of the 2.9 MB PNG), 401 page; restyled sprint view, backlog item details, permissions and admin settings.
- Full-screen loader only appears for slow navigations (and uses a 27 KB icon instead of a 3.4 MB PNG); AJAX calls show a slim top progress bar.
- Brand palette applied app-wide (`orvexa-ui.css` tokens): Primary Green `#42D674`, Fresh Green `#80EF80`, Pistachio `#E3F0A3`, Sage `#BADBA2`, Deep Green `#102018`, Soft White `#F7FAF3`. Primary actions are Primary Green with Deep Green text (8.9:1); green text/links use derived darker shades (`#167A40`, 5.4:1) because Primary Green on white is only 1.9:1. Login page and loader unchanged.

## Design pass 2 – consistency across pages
- One primary button style everywhere (Primary Green fill, Deep Green text). The legacy `#main .btn-primary` rule in `app-theme.css` had made some primaries dark green with white text. Each page now has one primary action; Refinement, Add epic, Import user stories, and Edit/Delete permission are secondary.
- New shared components in `orvexa-ui.css`: a context strip (`.ox-context`) replaces the green "Product name :" lines on backlog items, refinement, user stories and tasks. Also added: ID chips, status pills, ghost icon actions (delete turns red on hover), and tinted priority chips. The "M" chip was white on yellow (1.6:1).
- Tables are left-aligned. A legacy `tbody { top: 10px }` pushed rows down and clipped the last row; this is fixed.
- Task cards were rebuilt as a field grid with a progress bar (the old markup had a broken `class` attribute and an unclosed `div`). User story card headers were tidied, and both card types wrap on phones.
- Product dashboard: cards are restyled, white-on-white empty messages are fixed, and the charts use one palette validated for colour-blind readers (green, blue, amber, violet, rose). Empty slices no longer print "0.0%".
- Empty states (`NodataView`, sprint list) use the shared `ox-empty` panel with useful copy.
- Permissions are grouped into module cards with a sticky "Set role" bar. On Admin Settings, Customer Management no longer shows under every other tab (`#customerDiv` was missing from the hide list).
- Past day numbers in the calendar now meet 4.5:1 contrast.

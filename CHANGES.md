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

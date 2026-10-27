# Orvexa Tool – Project Documentation

## 1. Purpose and scope

Orvexa is a Scrum/project execution platform. It connects products, customers, backlog items, epics, user stories, tasks, sprints, meetings, team members, permissions, reports, and notifications in one application.

This document is a technical overview of the repository as inspected. It is intended for developers, administrators, testers, and deployment/support teams.

## 2. Technology stack

| Area | Technology | Evidence in repository |
|---|---|---|
| Application framework | CodeIgniter 4 | `composer.json`, `app/Config`, `spark` |
| Language/runtime | PHP 8.2+ (CodeIgniter 4.7.5); Docker image uses PHP 8.5.12-FPM | `composer.json`, `Dockerfile` |
| Database | MySQL 8.4 LTS | `docker-compose.yml` |
| Web server | Nginx 1.27 Alpine | `docker-compose.yml`, `docker/nginx/default.conf` |
| Database driver | MySQLi / PDO MySQL extension | `app/Config/Database.php`, `Dockerfile` |
| Front end | Server-rendered PHP views, CSS, JavaScript, Bootstrap-style UI assets | `app/Views`, `public/assets`, `public/support` |
| Real-time capability | Ratchet WebSockets | `composer.json`, `app/Websockets/PockerServer.php` |
| External integration | Redmine API client | `kbsali/redmine-api`, sync controllers/models |
| Reports/files | Dompdf and PhpSpreadsheet | `composer.json`, sprint/report controllers |
| Testing | PHPUnit 11.5 and CodeIgniter test support | `tests`, `composer.json` |

## 3. Repository structure

```text
app/
  Config/          Application configuration, routes, filters, services
  Controllers/     HTTP request handlers grouped by business capability
  Models/          Database access and domain operations
  Services/        Reusable application services
  Views/           PHP templates and email templates
  Database/        Application migrations and seeds
  Filters/         Authentication and access-control filters
  Jobs/            Background/queued work
  Websockets/      WebSocket server code
public/            Public entry assets, CSS, JavaScript, images, uploads/support files
system/            CodeIgniter framework source
docker/            Nginx and MySQL initialization files
tests/             Unit, controller, model, database, and session tests
database_sample/   Archived/sample database exports
writable/          Runtime logs, cache, sessions, and generated files
```

## 4. Framework and request flow

The application uses the CodeIgniter 4 MVC pattern:

1. Nginx receives the request on port `8080` and forwards PHP execution to PHP-FPM.
2. `index.php` boots CodeIgniter.
3. `app/Config/Routes.php` maps the URI and HTTP method to a controller action.
4. Filters run before the controller. Protected business routes use `auth` and `acl`.
5. Controllers validate input, call models/services, and select a view or return data.
6. Models use CodeIgniter's database layer against MySQL.
7. Views render the server-side HTML; JavaScript performs asynchronous form and table operations where configured.

### Main application modules

| Module | Main controllers/models | Responsibilities |
|---|---|---|
| Authentication | `AuthController`, `UserModel` | Login, logout, session identity, unauthorized access |
| Dashboard | `DashboardController`, `DashboardModel` | Product/sprint metrics and pending tasks |
| Backlog | `BacklogController`, `UserStoryController`, `TaskController`, `RefinementController` | Backlog items, epics, stories, tasks, refinement, comments, documents |
| Sprint | `SprintController`, `Models/Sprint/*` | Planning, daily scrum, review, retrospective, history, PDF output |
| Meetings | `MeetingController`, `Models/Meeting/*` | Calendar, scheduling, teams, members, time logging, notifications |
| Administration | `AdminController`, `SettingsController`, `Models/Admin/*` | Users, roles, permissions, holidays, poker and sizing settings |
| Reports | `CustomReportController` | Meeting, sprint, and backlog reports/export |
| Integration | `SyncRedmineController`, `SyncRedmineModel` | Redmine users/projects/issues synchronization |

## 5. Local deployment

### Start the stack

```bash
docker compose up -d --build
docker compose ps
```

Endpoints:

| Service | Address |
|---|---|
| Application | http://localhost:8080 |
| Adminer | http://localhost:8081 |
| MySQL from host | `localhost:3307` |
| MySQL inside Docker network | host `mysql`, port `3306` |

The MySQL initialization script is mounted into `/docker-entrypoint-initdb.d`. It is executed automatically only when the MySQL data volume is initialized for the first time.

### Useful commands

```bash
docker compose logs -f app
docker compose exec -T app php spark routes
docker compose exec -T app php spark list
docker compose exec -T app php spark migrate
docker compose exec -T app ./vendor/bin/phpunit
```

### Environment and database caution

`docker-compose.yml` expects a root `.env` file for the application container. The checked-in `app/Config/Database.php` contains development defaults (`root`/`root`, database `orvexa`, port `3306`), while the Docker service exposes MySQL to the host on `3307`. Confirm the runtime environment overrides these values correctly before running outside Docker or in production.

Never use the sample credentials from the README in a production deployment. Rotate database, Adminer, application, mail, API, and encryption secrets.

## 6. Database structure

The primary schema is `docker/mysql/init/01-scrum_tool.sql`. The schema contains CodeIgniter support tables, queue tables, and Orvexa domain tables. Most Orvexa tables use a numeric primary key, `is_deleted` soft-delete flag, and/or created/updated audit fields. Relationships are commonly represented by `r_*` columns; verify exact foreign-key constraints in the SQL before changing a relationship.

### Platform/support tables

| Table | Purpose |
|---|---|
| `migrations` | CodeIgniter migration history |
| `queue_jobs` | Pending background jobs |
| `queue_jobs_failed` | Failed background jobs |
| `settings` | Framework/application settings table |

### Reference and administration tables

| Tables | Purpose |
|---|---|
| `orvexa_user`, `orvexa_role`, `orvexa_permission`, `orvexa_role_permission` | Users, roles, permissions, and role-permission mapping |
| `orvexa_module`, `orvexa_status`, `orvexa_module_status` | Navigation/business modules and module-specific statuses |
| `orvexa_settings` | Orvexa configuration key/value settings |
| `orvexa_customer` | Customers |
| `orvexa_trackers`, `orvexa_task_status` | Issue/task classification and status values |
| `orvexa_document_type`, `orvexa_notes_type`, `orvexa_action_type` | Document, note, and audit-action classifications |
| `orvexa_holidays` | Non-working days |
| `orvexa_fibonacci_settings`, `orvexa_t_shirt_size` | Estimation configuration |

### Product and backlog tables

| Tables | Purpose |
|---|---|
| `orvexa_product`, `orvexa_product_customer`, `orvexa_product_owners`, `orvexa_product_user` | Products and their customer, owner, member, and role associations |
| `orvexa_backlog_item` | Product-level backlog items |
| `orvexa_epic` | Epics under backlog items |
| `orvexa_user_story`, `orvexa_user_story_condition` | User stories and acceptance conditions |
| `orvexa_task` | Tasks attached to user stories |
| `orvexa_comments` | Comments associated with work items |
| `orvexa_document` | Documents/references attached to work items |
| `orvexa_poker_planning` | Planning-poker estimates and reveal state |

Core hierarchy:

```text
Product -> Backlog item -> Epic -> User story -> Task
Product -> Sprint -> Sprint task / Sprint user
```

### Sprint tables

| Tables | Purpose |
|---|---|
| `orvexa_sprint`, `orvexa_sprint_duration` | Sprint definition, dates, goal, duration |
| `orvexa_sprint_task`, `orvexa_sprint_user` | Sprint work and participants |
| `orvexa_sprint_planning` | Sprint planning records |
| `orvexa_daily_scrum` | Daily scrum entries |
| `orvexa_sprint_review` | Sprint review outcomes |
| `orvexa_sprint_retrospective` | Retrospective outcomes |
| `orvexa_sprint_activity` | Sprint activity reference values |
| `orvexa_code_review_users` | Code-review participant assignments |

### Meeting and collaboration tables

| Tables | Purpose |
|---|---|
| `orvexa_meeting_details` | Meeting schedule, product/sprint links, duration, location, recurrence |
| `orvexa_meeting_members` | Meeting attendees and logged duration |
| `orvexa_meeting_type`, `orvexa_meeting_location` | Meeting reference values |
| `orvexa_meeting_team`, `orvexa_meeting_team_members` | Reusable meeting teams and members |
| `orvexa_brainstorm_meeting_details` | Brainstorm meeting-specific details |
| `orvexa_notes` | Polymorphic/reference-linked notes |

### Integration and audit tables

| Tables | Purpose |
|---|---|
| `orvexa_sync_activities` | Redmine/external synchronization status and timestamps |
| `orvexa_user_action` | User activity/audit records with action, module, and reference data |

### Schema maintenance notes

- The SQL file is the current complete bootstrap schema; keep it versioned with any release that changes database structure.
- `app/Database/Migrations` contains only test/example migration material in the inspected pack. Add real forward migrations for future production changes.
- Preserve soft-delete behavior when querying records; a missing `is_deleted` condition can expose retired data.
- Treat `external_*`, `r_*`, and `reference_id` columns as integration/relationship boundaries and document their source system and lifecycle.
- Before production use, confirm indexes, foreign keys, cascading behavior, and uniqueness constraints against actual workload requirements.

## 7. Security and operations checklist

- Set `CI_ENVIRONMENT=production` and disable debug output and the development toolbar.
- Move credentials and API keys to environment secrets; do not commit them.
- Enable and verify CSRF, secure headers, invalid-character, and HTTPS protections for the deployed threat model. The configuration currently defines several filters but leaves some global filters commented out.
- Confirm `auth` and `acl` protect every state-changing route, including asynchronous POST endpoints.
- Restrict Adminer and MySQL ports in production; they are convenient for local development but should not be publicly exposed.
- Configure writable directory ownership, log rotation, session storage, queue workers, email delivery, and backups.
- Back up both the MySQL data volume and uploaded/generated files under `writable` or public support locations.
- Review file download/view endpoints for authorization, path traversal protection, MIME validation, and safe storage names.

## 8. Testing and quality

The `tests/` directory includes model tests, controller tests, session/database examples, and a health test. Run the suite after configuration and schema changes:

```bash
docker compose exec -T app ./vendor/bin/phpunit
```

Recommended additions:

- Integration tests for login, ACL denial, and every major state transition.
- Migration tests that build a clean database from an empty schema.
- Contract tests for Redmine synchronization and queue retries.
- Upload/download security tests and authorization tests for generated reports.
- Smoke tests for the Docker deployment and the WebSocket process.

## 9. Documents that should be maintained separately

This overview should remain stable and link to focused documents as they are created:

1. **Deployment Runbook** – environments, secrets, Docker release procedure, health checks, rollback, and backup restore.
2. **Database Reference** – complete column-level data dictionary, indexes, foreign keys, lifecycle rules, and ER diagram.
3. **API/Route Reference** – route, method, authentication, request fields, response shape, and error behavior.
4. **Administrator Guide** – users, roles, permissions, products, holidays, estimation configuration, and integrations.
5. **User Guide** – backlog, refinement, poker planning, sprint ceremonies, meetings, dashboards, and reports.
6. **Redmine Integration Guide** – credentials, mapped identifiers, sync direction, scheduling, failure handling, and reconciliation.
7. **Security Guide** – threat model, secrets, CSRF/ACL rules, file handling, audit logging, and incident response.
8. **Testing Guide** – local test setup, test data, PHPUnit conventions, coverage targets, and CI checks.
9. **Change Log/Release Notes** – schema changes, breaking route changes, dependency changes, and deployment notes.

## 10. Current documentation gaps and risks

- `README.md` contains unresolved Git conflict markers and should be cleaned up before it is treated as the official entry point.
- The README and configuration expose development credentials/defaults; these should be clearly marked as local-only or removed.
- The schema is initialized from one SQL dump rather than a complete production migration history.
- The exact table relationships and indexes are not represented in a dedicated ER/data dictionary document.
- Some security filters are declared but commented out globally; this needs an intentional production decision.

## 11. Source-of-truth files

- Application dependencies: `composer.json`
- Routes: `app/Config/Routes.php`
- Filters: `app/Config/Filters.php`
- Database connection: `app/Config/Database.php`
- Bootstrap schema: `docker/mysql/init/01-scrum_tool.sql`
- Container stack: `docker-compose.yml`
- PHP runtime image: `Dockerfile`
- Automated tests: `tests/`

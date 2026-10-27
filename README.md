# Orvexa

Orvexa is a Scrum and software-delivery management platform for products, backlogs, user stories, tasks, sprints, meetings, teams, and reports.

> Orvexa — Orchestrate Work. Accelerate Execution.

## Quick start

Requirements: Docker, Docker Compose, and Git.

```bash
docker compose up -d --build
docker compose ps
```

Open the application at http://localhost:8080.

## Common commands

```bash
docker compose stop
docker compose start
docker compose down
docker compose restart
docker compose logs -f
docker compose logs -f app
docker compose exec app sh
docker compose exec -T app php spark list
docker compose exec -T app php spark routes
docker compose exec -T app php spark migrate
docker compose exec -T app ./vendor/bin/phpunit
```

See [docs/ORVEXA_COMMANDS.md](docs/ORVEXA_COMMANDS.md) for the full command reference.

## Services

| Service | Address | Purpose |
|---|---|---|
| Application | http://localhost:8080 | Orvexa web application |
| Adminer | http://localhost:8081 | Local database administration |
| MySQL | localhost:3307 | Database access from the host |

## Technology and architecture

- PHP 8.5.12-FPM and CodeIgniter 4
- MySQL 8.3 and Nginx 1.27
- Docker Compose and PHPUnit 10.5
- Redmine synchronization, report exports, background jobs, and WebSockets

The application follows CodeIgniter MVC. Routes are defined in `app/Config/Routes.php`; authentication and ACL filters protect modules; controllers call models/services; and views render the interface.

```text
Product → Backlog item → Epic → User story → Task
Product → Sprint → Sprint task / Sprint user
```

See [docs/ORVEXA_PROJECT_DOCUMENTATION.md](docs/ORVEXA_PROJECT_DOCUMENTATION.md) for detailed framework, modules, database structure, deployment notes, and operations guidance.

## Project structure

| Directory | Purpose |
|---|---|
| `app/` | Application code, configuration, controllers, models, views, filters, and WebSockets |
| `public/` | Public CSS, JavaScript, images, and assets |
| `docker/` | Docker, Nginx, and database initialization files |
| `tests/` | PHPUnit tests |
| `docs/` | Project and command documentation |

## Configuration and security

Review `docker-compose.yml`, `Dockerfile`, `app/Config/Database.php`, `app/Config/Routes.php`, and `app/Config/Filters.php` when changing the environment.

Do not commit production passwords, API keys, encryption keys, or mail credentials. Docker credentials are for local development only. Do not expose MySQL or Adminer publicly in production.

## Database and testing

The bootstrap schema is [docker/mysql/init/01-scrum_tool.sql](docker/mysql/init/01-scrum_tool.sql). Future schema changes should use versioned CodeIgniter migrations.

MySQL data is stored in the `orvexa_mysql_data` Docker volume. Running `docker compose down` preserves the volume.

## Brand meaning

Orvexa combines **Orchestrate** (coordinate work), **Velocity** (move faster), and **X** (execution, technology, and collaboration).

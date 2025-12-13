# Task Manager (DIP demo)

Small PHP web app to demonstrate **Dependency Inversion Principle**:

- `TaskService` depends on `TaskRepository` interface
- `SqliteTaskRepository` implements the interface using SQLite

## Requirements

- PHP **8.1+** (enums are used)
- SQLite extension: `pdo_sqlite` (recommended) or `sqlite3`

Check:

```bash
php -v
php -m | grep -Ei 'pdo_sqlite|sqlite3'
```

If missing on Ubuntu/Mint:

```bash
sudo apt update
sudo apt install php-sqlite3
```

## Run (recommended: built-in PHP server)

From the project root (`task-manager-web/`):

```bash
php -S 127.0.0.1:8000 -t public public/router.php
```

Open:

- http://127.0.0.1:8000/

Database file will be created automatically in `data/app.sqlite`.

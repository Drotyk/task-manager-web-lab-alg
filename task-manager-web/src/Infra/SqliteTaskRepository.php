<?php
declare(strict_types=1);

namespace src\Infra;

use PDO;
use src\Ports\TaskRepository;
use src\Domain\Task;
use src\Domain\TaskStatus;

final class SqliteTaskRepository implements TaskRepository {
    private PDO $pdo;

    public function __construct(string $dbPath) {
        $dir = dirname($dbPath);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $this->pdo = new PDO('sqlite:' . $dbPath);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS tasks(
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              title TEXT NOT NULL,
              description TEXT NOT NULL DEFAULT '',
              status TEXT NOT NULL,
              priority INTEGER NOT NULL,
              due_date TEXT NOT NULL DEFAULT ''
            );
        ");
    }

    public function add(string $title, string $description, int $priority, string $dueDate): int {
        $st = $this->pdo->prepare("INSERT INTO tasks(title,description,status,priority,due_date) VALUES(?,?,?,?,?)");
        $st->execute([$title, $description, 'TODO', $priority, $dueDate]);
        return (int)$this->pdo->lastInsertId();
    }

    public function all(): array {
        $rows = $this->pdo->query("SELECT * FROM tasks ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        return array_map(function($r){
            return new Task(
                (int)$r['id'],
                (string)$r['title'],
                (string)$r['description'],
                TaskStatus::fromString((string)$r['status']),
                (int)$r['priority'],
                (string)$r['due_date']
            );
        }, $rows);
    }

    public function setStatus(int $id, TaskStatus $status): void {
        $st = $this->pdo->prepare("UPDATE tasks SET status=? WHERE id=?");
        $st->execute([$status->value, $id]);
        if ($st->rowCount() === 0) throw new \RuntimeException("Task not found");
    }

    public function remove(int $id): void {
        $st = $this->pdo->prepare("DELETE FROM tasks WHERE id=?");
        $st->execute([$id]);
        if ($st->rowCount() === 0) throw new \RuntimeException("Task not found");
    }
}

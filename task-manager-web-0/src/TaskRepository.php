<?php
declare(strict_types=1);

namespace src\Ports;

use src\Domain\Task;
use src\Domain\TaskStatus;

interface TaskRepository {
    public function add(string $title, string $description, int $priority, string $dueDate): int;
    public function all(): array; // Task[]
    public function setStatus(int $id, TaskStatus $status): void;
    public function remove(int $id): void;
}

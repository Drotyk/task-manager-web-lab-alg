<?php
declare(strict_types=1);

namespace src\App;

use src\Ports\TaskRepository;
use src\Domain\TaskStatus;

final class TaskService {
    public function __construct(private TaskRepository $repo) {}

    public function create(string $title, string $description, int $priority, string $dueDate): int {
        if ($title === '') throw new \RuntimeException("Title is empty");
        if ($priority < 1 || $priority > 5) throw new \RuntimeException("Priority must be 1..5");
        return $this->repo->add($title, $description, $priority, $dueDate);
    }

    public function all(): array {
        return $this->repo->all();
    }

    public function changeStatus(int $id, TaskStatus $status): void {
        if ($id <= 0) throw new \RuntimeException("Bad id");
        $this->repo->setStatus($id, $status);
    }

    public function remove(int $id): void {
        if ($id <= 0) throw new \RuntimeException("Bad id");
        $this->repo->remove($id);
    }
}

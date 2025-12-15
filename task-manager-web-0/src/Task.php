<?php
declare(strict_types=1);

namespace src\Domain;

final class Task {
    public function __construct(
        public int $id,
        public string $title,
        public string $description,
        public TaskStatus $status,
        public int $priority,
        public string $dueDate
    ) {}
}

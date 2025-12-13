<?php
declare(strict_types=1);

namespace src\Domain;

enum TaskStatus: string {
    case TODO = 'TODO';
    case DOING = 'DOING';
    case DONE = 'DONE';

    public static function fromString(string $s): self {
        return match (strtoupper(trim($s))) {
            'TODO' => self::TODO,
            'DOING' => self::DOING,
            'DONE' => self::DONE,
            default => throw new \RuntimeException("Bad status: $s")
        };
    }
}

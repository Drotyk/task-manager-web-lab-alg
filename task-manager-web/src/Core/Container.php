<?php
declare(strict_types=1);

namespace src\Core;

class Container {
    // Тут зберігаємо "рецепти" (closures), як створювати об'єкти
    private array $definitions = [];
    
    // Тут зберігаємо вже створені об'єкти (Singleton pattern)
    private array $instances = [];

    // Метод для реєстрації залежності (id = назва класу або інтерфейсу)
    public function set(string $id, callable $factory): void {
        $this->definitions[$id] = $factory;
    }

    // Метод для отримання готового об'єкту
    public function get(string $id): mixed {
        // 1. Якщо об'єкт вже створений — повертаємо його (Singleton)
        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        // 2. Якщо немає рецепту — помилка
        if (!array_key_exists($id, $this->definitions)) {
            throw new \RuntimeException("Service not found: $id");
        }

        // 3. Виконуємо рецепт, створюємо об'єкт
        $factory = $this->definitions[$id];
        $service = $factory($this); // Передаємо контейнер всередину фабрики

        // 4. Зберігаємо результат, щоб не створювати знову
        $this->instances[$id] = $service;

        return $service;
    }
}
<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Автозавантаження класів
spl_autoload_register(function(string $class){
    // Перетворює namespace (наприклад, src\Core\Container) у шлях файлу
    $path = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($path)) require $path;
});

// Імпортуємо класи
use src\Core\Container;
use src\Infra\SqliteTaskRepository;
use src\App\TaskService;
use src\Ports\TaskRepository;
use src\Domain\TaskStatus;

// 1. Ініціалізація Контейнера
$container = new Container();

// 2. Налаштування залежностей (Wiring)

// Коли просимо інтерфейс TaskRepository -> повертаємо SqliteTaskRepository
$container->set(TaskRepository::class, function() {
    return new SqliteTaskRepository(__DIR__ . '/../data/app.sqlite');
});

// Коли просимо TaskService -> створюємо його, беручи репозиторій з контейнера
$container->set(TaskService::class, function(Container $c) {
    return new TaskService($c->get(TaskRepository::class));
});

// 3. Запуск застосунку

// Отримуємо готовий сервіс із контейнера
$service = $container->get(TaskService::class);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function redirect(string $to = '/'): void {
    header("Location: $to");
    exit;
}

try {
    // Обробка POST запитів
    if ($method === 'POST' && $path === '/add') {
        $service->create(
            trim($_POST['title'] ?? ''),
            trim($_POST['description'] ?? ''),
            (int)($_POST['priority'] ?? 3),
            trim($_POST['due_date'] ?? '')
        );
        redirect('/');
    }

    if ($method === 'POST' && $path === '/status') {
        $id = (int)($_POST['id'] ?? 0);
        $st = (string)($_POST['status'] ?? 'TODO');
        $service->changeStatus($id, TaskStatus::fromString($st));
        redirect('/');
    }

    if ($method === 'POST' && $path === '/delete') {
        $id = (int)($_POST['id'] ?? 0);
        $service->remove($id);
        redirect('/');
    }

    // GET / - Отримання списку задач
    $tasks = $service->all();
    $error = null;

} catch (Throwable $e) {
    // Якщо сталася помилка (наприклад, БД заблокована або валідація не пройшла)
    $tasks = $service->all(); // Пробуємо все одно показати задачі
    $error = $e->getMessage();
}

?>
<!doctype html>
<html lang="uk">
<head>
  <meta charset="utf-8">
  <title>Task Manager (DI Container)</title>
  <link rel="stylesheet" href="/styles.css">
</head>
<body>
<main class="container">
  <header class="header">
    <h1>Task Manager</h1>
    <p class="muted">DIP demo: Container → Service → Interface → Repository</p>
  </header>

  <?php if (!empty($error)): ?>
    <div class="alert"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <section class="card">
    <h2>Додати задачу</h2>
    <form method="post" action="/add" class="grid">
      <label>
        Назва
        <input name="title" required maxlength="120">
      </label>

      <label>
        Пріоритет (1..5)
        <input name="priority" type="number" min="1" max="5" value="3">
      </label>

      <label class="span2">
        Опис
        <textarea name="description" rows="3"></textarea>
      </label>

      <label>
        Дедлайн (YYYY-MM-DD або пусто)
        <input name="due_date" placeholder="2025-12-20">
      </label>

      <button class="btn" type="submit">Додати</button>
    </form>
  </section>

  <section class="card">
    <h2>Список задач</h2>

    <?php if (empty($tasks)): ?>
      <p class="muted">Поки задач немає.</p>
    <?php else: ?>
      <table class="table">
        <thead>
          <tr>
            <th>ID</th><th>Статус</th><th>Пріоритет</th><th>Дедлайн</th><th>Назва</th><th>Дії</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tasks as $t): ?>
            <tr>
              <td><?= (int)$t->id ?></td>
              <td><?= htmlspecialchars($t->status->value) ?></td>
              <td><?= (int)$t->priority ?></td>
              <td><?= htmlspecialchars($t->dueDate ?: '-') ?></td>
              <td>
                <strong><?= htmlspecialchars($t->title) ?></strong>
                <?php if ($t->description): ?>
                  <div class="muted small"><?= nl2br(htmlspecialchars($t->description)) ?></div>
                <?php endif; ?>
              </td>
              <td class="actions">
                <form method="post" action="/status">
                  <input type="hidden" name="id" value="<?= (int)$t->id ?>">
                  <select name="status">
                    <option <?= $t->status->value==='TODO'?'selected':'' ?>>TODO</option>
                    <option <?= $t->status->value==='DOING'?'selected':'' ?>>DOING</option>
                    <option <?= $t->status->value==='DONE'?'selected':'' ?>>DONE</option>
                  </select>
                  <button class="btn" type="submit">OK</button>
                </form>

                <form method="post" action="/delete" onsubmit="return confirm('Видалити задачу?')">
                  <input type="hidden" name="id" value="<?= (int)$t->id ?>">
                  <button class="btn danger" type="submit">X</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>
</main>
</body>
</html>
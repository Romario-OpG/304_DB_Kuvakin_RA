<?php
/**
 * Добавление выполненной работы мастера
 */

require_once '../config.php';

$mechanic_id = $_GET['mechanic_id'] ?? null;
$error = '';

if (!$mechanic_id) {
    header('Location: index.php');
    exit;
}

// Получаем данные мастера
$stmt = $pdo->prepare("SELECT * FROM mechanic WHERE mechanic_id = ?");
$stmt->execute([$mechanic_id]);
$mechanic = $stmt->fetch();

if (!$mechanic) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual_start_raw = trim($_POST['actual_start'] ?? '');
    $actual_end_raw = trim($_POST['actual_end'] ?? '');
    $final_price = $_POST['final_price'] ?? '';
    $notes = trim($_POST['notes'] ?? '');
    
    // Конвертируем формат datetime-local (YYYY-MM-DDTHH:MM) в SQL формат (YYYY-MM-DD HH:MM:SS)
    $actual_start = str_replace('T', ' ', $actual_start_raw) . ':00';
    $actual_end = str_replace('T', ' ', $actual_end_raw) . ':00';
    
    if (empty($actual_start_raw) || empty($actual_end_raw) || empty($final_price)) {
        $error = 'Все обязательные поля должны быть заполнены';
    } elseif (!is_numeric($final_price) || $final_price < 0) {
        $error = 'Стоимость должна быть положительным числом';
    } elseif (strtotime($actual_end) <= strtotime($actual_start)) {
        $error = 'Дата окончания должна быть позже даты начала';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO work_log (mechanic_id, actual_start, actual_end, final_price, notes)
                VALUES (?, ?, ?, ?, ?)
            ");
            $notes_value = !empty($notes) ? $notes : null;
            $stmt->execute([$mechanic_id, $actual_start, $actual_end, $final_price, $notes_value]);
            header("Location: work_log.php?mechanic_id=$mechanic_id");
            exit;
        } catch (PDOException $e) {
            $error = 'Ошибка при добавлении работы: ' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить выполненную работу</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <a href="work_log.php?mechanic_id=<?= $mechanic_id ?>" class="back-link">← Назад к работам</a>
        <h1>Добавить выполненную работу</h1>
        <p><strong>Мастер:</strong> <?= htmlspecialchars($mechanic['full_name']) ?></p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="actual_start">Дата и время начала:</label>
                <input type="datetime-local" id="actual_start" name="actual_start" required step="1"
                       value="<?= isset($_POST['actual_start']) ? htmlspecialchars($_POST['actual_start']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="actual_end">Дата и время окончания:</label>
                <input type="datetime-local" id="actual_end" name="actual_end" required step="1"
                       value="<?= isset($_POST['actual_end']) ? htmlspecialchars($_POST['actual_end']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="final_price">Стоимость (руб.):</label>
                <input type="number" id="final_price" name="final_price" step="0.01" min="0" required
                       value="<?= isset($_POST['final_price']) ? htmlspecialchars($_POST['final_price']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="notes">Примечания:</label>
                <textarea id="notes" name="notes" rows="4"><?= isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : '' ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-add">Добавить</button>
                <a href="work_log.php?mechanic_id=<?= $mechanic_id ?>" class="btn btn-edit">Отмена</a>
            </div>
        </form>
    </div>
</body>
</html>


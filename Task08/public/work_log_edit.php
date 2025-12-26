<?php
/**
 * Редактирование выполненной работы мастера
 */

require_once '../config.php';

$work_id = $_GET['id'] ?? null;
$mechanic_id = $_GET['mechanic_id'] ?? null;
$error = '';

if (!$work_id || !$mechanic_id) {
    header('Location: index.php');
    exit;
}

// Получаем данные работы
$stmt = $pdo->prepare("SELECT * FROM work_log WHERE work_id = ?");
$stmt->execute([$work_id]);
$work = $stmt->fetch();

if (!$work) {
    header("Location: work_log.php?mechanic_id=$mechanic_id");
    exit;
}

// Получаем данные мастера
$stmt = $pdo->prepare("SELECT * FROM mechanic WHERE mechanic_id = ?");
$stmt->execute([$mechanic_id]);
$mechanic = $stmt->fetch();

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
                UPDATE work_log 
                SET actual_start = ?, actual_end = ?, final_price = ?, notes = ?
                WHERE work_id = ?
            ");
            $notes_value = !empty($notes) ? $notes : null;
            $stmt->execute([$actual_start, $actual_end, $final_price, $notes_value, $work_id]);
            header("Location: work_log.php?mechanic_id=$mechanic_id");
            exit;
        } catch (PDOException $e) {
            $error = 'Ошибка при обновлении работы: ' . $e->getMessage();
        }
    }
}

// Преобразуем дату из SQL формата (YYYY-MM-DD HH:MM:SS) в формат datetime-local (YYYY-MM-DDTHH:MM)
$actual_start_formatted = substr(str_replace(' ', 'T', $work['actual_start']), 0, 16);
$actual_end_formatted = substr(str_replace(' ', 'T', $work['actual_end']), 0, 16);

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать выполненную работу</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <a href="work_log.php?mechanic_id=<?= $mechanic_id ?>" class="back-link">← Назад к работам</a>
        <h1>Редактировать выполненную работу</h1>
        <p><strong>Мастер:</strong> <?= htmlspecialchars($mechanic['full_name']) ?></p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="actual_start">Дата и время начала:</label>
                <input type="datetime-local" id="actual_start" name="actual_start" required step="1"
                       value="<?= htmlspecialchars($actual_start_formatted) ?>">
            </div>
            
            <div class="form-group">
                <label for="actual_end">Дата и время окончания:</label>
                <input type="datetime-local" id="actual_end" name="actual_end" required step="1"
                       value="<?= htmlspecialchars($actual_end_formatted) ?>">
            </div>
            
            <div class="form-group">
                <label for="final_price">Стоимость (руб.):</label>
                <input type="number" id="final_price" name="final_price" step="0.01" min="0" required
                       value="<?= htmlspecialchars($work['final_price']) ?>">
            </div>
            
            <div class="form-group">
                <label for="notes">Примечания:</label>
                <textarea id="notes" name="notes" rows="4"><?= $work['notes'] ? htmlspecialchars($work['notes']) : '' ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-edit">Сохранить</button>
                <a href="work_log.php?mechanic_id=<?= $mechanic_id ?>" class="btn btn-delete">Отмена</a>
            </div>
        </form>
    </div>
</body>
</html>


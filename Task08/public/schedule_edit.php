<?php
/**
 * Редактирование записи графика работы мастера
 */

require_once '../config.php';

$schedule_id = $_GET['id'] ?? null;
$mechanic_id = $_GET['mechanic_id'] ?? null;
$error = '';

if (!$schedule_id || !$mechanic_id) {
    header('Location: index.php');
    exit;
}

// Получаем данные записи графика
$stmt = $pdo->prepare("SELECT * FROM mechanic_schedule WHERE schedule_id = ?");
$stmt->execute([$schedule_id]);
$schedule = $stmt->fetch();

if (!$schedule) {
    header("Location: schedule.php?mechanic_id=$mechanic_id");
    exit;
}

// Получаем данные мастера
$stmt = $pdo->prepare("SELECT * FROM mechanic WHERE mechanic_id = ?");
$stmt->execute([$mechanic_id]);
$mechanic = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $day_of_week = $_POST['day_of_week'] ?? '';
    $start_time = trim($_POST['start_time'] ?? '');
    $end_time = trim($_POST['end_time'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($day_of_week) || empty($start_time) || empty($end_time)) {
        $error = 'Все поля обязательны для заполнения';
    } elseif ($end_time <= $start_time) {
        $error = 'Время окончания должно быть позже времени начала';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE mechanic_schedule 
                SET day_of_week = ?, start_time = ?, end_time = ?, is_active = ?
                WHERE schedule_id = ?
            ");
            $stmt->execute([$day_of_week, $start_time, $end_time, $is_active, $schedule_id]);
            header("Location: schedule.php?mechanic_id=$mechanic_id");
            exit;
        } catch (PDOException $e) {
            $error = 'Ошибка при обновлении записи: ' . $e->getMessage();
        }
    }
}

$days = [1 => 'Понедельник', 2 => 'Вторник', 3 => 'Среда', 4 => 'Четверг', 
         5 => 'Пятница', 6 => 'Суббота', 7 => 'Воскресенье'];

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать запись графика</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <a href="schedule.php?mechanic_id=<?= $mechanic_id ?>" class="back-link">← Назад к графику</a>
        <h1>Редактировать запись графика</h1>
        <p><strong>Мастер:</strong> <?= htmlspecialchars($mechanic['full_name']) ?></p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="day_of_week">День недели:</label>
                <select id="day_of_week" name="day_of_week" required>
                    <option value="">Выберите день</option>
                    <?php foreach ($days as $num => $name): ?>
                        <option value="<?= $num ?>" 
                                <?= ($schedule['day_of_week'] == $num) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="start_time">Время начала (формат HH:MM):</label>
                <input type="time" id="start_time" name="start_time" required
                       value="<?= htmlspecialchars($schedule['start_time']) ?>">
            </div>
            
            <div class="form-group">
                <label for="end_time">Время окончания (формат HH:MM):</label>
                <input type="time" id="end_time" name="end_time" required
                       value="<?= htmlspecialchars($schedule['end_time']) ?>">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" 
                           <?= $schedule['is_active'] ? 'checked' : '' ?>>
                    Активен
                </label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-edit">Сохранить</button>
                <a href="schedule.php?mechanic_id=<?= $mechanic_id ?>" class="btn btn-delete">Отмена</a>
            </div>
        </form>
    </div>
</body>
</html>


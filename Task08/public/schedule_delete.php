<?php
/**
 * Удаление записи графика работы мастера
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

$days = [1 => 'Понедельник', 2 => 'Вторник', 3 => 'Среда', 4 => 'Четверг', 
         5 => 'Пятница', 6 => 'Суббота', 7 => 'Воскресенье'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM mechanic_schedule WHERE schedule_id = ?");
        $stmt->execute([$schedule_id]);
        header("Location: schedule.php?mechanic_id=$mechanic_id");
        exit;
    } catch (PDOException $e) {
        $error = 'Ошибка при удалении записи: ' . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удалить запись графика</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <a href="schedule.php?mechanic_id=<?= $mechanic_id ?>" class="back-link">← Назад к графику</a>
        <h1>Удалить запись графика</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="confirm-message">
            <p><strong>Вы уверены, что хотите удалить эту запись графика?</strong></p>
            <p>Мастер: <?= htmlspecialchars($mechanic['full_name']) ?></p>
            <p>День недели: <?= htmlspecialchars($days[$schedule['day_of_week']]) ?></p>
            <p>Время: <?= htmlspecialchars($schedule['start_time']) ?> - <?= htmlspecialchars($schedule['end_time']) ?></p>
        </div>
        
        <form method="POST" action="">
            <div class="form-actions">
                <button type="submit" name="confirm" value="1" class="btn btn-delete">Да, удалить</button>
                <a href="schedule.php?mechanic_id=<?= $mechanic_id ?>" class="btn btn-edit">Отмена</a>
            </div>
        </form>
    </div>
</body>
</html>


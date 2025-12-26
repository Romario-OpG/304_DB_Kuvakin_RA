<?php
/**
 * График работы мастера
 */

require_once '../config.php';

$mechanic_id = $_GET['mechanic_id'] ?? null;
$error = '';
$message = '';

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

// Получаем график работы мастера
$stmt = $pdo->prepare("
    SELECT * FROM mechanic_schedule 
    WHERE mechanic_id = ? 
    ORDER BY day_of_week, start_time
");
$stmt->execute([$mechanic_id]);
$schedules = $stmt->fetchAll();

// Названия дней недели
$days = [1 => 'Понедельник', 2 => 'Вторник', 3 => 'Среда', 4 => 'Четверг', 
         5 => 'Пятница', 6 => 'Суббота', 7 => 'Воскресенье'];

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>График работы мастера</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">← Назад к списку</a>
        <h1>График работы мастера: <?= htmlspecialchars($mechanic['full_name']) ?></h1>
        
        <table>
            <thead>
                <tr>
                    <th>День недели</th>
                    <th>Время начала</th>
                    <th>Время окончания</th>
                    <th>Активен</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($schedules)): ?>
                    <tr>
                        <td colspan="5">Записей графика не найдено</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($schedules as $schedule): ?>
                        <tr>
                            <td><?= htmlspecialchars($days[$schedule['day_of_week']]) ?></td>
                            <td><?= htmlspecialchars($schedule['start_time']) ?></td>
                            <td><?= htmlspecialchars($schedule['end_time']) ?></td>
                            <td><?= $schedule['is_active'] ? 'Да' : 'Нет' ?></td>
                            <td class="actions">
                                <a href="schedule_edit.php?id=<?= $schedule['schedule_id'] ?>&mechanic_id=<?= $mechanic_id ?>" class="btn btn-edit">Редактировать</a>
                                <a href="schedule_delete.php?id=<?= $schedule['schedule_id'] ?>&mechanic_id=<?= $mechanic_id ?>" class="btn btn-delete">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="actions-bottom">
            <a href="schedule_add.php?mechanic_id=<?= $mechanic_id ?>" class="btn btn-add">Добавить запись в график</a>
        </div>
    </div>
</body>
</html>


<?php
/**
 * Главная страница - список мастеров СТО
 */

require_once '../config.php';

// Получаем список всех мастеров, отсортированный по фамилии
$stmt = $pdo->prepare("
    SELECT mechanic_id, full_name, specialization 
    FROM mechanic 
    ORDER BY full_name
");
$stmt->execute();
$mechanics = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мастера СТО</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Список мастеров СТО</h1>
        
        <table>
            <thead>
                <tr>
                    <th>ФИО</th>
                    <th>Специализация</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($mechanics)): ?>
                    <tr>
                        <td colspan="3">Мастера не найдены</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($mechanics as $mechanic): ?>
                        <tr>
                            <td><?= htmlspecialchars($mechanic['full_name']) ?></td>
                            <td><?= htmlspecialchars($mechanic['specialization']) ?></td>
                            <td class="actions">
                                <a href="mechanic_edit.php?id=<?= $mechanic['mechanic_id'] ?>" class="btn btn-edit">Редактировать</a>
                                <a href="mechanic_delete.php?id=<?= $mechanic['mechanic_id'] ?>" class="btn btn-delete">Удалить</a>
                                <a href="schedule.php?mechanic_id=<?= $mechanic['mechanic_id'] ?>" class="btn btn-schedule">График</a>
                                <a href="work_log.php?mechanic_id=<?= $mechanic['mechanic_id'] ?>" class="btn btn-work">Выполненные работы</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="actions-bottom">
            <a href="mechanic_add.php" class="btn btn-add">Добавить мастера</a>
        </div>
    </div>
</body>
</html>


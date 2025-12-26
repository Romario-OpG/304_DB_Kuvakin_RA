<?php
/**
 * Выполненные работы мастера
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

// Получаем выполненные работы мастера, отсортированные по дате (самые новые первые)
$stmt = $pdo->prepare("
    SELECT work_id, actual_start, actual_end, final_price, notes
    FROM work_log 
    WHERE mechanic_id = ? 
    ORDER BY actual_start DESC
");
$stmt->execute([$mechanic_id]);
$works = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Выполненные работы мастера</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">← Назад к списку</a>
        <h1>Выполненные работы мастера: <?= htmlspecialchars($mechanic['full_name']) ?></h1>
        
        <table>
            <thead>
                <tr>
                    <th>Дата начала</th>
                    <th>Дата окончания</th>
                    <th>Стоимость (руб.)</th>
                    <th>Примечания</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($works)): ?>
                    <tr>
                        <td colspan="5">Выполненных работ не найдено</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($works as $work): ?>
                        <tr>
                            <td><?= htmlspecialchars($work['actual_start']) ?></td>
                            <td><?= htmlspecialchars($work['actual_end']) ?></td>
                            <td><?= number_format($work['final_price'], 2, '.', ' ') ?></td>
                            <td><?= $work['notes'] ? htmlspecialchars($work['notes']) : '-' ?></td>
                            <td class="actions">
                                <a href="work_log_edit.php?id=<?= $work['work_id'] ?>&mechanic_id=<?= $mechanic_id ?>" class="btn btn-edit">Редактировать</a>
                                <a href="work_log_delete.php?id=<?= $work['work_id'] ?>&mechanic_id=<?= $mechanic_id ?>" class="btn btn-delete">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="actions-bottom">
            <a href="work_log_add.php?mechanic_id=<?= $mechanic_id ?>" class="btn btn-add">Добавить выполненную работу</a>
        </div>
    </div>
</body>
</html>


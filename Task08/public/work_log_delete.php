<?php
/**
 * Удаление выполненной работы мастера
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM work_log WHERE work_id = ?");
        $stmt->execute([$work_id]);
        header("Location: work_log.php?mechanic_id=$mechanic_id");
        exit;
    } catch (PDOException $e) {
        $error = 'Ошибка при удалении работы: ' . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удалить выполненную работу</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <a href="work_log.php?mechanic_id=<?= $mechanic_id ?>" class="back-link">← Назад к работам</a>
        <h1>Удалить выполненную работу</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="confirm-message">
            <p><strong>Вы уверены, что хотите удалить эту работу?</strong></p>
            <p>Мастер: <?= htmlspecialchars($mechanic['full_name']) ?></p>
            <p>Дата начала: <?= htmlspecialchars($work['actual_start']) ?></p>
            <p>Дата окончания: <?= htmlspecialchars($work['actual_end']) ?></p>
            <p>Стоимость: <?= number_format($work['final_price'], 2, '.', ' ') ?> руб.</p>
            <?php if ($work['notes']): ?>
                <p>Примечания: <?= htmlspecialchars($work['notes']) ?></p>
            <?php endif; ?>
        </div>
        
        <form method="POST" action="">
            <div class="form-actions">
                <button type="submit" name="confirm" value="1" class="btn btn-delete">Да, удалить</button>
                <a href="work_log.php?mechanic_id=<?= $mechanic_id ?>" class="btn btn-edit">Отмена</a>
            </div>
        </form>
    </div>
</body>
</html>


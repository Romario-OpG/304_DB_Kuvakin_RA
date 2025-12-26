<?php
/**
 * Удаление мастера
 */

require_once '../config.php';

$mechanic_id = $_GET['id'] ?? null;
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM mechanic WHERE mechanic_id = ?");
        $stmt->execute([$mechanic_id]);
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        $error = 'Ошибка при удалении мастера: ' . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удалить мастера</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">← Назад к списку</a>
        <h1>Удалить мастера</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="confirm-message">
            <p><strong>Вы уверены, что хотите удалить мастера?</strong></p>
            <p>ФИО: <?= htmlspecialchars($mechanic['full_name']) ?></p>
            <p>Специализация: <?= htmlspecialchars($mechanic['specialization']) ?></p>
            <p class="alert alert-error">Внимание! Это действие необратимо. Также будут удалены связанные записи графика и выполненных работ.</p>
        </div>
        
        <form method="POST" action="">
            <div class="form-actions">
                <button type="submit" name="confirm" value="1" class="btn btn-delete">Да, удалить</button>
                <a href="index.php" class="btn btn-edit">Отмена</a>
            </div>
        </form>
    </div>
</body>
</html>


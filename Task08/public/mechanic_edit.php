<?php
/**
 * Редактирование мастера
 */

require_once '../config.php';

$mechanic_id = $_GET['id'] ?? null;
$message = '';
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
    $full_name = trim($_POST['full_name'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $commission_rate = $_POST['commission_rate'] ?? '';
    $hire_date = $_POST['hire_date'] ?? '';
    $dismissal_date = !empty($_POST['dismissal_date']) ? $_POST['dismissal_date'] : null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($full_name) || empty($specialization) || empty($commission_rate) || empty($hire_date)) {
        $error = 'Все обязательные поля должны быть заполнены';
    } elseif (!is_numeric($commission_rate) || $commission_rate < 0 || $commission_rate > 100) {
        $error = 'Процент комиссии должен быть числом от 0 до 100';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE mechanic 
                SET full_name = ?, specialization = ?, commission_rate = ?, 
                    hire_date = ?, dismissal_date = ?, is_active = ?
                WHERE mechanic_id = ?
            ");
            $stmt->execute([$full_name, $specialization, $commission_rate, $hire_date, $dismissal_date, $is_active, $mechanic_id]);
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Ошибка при обновлении данных: ' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать мастера</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">← Назад к списку</a>
        <h1>Редактировать мастера</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="full_name">ФИО:</label>
                <input type="text" id="full_name" name="full_name" required 
                       value="<?= htmlspecialchars($mechanic['full_name']) ?>">
            </div>
            
            <div class="form-group">
                <label for="specialization">Специализация:</label>
                <input type="text" id="specialization" name="specialization" required
                       value="<?= htmlspecialchars($mechanic['specialization']) ?>">
            </div>
            
            <div class="form-group">
                <label for="commission_rate">Процент комиссии (0-100):</label>
                <input type="number" id="commission_rate" name="commission_rate" step="0.1" min="0" max="100" required
                       value="<?= htmlspecialchars($mechanic['commission_rate']) ?>">
            </div>
            
            <div class="form-group">
                <label for="hire_date">Дата приёма на работу:</label>
                <input type="date" id="hire_date" name="hire_date" required
                       value="<?= htmlspecialchars($mechanic['hire_date']) ?>">
            </div>
            
            <div class="form-group">
                <label for="dismissal_date">Дата увольнения (если применимо):</label>
                <input type="date" id="dismissal_date" name="dismissal_date"
                       value="<?= $mechanic['dismissal_date'] ? htmlspecialchars($mechanic['dismissal_date']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" 
                           <?= $mechanic['is_active'] ? 'checked' : '' ?>>
                    Активен
                </label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-edit">Сохранить</button>
                <a href="index.php" class="btn btn-delete">Отмена</a>
            </div>
        </form>
    </div>
</body>
</html>


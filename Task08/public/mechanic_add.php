<?php
/**
 * Добавление нового мастера
 */

require_once '../config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $commission_rate = $_POST['commission_rate'] ?? '';
    $hire_date = $_POST['hire_date'] ?? '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($full_name) || empty($specialization) || empty($commission_rate) || empty($hire_date)) {
        $error = 'Все поля обязательны для заполнения';
    } elseif (!is_numeric($commission_rate) || $commission_rate < 0 || $commission_rate > 100) {
        $error = 'Процент комиссии должен быть числом от 0 до 100';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO mechanic (full_name, specialization, commission_rate, hire_date, is_active)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$full_name, $specialization, $commission_rate, $hire_date, $is_active]);
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Ошибка при добавлении мастера: ' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить мастера</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">← Назад к списку</a>
        <h1>Добавить мастера</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="full_name">ФИО:</label>
                <input type="text" id="full_name" name="full_name" required 
                       value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="specialization">Специализация:</label>
                <input type="text" id="specialization" name="specialization" required
                       value="<?= isset($_POST['specialization']) ? htmlspecialchars($_POST['specialization']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="commission_rate">Процент комиссии (0-100):</label>
                <input type="number" id="commission_rate" name="commission_rate" step="0.1" min="0" max="100" required
                       value="<?= isset($_POST['commission_rate']) ? htmlspecialchars($_POST['commission_rate']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="hire_date">Дата приёма на работу:</label>
                <input type="date" id="hire_date" name="hire_date" required
                       value="<?= isset($_POST['hire_date']) ? htmlspecialchars($_POST['hire_date']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" 
                           <?= (isset($_POST['is_active']) || !isset($_POST['is_active'])) ? 'checked' : '' ?>>
                    Активен
                </label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-add">Добавить</button>
                <a href="index.php" class="btn btn-edit">Отмена</a>
            </div>
        </form>
    </div>
</body>
</html>


<?php
/**
 * Скрипт для инициализации базы данных SQLite
 * Запустите этот файл один раз для создания базы данных и таблиц
 */

$dbPath = __DIR__ . '/data/sto.db';
$sqlFile = __DIR__ . '/../db_init.sql';

// Проверяем наличие SQL-файла
if (!file_exists($sqlFile)) {
    die("Ошибка: SQL-файл не найден: $sqlFile\n");
}

// Удаляем старую базу данных, если она существует
if (file_exists($dbPath)) {
    unlink($dbPath);
    echo "Старая база данных удалена.\n";
}

// Создаём новую базу данных
try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Читаем SQL-файл
    $sql = file_get_contents($sqlFile);
    
    // Удаляем комментарии (однострочные)
    $lines = explode("\n", $sql);
    $cleanedLines = [];
    foreach ($lines as $line) {
        // Удаляем однострочные комментарии
        $pos = strpos($line, '--');
        if ($pos !== false) {
            $line = substr($line, 0, $pos);
        }
        $cleanedLines[] = $line;
    }
    $sql = implode("\n", $cleanedLines);
    
    // Удаляем многострочные комментарии
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    // Разбиваем на отдельные запросы по точке с запятой
    // Используем более надежный метод для разделения запросов
    $queries = [];
    $currentQuery = '';
    $inString = false;
    $stringChar = '';
    
    for ($i = 0; $i < strlen($sql); $i++) {
        $char = $sql[$i];
        $currentQuery .= $char;
        
        // Отслеживаем строки в кавычках
        if (($char == '"' || $char == "'") && ($i == 0 || $sql[$i-1] != '\\')) {
            if (!$inString) {
                $inString = true;
                $stringChar = $char;
            } elseif ($char == $stringChar) {
                $inString = false;
                $stringChar = '';
            }
        }
        
        // Если находим точку с запятой вне строки, это конец запроса
        if ($char == ';' && !$inString) {
            $query = trim($currentQuery);
            if (!empty($query) && $query != ';') {
                $queries[] = $query;
            }
            $currentQuery = '';
        }
    }
    
    // Добавляем последний запрос, если он есть
    $lastQuery = trim($currentQuery);
    if (!empty($lastQuery)) {
        $queries[] = $lastQuery;
    }
    
    // Выполняем каждый запрос
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            try {
                $pdo->exec($query);
            } catch (PDOException $e) {
                // Пропускаем ошибки типа "table already exists" при повторном запуске
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "Предупреждение при выполнении запроса: " . $e->getMessage() . "\n";
                    echo "Запрос: " . substr($query, 0, 100) . "...\n";
                }
            }
        }
    }
    
    echo "База данных успешно создана: $dbPath\n";
    echo "Таблицы и данные загружены.\n";
    
} catch (PDOException $e) {
    die("Ошибка при создании базы данных: " . $e->getMessage() . "\n");
}


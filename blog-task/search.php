<?php
$db_host = 'localhost';
$db_name = 'test';
$db_user = 'root';
$db_pass = '';

$search_results = [];
$search_query = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search']) && strlen($_GET['search']) >= 3) {
    $search_query = trim($_GET['search']);
    
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("
            SELECT p.title, c.body 
            FROM comments c
            JOIN posts p ON c.post_id = p.id
            WHERE c.body LIKE :search
        ");
        
        $stmt->execute([':search' => "%$search_query%"]);
        $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        die("Ошибка базы данных: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Поиск записей по комментариям</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .search-form { margin-bottom: 30px; }
        .search-input { padding: 8px; width: 300px; }
        .search-button { padding: 8px 15px; }
        .result-item { margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .result-title { font-weight: bold; margin-bottom: 5px; }
        .result-comment { color: #555; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Поиск записей по комментариям</h1>
    
    <div class="search-form">
        <form method="GET" action="">
            <input type="text" name="search" class="search-input" 
                   placeholder="Введите минимум 3 символа..." 
                   value="<?= htmlspecialchars($search_query) ?>">
            <button type="submit" class="search-button">Найти</button>
        </form>
        <?php if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search']) && strlen($_GET['search']) < 3): ?>
            <p class="error">Пожалуйста, введите минимум 3 символа для поиска.</p>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($search_results)): ?>
        <h2>Результаты поиска (<?= count($search_results) ?>):</h2>
        <?php foreach ($search_results as $result): ?>
            <div class="result-item">
                <div class="result-title"><?= htmlspecialchars($result['title']) ?></div>
                <div class="result-comment"><?= htmlspecialchars($result['body']) ?></div>
            </div>
        <?php endforeach; ?>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search']) && strlen($_GET['search']) >= 3): ?>
        <p>Ничего не найдено.</p>
    <?php endif; ?>
</body>
</html>
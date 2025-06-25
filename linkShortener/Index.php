<?php
require_once 'db.php';

const alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != '/') {
    $code = trim($_SERVER['REQUEST_URI'], '/');

    if (preg_match('/^[a-zA-Z0-9]{6}$/', $code)) {
        $db = getDb();
        $stmt = $db->prepare("SELECT url FROM links WHERE code = ?");
        $stmt->execute([$code]);
        $url = $stmt->fetchColumn();
        if ($url) {
            header("Location: " . $url);
            exit;
        }
    }
    http_response_code(404);
    echo "ССылка не найдена";
    exit;
}

$shortUrl = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $url = trim($_POST['url']);
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $error = "Некорректный URL";
    } else {
        $code = substr(str_shuffle(alphabet), 0, 6);
        $db = getDb();
        $stmt = $db->prepare("SELECT COUNT(*) FROM links WHERE code = ?");
        $stmt->execute([$code]);
        while ($stmt->fetchColumn() > 0) {
            $code = substr(str_shuffle(alphabet), 0, 6);
            $stmt->execute([$code]);
        }

        $stmt = $db->prepare("INSERT INTO links (code, url) VALUES (?, ?)");
        $stmt->execute([$code, $url]);

        $shortUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/' . $code;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Сокращатель ссылок</title>
</head>
<body>
<h1>Сократить ссылку</h1>
<form method="post">
    <input type="text" name="url" placeholder="Введите длинную ссылку" required>
    <button type="submit">Сократить</button>
</form>

<?php if ($error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($shortUrl): ?>
    <p>Короткая ссылка: <a href="<?= $shortUrl ?>"><?= $shortUrl ?></a></p>
<?php endif; ?>
</body>
</html>

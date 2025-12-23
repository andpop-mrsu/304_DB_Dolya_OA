<?php
require_once __DIR__ . '/../src/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Некорректный ID");
}

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT full_name FROM Students WHERE id = ?");
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    die("Студент не найден");
}

$stmt = $pdo->prepare("DELETE FROM Students WHERE id = ?");
$stmt->execute([$id]);

header("Location: index.php");
exit;

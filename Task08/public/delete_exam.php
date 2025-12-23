<?php
require_once __DIR__ . '/../src/db.php';

$id = $_GET['id'] ?? null;
$studentId = $_GET['student_id'] ?? null;

if (!is_numeric($id) || !is_numeric($studentId)) {
    die("Некорректные параметры");
}

$id = (int)$id;
$studentId = (int)$studentId;

$stmt = $pdo->prepare("DELETE FROM Grades WHERE id = ? AND student_id = ?");
$stmt->execute([$id, $studentId]);

header("Location: exam_results.php?student_id=$studentId");
exit;

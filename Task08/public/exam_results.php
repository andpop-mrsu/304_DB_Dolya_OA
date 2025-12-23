<?php
require_once __DIR__ . '/../src/db.php';

if (!isset($_GET['student_id']) || !is_numeric($_GET['student_id'])) {
    die("Некорректный ID студента");
}

$studentId = (int)$_GET['student_id'];

$stmt = $pdo->prepare("
    SELECT s.full_name, s.study_plan_id 
    FROM Students s 
    WHERE s.id = ?
");
$stmt->execute([$studentId]);
$student = $stmt->fetch();
if (!$student) {
    die("Студент не найден");
}

$sql = "
    SELECT 
        g.id AS grade_id,
        g.grade,
        g.date_recorded,
        s.name AS subject_name,
        ci.total_hours,
        ci.assessment_type
    FROM Grades g
    JOIN CurriculumItems ci ON g.curriculum_item_id = ci.id
    JOIN Subjects s ON ci.subject_id = s.id
    WHERE g.student_id = ?
    ORDER BY g.date_recorded ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$studentId]);
$grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM CurriculumItems 
    WHERE study_plan_id = ?
");
$stmt->execute([$student['study_plan_id']]);
$totalItems = (int)$stmt->fetchColumn();

$completedItems = count($grades);
$canAddGrade = ($completedItems < $totalItems);
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Результаты экзаменов — <?= htmlspecialchars($student['full_name']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h1 {
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .actions a {
            margin: 0 5px;
            text-decoration: none;
            color: #0066cc;
        }

        .actions a.delete {
            color: red;
        }

        .back {
            margin-top: 15px;
        }

        .add-btn {
            display: inline-block;
            padding: 6px 12px;
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 3px;
        }

        .info {
            color: #666;
        }
    </style>
</head>

<body>

    <h1>Результаты экзаменов</h1>
    <p><strong>Студент:</strong> <?= htmlspecialchars($student['full_name']) ?></p>

    <?php if (empty($grades)): ?>
        <p>Нет результатов сдачи экзаменов.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Предмет</th>
                    <th>Форма контроля</th>
                    <th>Оценка</th>
                    <th>Часов</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grades as $g): ?>
                    <tr>
                        <td><?= htmlspecialchars($g['date_recorded']) ?></td>
                        <td><?= htmlspecialchars($g['subject_name']) ?></td>
                        <td><?= $g['assessment_type'] === 'exam' ? 'Экзамен' : 'Зачёт' ?></td>
                        <td><?= $g['grade'] ?></td>
                        <td><?= $g['total_hours'] ?></td>
                        <td class="actions">
                            <a href="edit_exam.php?id=<?= $g['grade_id'] ?>&student_id=<?= $studentId ?>">Изменить</a>
                            <a href="delete_exam.php?id=<?= $g['grade_id'] ?>&student_id=<?= $studentId ?>"
                                class="delete"
                                onclick="return confirm('Удалить эту оценку?')">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($canAddGrade): ?>
        <p>
            <a href="add_exam.php">Добавить оценку (любой курс/задним числом)</a>
        </p>
    <?php else: ?>
        <p class="info">Все предметы учебного плана уже сданы.</p>
    <?php endif; ?>

    <p class="back">
        <a href="index.php">&larr; Назад к списку студентов</a>
    </p>

</body>

</html>
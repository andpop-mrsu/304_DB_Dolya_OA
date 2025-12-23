<?php
require_once __DIR__ . '/../src/db.php';

$studentId = $_GET['student_id'] ?? null;
$gradeId = $_GET['id'] ?? null;

if (!$studentId || !is_numeric($studentId)) {
    die("Требуется ID студента");
}
$studentId = (int)$studentId;

$stmt = $pdo->prepare("SELECT full_name, study_plan_id FROM Students WHERE id = ?");
$stmt->execute([$studentId]);
$student = $stmt->fetch();
if (!$student) {
    die("Студент не найден");
}

$isEdit = !empty($gradeId);
$gradeData = null;

if ($isEdit) {
    $stmt = $pdo->prepare("
        SELECT g.*, ci.study_plan_id
        FROM Grades g
        JOIN CurriculumItems ci ON g.curriculum_item_id = ci.id
        WHERE g.id = ? AND g.student_id = ?
    ");
    $stmt->execute([$gradeId, $studentId]);
    $gradeData = $stmt->fetch();
    if (!$gradeData) {
        die("Оценка не найдена");
    }

    $stmt = $pdo->prepare("
        SELECT ci.id, s.name AS subject_name, ci.assessment_type, ci.total_hours
        FROM CurriculumItems ci
        JOIN Subjects s ON ci.subject_id = s.id
        WHERE ci.study_plan_id = ?
        ORDER BY s.name
    ");
    $stmt->execute([$student['study_plan_id']]);
    $curriculumItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("
        SELECT ci.id, s.name AS subject_name, ci.assessment_type, ci.total_hours
        FROM CurriculumItems ci
        JOIN Subjects s ON ci.subject_id = s.id
        WHERE ci.study_plan_id = ?
          AND ci.id NOT IN (
              SELECT curriculum_item_id 
              FROM Grades 
              WHERE student_id = ?
          )
        ORDER BY s.name
    ");
    $stmt->execute([$student['study_plan_id'], $studentId]);
    $curriculumItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$error = '';

if ($_POST) {
    try {
        $curriculum_item_id = (int)$_POST['curriculum_item_id'];
        $grade = (int)$_POST['grade'];
        $date_recorded = $_POST['date_recorded'];

        // Дополнительная проверка: предмет должен быть в учебном плане студента
        $valid = false;
        foreach ($curriculumItems as $item) {
            if ($item['id'] == $curriculum_item_id) {
                $valid = true;
                break;
            }
        }
        if (!$valid) {
            throw new Exception("Выбранный предмет недоступен для этого студента");
        }

        if (!in_array($grade, [2, 3, 4, 5])) {
            throw new Exception("Оценка должна быть от 2 до 5");
        }

        if ($isEdit) {
            $stmt = $pdo->prepare("
                UPDATE Grades 
                SET curriculum_item_id = ?, grade = ?, date_recorded = ?
                WHERE id = ? AND student_id = ?
            ");
            $stmt->execute([$curriculum_item_id, $grade, $date_recorded, $gradeId, $studentId]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO Grades (student_id, curriculum_item_id, grade, date_recorded)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$studentId, $curriculum_item_id, $grade, $date_recorded]);
        }

        header("Location: exam_results.php?student_id=$studentId");
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title><?= $isEdit ? 'Редактировать оценку' : 'Добавить оценку' ?></title>
    <style>
        body {
            font-family: Arial;
            margin: 20px;
        }

        .form-group {
            margin: 12px 0;
        }

        label {
            display: inline-block;
            width: 200px;
        }

        input,
        select {
            padding: 6px;
            width: 250px;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        .btn {
            margin-top: 15px;
        }

        .back {
            margin-top: 15px;
        }
    </style>
</head>

<body>

    <h1><?= $isEdit ? 'Редактирование оценки' : 'Добавление оценки' ?></h1>
    <p><strong>Студент:</strong> <?= htmlspecialchars($student['full_name']) ?></p>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Предмет и контроль:</label>
            <select name="curriculum_item_id" required>
                <option value="">— Выберите —</option>
                <?php if (empty($curriculumItems)): ?>
                    <option disabled>Все предметы уже сданы</option>
                <?php else: ?>
                    <?php foreach ($curriculumItems as $item): ?>
                        <option value="<?= $item['id'] ?>"
                            <?= ($gradeData['curriculum_item_id'] ?? 0) == $item['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars("{$item['subject_name']} — " . ($item['assessment_type'] === 'exam' ? 'экзамен' : 'зачёт') . " ({$item['total_hours']} ч.)") ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Оценка:</label>
            <select name="grade" required>
                <option value="">— Выберите —</option>
                <option value="5" <?= ($gradeData['grade'] ?? 0) == 5 ? 'selected' : '' ?>>5 (отлично)</option>
                <option value="4" <?= ($gradeData['grade'] ?? 0) == 4 ? 'selected' : '' ?>>4 (хорошо)</option>
                <option value="3" <?= ($gradeData['grade'] ?? 0) == 3 ? 'selected' : '' ?>>3 (удовл.)</option>
                <option value="2" <?= ($gradeData['grade'] ?? 0) == 2 ? 'selected' : '' ?>>2 (неуд.)</option>
            </select>
        </div>

        <div class="form-group">
            <label>Дата сдачи:</label>
            <input type="date" name="date_recorded"
                value="<?= $gradeData['date_recorded'] ?? date('Y-m-d') ?>" required>
        </div>

        <div class="btn">
            <button type="submit" <?= empty($curriculumItems) && !$isEdit ? 'disabled' : '' ?>>
                <?= $isEdit ? 'Сохранить' : 'Добавить' ?>
            </button>
            <a href="exam_results.php?student_id=<?= $studentId ?>">Отмена</a>
        </div>
    </form>

    <p class="back">
        <a href="exam_results.php?student_id=<?= $studentId ?>">&larr; Назад к результатам</a>
    </p>

</body>

</html>
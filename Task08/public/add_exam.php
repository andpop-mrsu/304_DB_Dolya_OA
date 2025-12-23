<?php
require_once __DIR__ . '/../src/db.php';

$groups = $pdo->query("SELECT id, name FROM Groups ORDER BY name")->fetchAll();

$selectedGroup = null;
$students = [];
$selectedStudent = null;
$studyPlans = [];
$selectedPlan = null;
$curriculumItems = [];
$error = '';

if ($_POST) {
    try {
        $student_id = (int)($_POST['student_id'] ?? 0);
        $curriculum_item_id = (int)($_POST['curriculum_item_id'] ?? 0);
        $grade = (int)($_POST['grade'] ?? 0);
        $date_recorded = $_POST['date_recorded'] ?? '';

        if (!$student_id || !$curriculum_item_id || !in_array($grade, [2, 3, 4, 5]) || !$date_recorded) {
            throw new Exception("Заполните все поля");
        }

        $stmt = $pdo->prepare("SELECT id FROM Students WHERE id = ?");
        $stmt->execute([$student_id]);
        if (!$stmt->fetch()) {
            throw new Exception("Студент не найден");
        }

        $stmt = $pdo->prepare("SELECT id FROM CurriculumItems WHERE id = ?");
        $stmt->execute([$curriculum_item_id]);
        if (!$stmt->fetch()) {
            throw new Exception("Недопустимый предмет");
        }

        $stmt = $pdo->prepare("
            SELECT id FROM Grades WHERE student_id = ? AND curriculum_item_id = ?
        ");
        $stmt->execute([$student_id, $curriculum_item_id]);
        if ($stmt->fetch()) {
            throw new Exception("Этот студент уже имеет оценку по выбранному предмету");
        }

        $stmt = $pdo->prepare("
            INSERT INTO Grades (student_id, curriculum_item_id, grade, date_recorded)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$student_id, $curriculum_item_id, $grade, $date_recorded]);

        header("Location: exam_results.php?student_id=$student_id");
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$groupId = $_GET['group'] ?? null;
$studentId = $_GET['student'] ?? null;
$planId = $_GET['plan'] ?? null;

if ($groupId) {
    $groupId = (int)$groupId;
    $stmt = $pdo->prepare("SELECT id, full_name FROM Students WHERE group_id = ? ORDER BY full_name");
    $stmt->execute([$groupId]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $selectedGroup = $groupId;
}

if ($studentId) {
    $studentId = (int)$studentId;
    $stmt = $pdo->prepare("
        SELECT s.id, s.full_name, p.id AS program_id
        FROM Students s
        JOIN Groups g ON s.group_id = g.id
        JOIN Programs p ON g.program_id = p.id
        WHERE s.id = ?
    ");
    $stmt->execute([$studentId]);
    $selectedStudent = $stmt->fetch();
    if ($selectedStudent) {
        $stmt = $pdo->prepare("
            SELECT id, academic_year_start
            FROM StudyPlans
            WHERE program_id = ?
            ORDER BY academic_year_start DESC
        ");
        $stmt->execute([$selectedStudent['program_id']]);
        $studyPlans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if ($planId) {
    $planId = (int)$planId;
    $stmt = $pdo->prepare("
        SELECT ci.id, s.name AS subject_name, ci.assessment_type, ci.total_hours
        FROM CurriculumItems ci
        JOIN Subjects s ON ci.subject_id = s.id
        WHERE ci.study_plan_id = ?
        ORDER BY s.name
    ");
    $stmt->execute([$planId]);
    $curriculumItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $selectedPlan = $planId;
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Добавить результат экзамена</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .form-step {
            margin: 15px 0;
        }

        label {
            display: inline-block;
            width: 200px;
        }

        select,
        input {
            padding: 6px;
            margin: 5px 0;
        }

        .btn {
            margin-top: 15px;
        }

        .error {
            color: red;
            margin: 10px 0;
        }

        a {
            text-decoration: none;
            color: #0066cc;
        }
    </style>
</head>

<body>

    <h1>Добавить результат экзамена (задним числом)</h1>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="GET">
        <div class="form-step">
            <label>1. Выберите группу:</label>
            <select name="group" onchange="this.form.submit()" required>
                <option value="">— Выберите —</option>
                <?php foreach ($groups as $g): ?>
                    <option value="<?= $g['id'] ?>" <?= ($selectedGroup == $g['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($g['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <?php if ($students): ?>
        <form method="GET">
            <input type="hidden" name="group" value="<?= $selectedGroup ?>">
            <div class="form-step">
                <label>2. Выберите студента:</label>
                <select name="student" onchange="this.form.submit()" required>
                    <option value="">— Выберите —</option>
                    <?php foreach ($students as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ($selectedStudent['id'] ?? 0) == $s['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    <?php endif; ?>

    <?php if ($studyPlans): ?>
        <form method="GET">
            <input type="hidden" name="group" value="<?= $selectedGroup ?>">
            <input type="hidden" name="student" value="<?= $selectedStudent['id'] ?>">
            <div class="form-step">
                <label>3. Учебный год:</label>
                <select name="plan" onchange="this.form.submit()" required>
                    <option value="">— Выберите год обучения —</option>
                    <?php foreach ($studyPlans as $sp): ?>
                        <option value="<?= $sp['id'] ?>" <?= ($selectedPlan == $sp['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars("{$sp['academic_year_start']}–" . ($sp['academic_year_start'] + 1)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    <?php endif; ?>

    <?php if ($curriculumItems && $selectedStudent): ?>
        <form method="POST">
            <input type="hidden" name="student_id" value="<?= $selectedStudent['id'] ?>">

            <div class="form-step">
                <label>4. Предмет:</label>
                <select name="curriculum_item_id" required>
                    <option value="">— Выберите дисциплину —</option>
                    <?php foreach ($curriculumItems as $ci): ?>
                        <option value="<?= $ci['id'] ?>">
                            <?= htmlspecialchars($ci['subject_name'] . ' (' . ($ci['assessment_type'] === 'exam' ? 'экзамен' : 'зачёт') . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-step">
                <label>Оценка:</label>
                <select name="grade" required>
                    <option value="">— Выберите —</option>
                    <option value="5">5 (отлично)</option>
                    <option value="4">4 (хорошо)</option>
                    <option value="3">3 (удовл.)</option>
                    <option value="2">2 (неуд.)</option>
                </select>
            </div>

            <div class="form-step">
                <label>Дата сдачи:</label>
                <input type="date" name="date_recorded" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="btn">
                <button type="submit">Сохранить оценку</button>
                <a href="exam_results.php?student_id=<?= $selectedStudent['id'] ?>">Отмена</a>
            </div>
        </form>
    <?php endif; ?>

    <p><a href="index.php">&larr; Назад к списку студентов</a></p>

</body>

</html>
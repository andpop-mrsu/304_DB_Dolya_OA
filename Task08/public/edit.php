<?php
require_once __DIR__ . '/../src/db.php';

$groups = $pdo->query("SELECT id, name FROM Groups ORDER BY name")->fetchAll();

$studyPlans = $pdo->query("
    SELECT sp.id, p.name AS program, sp.academic_year_start 
    FROM StudyPlans sp 
    JOIN Programs p ON sp.program_id = p.id 
    ORDER BY p.name, sp.academic_year_start
")->fetchAll();

$student = null;
$isEdit = false;
$error = '';

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM Students WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student) {
        die("Студент не найден");
    }
    $isEdit = true;
}

if ($_POST) {
    try {
        $full_name = trim($_POST['full_name']);
        $group_id = (int)$_POST['group_id'];
        $study_plan_id = (int)$_POST['study_plan_id'];
        $birth_date = $_POST['birth_date'];
        $gender = $_POST['gender'] ?? null;
        $card = trim($_POST['student_card_number']);

        if (empty($full_name) || strlen($full_name) < 3) {
            throw new Exception("ФИО должно содержать минимум 3 символа");
        }

        if (!in_array($gender, ['M', 'F'])) {
            throw new Exception("Укажите пол");
        }

        if ($isEdit) {
            $stmt = $pdo->prepare("
                UPDATE Students 
                SET group_id = ?, study_plan_id = ?, full_name = ?, birth_date = ?, gender = ?, student_card_number = ?
                WHERE id = ?
            ");
            $stmt->execute([$group_id, $study_plan_id, $full_name, $birth_date, $gender, $card, $_GET['id']]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO Students (group_id, study_plan_id, full_name, birth_date, gender, student_card_number)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$group_id, $study_plan_id, $full_name, $birth_date, $gender, $card]);
        }
        header("Location: index.php");
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
    <title><?= $isEdit ? 'Редактировать студента' : 'Добавить студента' ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .form-group {
            margin: 12px 0;
        }

        label {
            display: inline-block;
            width: 200px;
        }

        input[type="text"],
        input[type="date"],
        select {
            padding: 6px;
            width: 250px;
        }

        .radio-group label {
            width: auto;
            margin-right: 15px;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        .btn {
            margin-top: 15px;
        }
    </style>
</head>

<body>

    <h1><?= $isEdit ? 'Редактирование студента' : 'Добавление студента' ?></h1>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>ФИО:</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($student['full_name'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>Группа:</label>
            <select name="group_id" required>
                <option value="">— Выберите группу —</option>
                <?php foreach ($groups as $g): ?>
                    <option value="<?= $g['id'] ?>" <?= ($student['group_id'] ?? 0) == $g['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($g['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Учебный план:</label>
            <select name="study_plan_id" required>
                <option value="">— Выберите учебный план —</option>
                <?php foreach ($studyPlans as $sp): ?>
                    <option value="<?= $sp['id'] ?>" <?= ($student['study_plan_id'] ?? 0) == $sp['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars("{$sp['program']} ({$sp['academic_year_start']})") ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Дата рождения:</label>
            <input type="date" name="birth_date" value="<?= $student['birth_date'] ?? date('Y-m-d') ?>" required>
        </div>

        <div class="form-group">
            <label>Пол:</label>
            <div class="radio-group">
                <label>
                    <input type="radio" name="gender" value="M"
                        <?= ($student['gender'] ?? 'M') === 'M' ? 'checked' : '' ?>>
                    Мужской
                </label>
                <label>
                    <input type="radio" name="gender" value="F"
                        <?= ($student['gender'] ?? 'M') === 'F' ? 'checked' : '' ?>>
                    Женский
                </label>
            </div>
        </div>

        <div class="form-group">
            <label>Номер студ. билета:</label>
            <input type="text" name="student_card_number" value="<?= htmlspecialchars($student['student_card_number'] ?? '') ?>" required>
        </div>

        <div class="btn">
            <button type="submit"><?= $isEdit ? 'Сохранить' : 'Добавить' ?></button>
            <a href="index.php">Отмена</a>
        </div>
    </form>

</body>

</html>
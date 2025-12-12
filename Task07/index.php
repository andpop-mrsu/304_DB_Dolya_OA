<?php
define('DB_PATH', __DIR__ . '/db.sqlite');

try {
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Ошибка БД: ' . htmlspecialchars($e->getMessage()));
}

$groups = $db->query("
    SELECT DISTINCT g.name
    FROM Groups g
    WHERE g.graduation_year <= strftime('%Y', 'now')
    ORDER BY g.name
")->fetchAll(PDO::FETCH_COLUMN);

$selectedGroup = $_GET['group'] ?? null;
if ($selectedGroup && !in_array($selectedGroup, $groups)) {
    $selectedGroup = null;
}

$query = "
    SELECT 
        g.name AS group_number,
        p.name AS program_name,
        s.full_name,
        s.gender,
        s.birth_date,
        s.student_card_number
    FROM Students s
    JOIN Groups g ON s.group_id = g.id
    JOIN Programs p ON g.program_id = p.id
    WHERE g.graduation_year <= strftime('%Y', 'now')
";

$params = [];
if ($selectedGroup) {
    $query .= " AND g.name = ?";
    $params[] = $selectedGroup;
}

$query .= " ORDER BY g.name, s.full_name";

$stmt = $db->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Список студентов</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 1rem;
        }

        th,
        td {
            border: 1px solid #aaa;
            padding: 8px 12px;
            text-align: left;
        }

        th {
            background-color: #f5f5f5;
        }

        select,
        button {
            padding: 6px 10px;
            margin: 5px 0;
        }
    </style>
</head>

<body>
    <h1>Список студентов (группы с годом окончания ≤ <?= date('Y') ?>)</h1>

    <form method="get">
        <label>Фильтр по группе:</label>
        <select name="group" onchange="this.form.submit()">
            <option value="">Все группы</option>
            <?php foreach ($groups as $group): ?>
                <option value="<?= htmlspecialchars($group) ?>" <?= $selectedGroup === $group ? 'selected' : '' ?>>
                    <?= htmlspecialchars($group) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <noscript><button>Применить</button></noscript>
    </form>

    <?php if (empty($students)): ?>
        <p>Студенты не найдены.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Группа</th>
                    <th>Направление подготовки</th>
                    <th>ФИО</th>
                    <th>Пол</th>
                    <th>Дата рождения</th>
                    <th>№ студ. билета</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['group_number']) ?></td>
                        <td><?= htmlspecialchars($s['program_name']) ?></td>
                        <td><?= htmlspecialchars($s['full_name']) ?></td>
                        <td><?= htmlspecialchars($s['gender'] === 'M' ? 'М' : 'Ж') ?></td>
                        <td><?= htmlspecialchars($s['birth_date']) ?></td>
                        <td><?= htmlspecialchars($s['student_card_number']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>

</html>
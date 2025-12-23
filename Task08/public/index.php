<?php
require_once __DIR__ . '/../src/db.php';

$stmt = $pdo->query("SELECT name FROM Groups ORDER BY name");
$groups = $stmt->fetchAll(PDO::FETCH_COLUMN);

$filterGroup = $_GET['group'] ?? null;

$sql = "
    SELECT 
        s.id,
        s.full_name,
        g.name AS group_name,
        s.student_card_number,
        s.birth_date,
        s.gender
    FROM Students s
    JOIN Groups g ON s.group_id = g.id
";

$params = [];
if ($filterGroup) {
    $sql .= " WHERE g.name = :group_name";
    $params[':group_name'] = $filterGroup;
}
$sql .= " ORDER BY g.name, s.full_name";

$stmt = $pdo->prepare($sql);
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
            margin: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
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

        .filter {
            margin-bottom: 15px;
        }

        select,
        button {
            padding: 5px;
            margin-right: 10px;
        }

        .actions a {
            margin: 0 5px;
            text-decoration: none;
            color: #0066cc;
        }

        .actions a.delete {
            color: red;
        }

        .add-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
    </style>
</head>

<body>

    <h1>Список студентов</h1>

    <div class="filter">
        <form method="GET">
            <label>Группа:</label>
            <select name="group" onchange="this.form.submit()">
                <option value="">Все группы</option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?= htmlspecialchars($group) ?>" <?= $filterGroup === $group ? 'selected' : '' ?>>
                        <?= htmlspecialchars($group) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($filterGroup): ?>
                <a href="index.php">Сбросить фильтр</a>
            <?php endif; ?>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ФИО</th>
                <th>Группа</th>
                <th>Номер студ. билета</th>
                <th>Дата рождения</th>
                <th>Пол</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($students)): ?>
                <tr>
                    <td colspan="6">Студенты не найдены</td>
                </tr>
            <?php else: ?>
                <?php foreach ($students as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['full_name']) ?></td>
                        <td><?= htmlspecialchars($s['group_name']) ?></td>
                        <td><?= htmlspecialchars($s['student_card_number']) ?></td>
                        <td><?= htmlspecialchars($s['birth_date']) ?></td>
                        <td><?= $s['gender'] === 'M' ? 'М' : 'Ж' ?></td>
                        <td class="actions">
                            <a href="edit.php?id=<?= $s['id'] ?>">Редактировать</a>
                            <a href="delete.php?id=<?= $s['id'] ?>" class="delete" onclick="return confirm('Удалить студента?')">Удалить</a>
                            <a href="exam_results.php?student_id=<?= $s['id'] ?>">Результаты экзаменов</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="edit.php" class="add-btn">Добавить студента</a>

</body>

</html>
<?php

define('DB_PATH', __DIR__ . '/db.sqlite');

try {
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Ошибка подключения к БД: " . $e->getMessage() . "\n";
    exit(1);
}

$groups = $db->query("
    SELECT DISTINCT g.name
    FROM Groups g
    WHERE g.graduation_year <= strftime('%Y', 'now')
    ORDER BY g.name
")->fetchAll(PDO::FETCH_COLUMN);

if (empty($groups)) {
    echo "Нет групп с годом окончания <= " . date('Y') . ".\n";
    exit(0);
}

echo "\nДоступные группы (год окончания <= " . date('Y') . "):\n";
foreach ($groups as $grp) {
    echo "  $grp\n";
}

echo "\nВведите номер группы (или Enter для всех): ";
$input = trim(fgets(STDIN));

$selectedGroup = null;
if ($input !== '' && !in_array($input, $groups)) {
    echo "Группа '$input' не найдена в списке допустимых.\n";
    exit(1);
}
if ($input !== '') {
    $selectedGroup = $input;
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

if (empty($students)) {
    echo "\nСтуденты не найдены.\n";
    exit(0);
}

$headers = ['Группа', 'Направление', 'ФИО', 'Пол', 'Дата рожд.', '№ билета'];
$rows = [];
foreach ($students as $s) {
    $rows[] = [
        $s['group_number'],
        $s['program_name'],
        $s['full_name'],
        $s['gender'] === 'M' ? 'М' : 'Ж',
        $s['birth_date'],
        $s['student_card_number']
    ];
}

$colWidths = [];
for ($i = 0; $i < count($headers); $i++) {
    $max = mb_strlen($headers[$i], 'UTF-8');
    foreach ($rows as $row) {
        $len = mb_strlen($row[$i], 'UTF-8');
        if ($len > $max) $max = $len;
    }
    $colWidths[] = max($max + 2, 8);
}

echo "\n";
printBorder($colWidths);
printRow($headers, $colWidths);
printBorder($colWidths);
foreach ($rows as $row) {
    printRow($row, $colWidths);
}
printBorder($colWidths);
echo "\n";

function printRow($cols, $widths) {
    echo "│";
    for ($i = 0; $i < count($cols); $i++) {
        $text = $cols[$i];
        $len = mb_strlen($text, 'UTF-8');
        $pad = str_repeat(' ', $widths[$i] - $len - 2);
        echo " " . $text . $pad . " │";
    }
    echo "\n";
}

function printBorder($widths) {
    echo "├";
    for ($i = 0; $i < count($widths); $i++) {
        echo str_repeat("─", $widths[$i]) . "┼";
    }
    echo "\n";
}
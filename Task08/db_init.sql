DROP TABLE IF EXISTS Grades;
DROP TABLE IF EXISTS CurriculumItems;
DROP TABLE IF EXISTS StudyPlans;
DROP TABLE IF EXISTS Students;
DROP TABLE IF EXISTS Groups;
DROP TABLE IF EXISTS Subjects;
DROP TABLE IF EXISTS Programs;

CREATE TABLE Programs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE
);

CREATE TABLE StudyPlans (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    program_id INTEGER NOT NULL,
    academic_year_start INTEGER NOT NULL CHECK (academic_year_start >= 1900),
    created_at TEXT NOT NULL DEFAULT (strftime('%Y-%m-%d', 'now')),
    UNIQUE (program_id, academic_year_start),
    FOREIGN KEY (program_id) REFERENCES Programs(id) ON DELETE CASCADE
);

CREATE TABLE Subjects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE
);

CREATE TABLE CurriculumItems (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    study_plan_id INTEGER NOT NULL,
    subject_id INTEGER NOT NULL,
    total_hours INTEGER NOT NULL CHECK (total_hours > 0 AND total_hours <= 1000),
    assessment_type TEXT NOT NULL CHECK (assessment_type IN ('exam', 'credit')),
    UNIQUE (study_plan_id, subject_id),
    FOREIGN KEY (study_plan_id) REFERENCES StudyPlans(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES Subjects(id) ON DELETE CASCADE
);

CREATE TABLE Groups (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    program_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    graduation_year INTEGER NOT NULL CHECK (graduation_year >= 1900),
    created_at TEXT NOT NULL DEFAULT (strftime('%Y-%m-%d', 'now')),
    UNIQUE (name),
    FOREIGN KEY (program_id) REFERENCES Programs(id) ON DELETE CASCADE
);

CREATE TABLE Students (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    group_id INTEGER NOT NULL,
    study_plan_id INTEGER NOT NULL,
    full_name TEXT NOT NULL CHECK (length(trim(full_name)) >= 3),
    birth_date TEXT NOT NULL CHECK (birth_date = strftime('%Y-%m-%d', birth_date)),
    gender TEXT NOT NULL CHECK (gender IN ('M', 'F')),
    student_card_number TEXT NOT NULL UNIQUE,
    created_at TEXT NOT NULL DEFAULT (strftime('%Y-%m-%d', 'now')),
    FOREIGN KEY (group_id) REFERENCES Groups(id) ON DELETE RESTRICT,
    FOREIGN KEY (study_plan_id) REFERENCES StudyPlans(id) ON DELETE RESTRICT
);

CREATE TABLE Grades (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    curriculum_item_id INTEGER NOT NULL,
    grade INTEGER NOT NULL CHECK (grade IN (2, 3, 4, 5)),
    date_recorded TEXT NOT NULL DEFAULT (strftime('%Y-%m-%d', 'now')),
    UNIQUE (student_id, curriculum_item_id),
    FOREIGN KEY (student_id) REFERENCES Students(id) ON DELETE CASCADE,
    FOREIGN KEY (curriculum_item_id) REFERENCES CurriculumItems(id) ON DELETE CASCADE
);

CREATE INDEX idx_grades_student ON Grades(student_id);
CREATE INDEX idx_grades_curriculum ON Grades(curriculum_item_id);
CREATE INDEX idx_grades_grade ON Grades(grade);
CREATE INDEX idx_students_group ON Students(group_id);
CREATE INDEX idx_students_study_plan ON Students(study_plan_id);
CREATE INDEX idx_curriculum_studyplan ON CurriculumItems(study_plan_id);
CREATE INDEX idx_curriculum_subject ON CurriculumItems(subject_id);

-- Программы подготовки
INSERT INTO Programs (name) VALUES
    ('Прикладная математика и информатика'),
    ('Фундаментальная информатика и информационные технологии'),
    ('Программная инженерия'),
    ('Компьютерная безопасность');

-- Учебные планы (2020, 2021, 2022)
INSERT INTO StudyPlans (program_id, academic_year_start) VALUES
    -- ПМиИ
    (1, 2020), (1, 2021), (1, 2022),
    -- ФИиИТ
    (2, 2020), (2, 2021), (2, 2022),
    -- ПИ
    (3, 2020), (3, 2021), (3, 2022),
    -- КБ
    (4, 2020), (4, 2021), (4, 2022);

-- Предметы
INSERT INTO Subjects (name) VALUES
    ('Математический анализ'),
    ('Линейная алгебра'),
    ('Дискретная математика'),
    ('Теория вероятностей и мат. статистика'),
    ('Основы программирования'),
    ('Алгоритмы и структуры данных'),
    ('Архитектура ЭВМ'),
    ('Операционные системы'),
    ('Базы данных'),
    ('Компьютерные сети'),
    ('Физика'),
    ('Иностранный язык'),
    ('Философия'),
    ('История России'),
    ('Экономика');


INSERT INTO CurriculumItems (study_plan_id, subject_id, total_hours, assessment_type) VALUES
    (1, 1, 144, 'exam'),
    (1, 2, 108, 'exam'),
    (1, 3, 108, 'exam'),
    (1, 5, 180, 'exam'),
    (1, 11, 72, 'credit'),
    (1, 12, 72, 'credit');

-- План 2 (ПМиИ, 2021)
INSERT INTO CurriculumItems (study_plan_id, subject_id, total_hours, assessment_type) VALUES
    (2, 1, 144, 'exam'),
    (2, 2, 108, 'exam'),
    (2, 3, 108, 'exam'),
    (2, 5, 200, 'exam'),
    (2, 11, 72, 'credit'),
    (2, 12, 72, 'credit');

-- План 3 (ПМиИ, 2022)
INSERT INTO CurriculumItems (study_plan_id, subject_id, total_hours, assessment_type) VALUES
    (3, 1, 120, 'exam'),
    (3, 2, 96, 'exam'),
    (3, 3, 96, 'exam'),
    (3, 5, 160, 'exam'),
    (3, 11, 60, 'credit'),
    (3, 12, 60, 'credit');

-- План 4 (ФИиИТ, 2020)
INSERT INTO CurriculumItems (study_plan_id, subject_id, total_hours, assessment_type) VALUES
    (4, 3, 120, 'exam'),
    (4, 5, 180, 'exam'),
    (4, 6, 144, 'exam'),
    (4, 7, 108, 'exam'),
    (4, 11, 72, 'credit'),
    (4, 12, 72, 'credit');

-- План 5 (ФИиИТ, 2021)
INSERT INTO CurriculumItems (study_plan_id, subject_id, total_hours, assessment_type) VALUES
    (5, 3, 120, 'exam'),
    (5, 5, 200, 'exam'),
    (5, 6, 160, 'exam'),
    (5, 7, 120, 'exam'),
    (5, 11, 72, 'credit'),
    (5, 12, 72, 'credit');

-- План 6 (ФИиИТ, 2022)
INSERT INTO CurriculumItems (study_plan_id, subject_id, total_hours, assessment_type) VALUES
    (6, 3, 100, 'exam'),
    (6, 5, 160, 'exam'),
    (6, 6, 120, 'exam'),
    (6, 7, 96, 'exam'),
    (6, 11, 60, 'credit'),
    (6, 12, 60, 'credit');

-- План 7 (ПИ, 2020)
INSERT INTO CurriculumItems (study_plan_id, subject_id, total_hours, assessment_type) VALUES
    (7, 5, 180, 'exam'),
    (7, 6, 144, 'exam'),
    (7, 8, 108, 'exam'),
    (7, 9, 108, 'exam'),
    (7, 12, 72, 'credit'),
    (7, 13, 72, 'credit');

-- План 8 (ПИ, 2021)
INSERT INTO CurriculumItems (study_plan_id, subject_id, total_hours, assessment_type) VALUES
    (8, 5, 200, 'exam'),
    (8, 6, 160, 'exam'),
    (8, 8, 120, 'exam'),
    (8, 9, 120, 'exam'),
    (8, 12, 72, 'credit'),
    (8, 13, 72, 'credit');

-- План 9 (ПИ, 2022)
INSERT INTO CurriculumItems (study_plan_id, subject_id, total_hours, assessment_type) VALUES
    (9, 5, 160, 'exam'),
    (9, 6, 120, 'exam'),
    (9, 8, 96, 'exam'),
    (9, 9, 96, 'exam'),
    (9, 12, 60, 'credit'),
    (9, 13, 60, 'credit');

-- План 10 (КБ, 2020)
INSERT INTO CurriculumItems (study_plan_id, subject_id, total_hours, assessment_type) VALUES
    (10, 5, 180, 'exam'),
    (10, 9, 108, 'exam'),
    (10, 10, 108, 'exam'),
    (10, 14, 72, 'credit'),
    (10, 15, 72, 'credit');

-- План 11 (КБ, 2021)
INSERT INTO CurriculumItems (study_plan_id, subject_id, total_hours, assessment_type) VALUES
    (11, 5, 200, 'exam'),
    (11, 9, 120, 'exam'),
    (11, 10, 120, 'exam'),
    (11, 14, 72, 'credit'),
    (11, 15, 72, 'credit');

-- План 12 (КБ, 2022)
INSERT INTO CurriculumItems (study_plan_id, subject_id, total_hours, assessment_type) VALUES
    (12, 5, 160, 'exam'),
    (12, 9, 96, 'exam'),
    (12, 10, 96, 'exam'),
    (12, 14, 60, 'credit'),
    (12, 15, 60, 'credit');

-- Группы (1–4 курсы для разных программ)
INSERT INTO Groups (program_id, name, graduation_year) VALUES
    -- ПМиИ
    (1, '103', 2023),
    (1, '203', 2024),
    (1, '303', 2025),
    (1, '403', 2026),
    -- ФИиИТ
    (2, '104', 2023),
    (2, '204', 2024),
    (2, '304', 2025),
    (2, '404', 2026),
    -- ПИ
    (3, '105', 2023),
    (3, '205', 2024),
    (3, '305', 2025),
    (3, '405', 2026);

-- Студенты
INSERT INTO Students (group_id, study_plan_id, full_name, birth_date, gender, student_card_number) VALUES
    -- Группа 103 (ПМиИ, 1 курс)
    (1, 1, 'Козлов Дмитрий Сергеевич', '2004-03-12', 'M', '103-КДС-001'),
    (1, 1, 'Волкова Екатерина Андреевна', '2004-07-21', 'F', '103-ВЕА-002'),
    -- Группа 203 (ПМиИ, 2 курс)
    (2, 2, 'Морозов Алексей Иванович', '2003-11-05', 'M', '203-МАИ-001'),
    (2, 2, 'Новикова Ольга Павловна', '2003-01-30', 'F', '203-НОП-002'),
    -- Группа 303 (ПМиИ, 3 курс)
    (3, 3, 'Иванов Иван Иванович', '2002-05-15', 'M', '303-ИИИ-001'),
    (3, 3, 'Петрова Анна Сергеевна', '2001-11-23', 'F', '303-ПАС-002'),
    -- Группа 403 (ПМиИ, 4 курс)
    (4, 3, 'Смирнов Николай Викторович', '2000-09-08', 'M', '403-СНВ-001'),
    -- Группа 104 (ФИиИТ, 1 курс)
    (5, 4, 'Лебедев Артём Дмитриевич', '2004-04-17', 'M', '104-ЛАД-001'),
    (5, 4, 'Кузнецова Мария Юрьевна', '2004-08-03', 'F', '104-КМЮ-002'),
    -- Группа 204 (ФИиИТ, 2 курс)
    (6, 5, 'Попов Александр Олегович', '2003-12-11', 'M', '204-ПАО-001'),
    -- Группа 304 (ФИиИТ, 3 курс)
    (7, 6, 'Сидоров Алексей Владимирович', '2001-08-30', 'M', '304-САВ-001'),
    (7, 6, 'Федорова Елена Николаевна', '2002-02-14', 'F', '304-ФЕН-002'),
    -- Группа 404 (ФИиИТ, 4 курс)
    (8, 6, 'Васильев Игорь Петрович', '2000-10-25', 'M', '404-ВИП-001'),
    -- Группа 105 (ПИ, 1 курс)
    (9, 7, 'Соколов Максим Валерьевич', '2004-05-19', 'M', '105-СМВ-001'),
    (9, 7, 'Ильина Дарья Романовна', '2004-09-27', 'F', '105-ИДР-002'),
    -- Группа 205 (ПИ, 2 курс)
    (10, 8, 'Гусев Роман Алексеевич', '2003-06-14', 'M', '205-ГРА-001'),
    -- Группа 305 (ПИ, 3 курс)
    (11, 9, 'Тихонова Анастасия Сергеевна', '2002-01-07', 'F', '305-ТАС-001'),
    -- Группа 405 (ПИ, 4 курс)
    (12, 9, 'Макаров Пётр Андреевич', '2000-11-30', 'M', '405-МПА-001');

INSERT INTO Grades (student_id, curriculum_item_id, grade, date_recorded) VALUES
    (1, 1, 5, '2021-01-15'),
    (1, 2, 4, '2021-01-20'),
    (1, 3, 5, '2021-01-25'),
    (1, 4, 4, '2021-02-10'),
    (1, 5, 3, '2021-02-05'),
    (1, 6, 5, '2021-02-12'),
    (3, 7, 4, '2022-01-18'),
    (3, 9, 5, '2022-01-22'),
    (3, 10, 4, '2022-02-01'),
    (3, 11, 3, '2022-02-03'),
    (3, 12, 5, '2022-02-05'),
    (5, 13, 5, '2023-01-10'),
    (5, 14, 5, '2023-01-15'),
    (5, 15, 5, '2023-01-20'),
    (5, 16, 5, '2023-02-01'),
    (5, 17, 5, '2023-02-03'),
    (5, 18, 5, '2023-02-05'),
    (6, 14, 3, '2023-01-12'),
    (6, 15, 2, '2023-01-18'),
    (6, 16, 4, '2023-02-02'),
    (6, 17, 4, '2023-02-04'),
    (6, 18, 5, '2023-02-06'),
    (7, 13, 4, '2020-12-10'),
    (7, 14, 5, '2020-12-15'),
    (7, 15, 4, '2020-12-20'),
    (7, 16, 5, '2020-12-22'),
    (7, 17, 5, '2020-12-25'),
    (7, 18, 4, '2020-12-28'),
    (9, 19, 5, '2021-01-14'),
    (9, 20, 4, '2021-01-19'),
    (9, 21, 5, '2021-01-24'),
    (9, 22, 4, '2021-02-04'),
    (9, 23, 5, '2021-02-06'),
    (9, 24, 3, '2021-02-08'),
    (11, 25, 4, '2022-01-17'),
    (11, 26, 5, '2022-01-21'),
    (11, 28, 4, '2022-02-02'),
    (11, 29, 5, '2022-02-04'),
    (11, 30, 4, '2022-02-06'),
    (12, 31, 5, '2023-01-11'),
    (12, 32, 4, '2023-01-16'),
    (12, 33, 5, '2023-01-21'),
    (12, 34, 3, '2023-02-02'),
    (12, 35, 4, '2023-02-04'),
    (12, 36, 5, '2023-02-06'),
    (14, 31, 5, '2021-12-12'),
    (14, 32, 5, '2021-12-14'),
    (14, 33, 4, '2021-12-16'),
    (14, 34, 5, '2021-12-18'),
    (14, 35, 4, '2021-12-20'),
    (14, 36, 5, '2021-12-22'),
    (15, 37, 4, '2021-01-13'),
    (15, 38, 5, '2021-01-18'),
    (15, 39, 4, '2021-01-23'),
    (15, 40, 5, '2021-02-03'),
    (15, 41, 4, '2021-02-05'),
    (15, 42, 3, '2021-02-07'),
    (17, 43, 5, '2022-01-16'),
    (17, 44, 4, '2022-01-20'),
    (17, 46, 3, '2022-02-03'),
    (17, 47, 4, '2022-02-05'),
    (17, 48, 5, '2022-02-07'),
    (18, 49, 5, '2023-01-12'),
    (18, 50, 4, '2023-01-17'),
    (18, 51, 5, '2023-01-22'),
    (18, 52, 4, '2023-02-03'),
    (18, 53, 5, '2023-02-05'),
    (18, 54, 4, '2023-02-07'),
    (19, 49, 4, '2020-12-11'),
    (19, 50, 5, '2020-12-13'),
    (19, 51, 4, '2020-12-15'),
    (19, 52, 5, '2020-12-17'),
    (19, 53, 4, '2020-12-19'),
    (19, 54, 5, '2020-12-21');
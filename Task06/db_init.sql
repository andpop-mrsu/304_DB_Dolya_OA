PRAGMA foreign_keys = ON;

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
    name TEXT NOT NULL UNIQUE,
    created_at TEXT NOT NULL DEFAULT (strftime('%Y-%m-%d', 'now')),
    FOREIGN KEY (program_id) REFERENCES Programs(id) ON DELETE CASCADE
);

CREATE TABLE Students (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    group_id INTEGER NOT NULL,
    study_plan_id INTEGER NOT NULL,
    full_name TEXT NOT NULL CHECK (length(trim(full_name)) >= 3),
    birth_date TEXT NOT NULL CHECK (birth_date = strftime('%Y-%m-%d', birth_date)),
    gender TEXT NOT NULL CHECK (gender IN ('M', 'F')),
    enrollment_year INTEGER NOT NULL CHECK (enrollment_year >= 1900),
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

INSERT INTO Programs (name) VALUES
    ('Прикладная математика и информатика'),
    ('Фундаментальная информатика и информационные технологии');

INSERT INTO StudyPlans (program_id, academic_year_start) VALUES
    (1, 2020),
    (1, 2021),
    (2, 2020);

INSERT INTO Subjects (name) VALUES
    ('Математический анализ'),
    ('Дискртеная математика'),
    ('Основы программирования'),
    ('Физика'),
    ('Иностранный язык');

INSERT INTO CurriculumItems (study_plan_id, subject_id, total_hours, assessment_type) VALUES
    (1, 1, 144, 'exam'),
    (1, 2, 108, 'exam'),
    (1, 3, 180, 'exam'),
    (1, 4, 72,  'credit'),
    (1, 5, 72,  'credit');

INSERT INTO CurriculumItems (study_plan_id, subject_id, total_hours, assessment_type) VALUES
    (2, 1, 144, 'exam'),
    (2, 2, 108, 'exam'),
    (2, 3, 200, 'exam'),
    (2, 4, 72,  'credit'),
    (2, 5, 72,  'credit');

INSERT INTO Groups (program_id, name) VALUES
    (1, '303'),
    (1, '403'),
    (2, '304');

INSERT INTO Students (group_id, study_plan_id, full_name, birth_date, gender, enrollment_year) VALUES
    (1, 1, 'Иванов Иван Иванович', '2002-05-15', 'M', 2020),
    (1, 1, 'Петрова Анна Сергеевна', '2001-11-23', 'F', 2020),
    (3, 3, 'Сидоров Алексей Владимирович', '2001-08-30', 'M', 2020);

INSERT INTO CurriculumItems (study_plan_id, subject_id, total_hours, assessment_type) VALUES
    (3, 1, 120, 'exam'),
    (3, 3, 180, 'exam'),
    (3, 5, 60,  'credit');

INSERT INTO Grades (student_id, curriculum_item_id, grade) VALUES
    (1, 1, 5),
    (1, 2, 4),
    (1, 3, 5),
    (2, 1, 3),
    (2, 2, 2),
    (2, 3, 4),
    (3, 11, 5),
    (3, 12, 5),
    (3, 13, 4);
#!/bin/bash
chcp 65001
sqlite3 movies_rating.db < db_init.sql

echo "1. Найти все пары пользователей, оценивших один и тот же фильм. Устранить дубликаты, проверить отсутствие пар с самим собой. Для каждой пары должны быть указаны имена пользователей и название фильма, который они ценили. В списке оставить первые 100 записей."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT DISTINCT u1.name AS 'Пользователь 1', u2.name AS 'Пользователь 2', m.title AS 'Название Фильма' FROM ratings r1 JOIN ratings r2 ON r1.movie_id = r2.movie_id AND r1.user_id < r2.user_id JOIN users u1 ON r1.user_id = u1.id JOIN users u2 ON r2.user_id = u2.id JOIN movies m ON r1.movie_id = m.id LIMIT 100"
echo " "

echo "2. Найти 10 самых свежих оценок от разных пользователей, вывести названия фильмов, имена пользователей, оценку, дату отзыва в формате ГГГГ-ММ-ДД."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT m.title AS 'Название фильма', u.name AS 'Имя', r.rating AS 'Оценка', date(r.timestamp, 'unixepoch') AS 'Дата Отзыва' FROM ratings r JOIN users u ON r.user_id = u.id JOIN movies m ON r.movie_id = m.id WHERE r.timestamp IN (SELECT MAX(r2.timestamp) FROM ratings r2 GROUP BY r2.user_id) ORDER BY r.timestamp DESC LIMIT 10"
echo " "

echo "3. Вывести в одном списке все фильмы с максимальным средним рейтингом и все фильмы с минимальным средним рейтингом. Общий список отсортировать по году выпуска и названию фильма. В зависимости от рейтинга в колонке 'Рекомендуем' для фильмов должно быть написано 'Да' или 'Нет'."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "WITH avg_ratings AS (SELECT movie_id, AVG(rating) as avg_rating FROM ratings GROUP BY movie_id), max_min AS (SELECT MAX(avg_rating) as max_r, MIN(avg_rating) as min_r FROM avg_ratings) SELECT m.title AS 'Название', m.year AS 'Год', ar.avg_rating AS 'Средний рейтинг', CASE WHEN ar.avg_rating = (SELECT max_r FROM max_min) THEN 'Да' ELSE 'Нет' END AS Рекомендуем FROM movies m JOIN avg_ratings ar ON m.id = ar.movie_id WHERE ar.avg_rating = (SELECT max_r FROM max_min) OR ar.avg_rating = (SELECT min_r FROM max_min) ORDER BY m.year, m.title"
echo " "

echo "4. Вычислить количество оценок и среднюю оценку, которую дали фильмам пользователи-женщины в период с 2010 по 2012 год."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT COUNT(*) AS 'Количество оценок', AVG(r.rating) AS 'Средняя оценка' FROM ratings r JOIN users u ON r.user_id = u.id WHERE u.gender = 'female' AND strftime('%Y', datetime(r.timestamp, 'unixepoch')) BETWEEN '2010' AND '2012'"
echo " "

echo "5. Составить список фильмов с указанием их средней оценки и места в рейтинге по средней оценке. Отсортировать по году выпуска и названиям фильмов. Показать первые 20 записей."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT title AS 'Название фильма', year AS 'Год выпуска', AVG(r.rating) AS 'Средняя оценка', RANK() OVER (ORDER BY AVG(r.rating) DESC) AS 'Место в рейтинге' FROM movies m JOIN ratings r ON m.id = r.movie_id GROUP BY m.id ORDER BY year ASC, title ASC LIMIT 20"
echo " "

echo "6. Вывести список из 10 последних зарегистрированных пользователей в формате 'Фамилия Имя|Дата регистрации'."
echo --------------------------------------------------
sqlite3 movies_rating.db -noheader -batch -echo "SELECT name || '|' || register_date AS 'Фамилия Имя|Дата регистрации' FROM users ORDER BY register_date DESC LIMIT 10"
echo " "

echo "7. Вывести таблицу умножения чисел от 1 до 10 с помощью рекурсивного CTE как в примере 1x1=1 1x2=2..."
echo --------------------------------------------------
sqlite3 movies_rating.db -noheader -batch -echo "WITH RECURSIVE x(i, j) AS (SELECT 1, 1 UNION ALL SELECT i, j+1 FROM x WHERE j<10 UNION ALL SELECT i+1, 1 FROM x WHERE j=10 AND i<10) SELECT i || 'x' || j || '=' || (i*j) FROM x ORDER BY i, j"
echo " "

echo "8. С помощью рекурсивного CTE выделить все жанры фильмов, имеющиеся в таблице movies (каждый жанр в отдельной строке)"
echo --------------------------------------------------
sqlite3 movies_rating.db -noheader -batch -echo "WITH RECURSIVE split(id, genre, rest) AS (SELECT id, '', genres || '|' FROM movies UNION ALL SELECT id, substr(rest, 0, instr(rest, '|')), substr(rest, instr(rest, '|')+1) FROM split WHERE rest!='') SELECT DISTINCT genre FROM split WHERE genre!='' ORDER BY genre"
echo " "
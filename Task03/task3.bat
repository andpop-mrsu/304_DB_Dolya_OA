#!/bin/bash
chcp 65001

sqlite3 movies_rating.db < db_init.sql

echo "1. Составить список фильмов, имеющих хотя бы одну оценку. Список фильмов отсортировать по году выпуска и по названиям. В списке оставить первые 10 фильмов."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "select distinct m.title, m.year from movies m, ratings r where m.id = r.movie_id and r.rating > 0  order by m.year, m.title ASC limit 10;"
echo " "

echo "2.  Вывести список всех пользователей, фамилии (не имена!) которых начинаются на букву 'A'. Полученный список отсортировать по дате регистрации. В списке оставить первых 5 пользователей."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT name, register_date FROM users WHERE substr(name, instr(name, ' ') + 1, 1) = 'A' ORDER BY register_date LIMIT 5;"
echo " "

echo "3. Написать запрос, возвращающий информацию о рейтингах в более читаемом формате: имя и фамилия эксперта, название фильма, год выпуска, оценка и дата оценки в формате ГГГГ-ММ-ДД. Отсортировать данные по имени эксперта, затем названию фильма и оценке. В списке оставить первые 50 записей."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT
    u.name AS expert_name,
    m.title AS movie_title,
    m.year AS release_year,
    r.rating AS score,
    DATE(datetime(timestamp, 'unixepoch')) AS rating_date
FROM ratings r
JOIN users u ON r.user_id = u.id
JOIN movies m ON r.movie_id = m.id
ORDER BY u.name, m.title, r.rating
LIMIT 50;"
echo " "

echo "4. Вывести список фильмов с указанием тегов, которые были им присвоены пользователями. Сортировать по году выпуска, затем по названию фильма, затем по тегу. В списке оставить первые 40 записей."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "select m.title, t.tag from movies m, tags t where m.id=t.movie_id order by m.year, m.title, t.tag limit 40;"
echo " "

echo "5. Вывести список самых свежих фильмов. В список должны войти все фильмы последнего года выпуска, имеющиеся в базе данных. Запрос должен быть универсальным, не зависящим от исходных данных (нужный год выпуска должен определяться в запросе, а не жестко задаваться)."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "select title, year from movies where year=(select max(year) from movies); "
echo " "

echo "6. Найти все комедии, выпущенные после 2000 года, которые понравились мужчинам (оценка не ниже 4.5). Для каждого фильма в этом списке вывести название, год выпуска и количество таких оценок. Результат отсортировать по году выпуска и названию фильма."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT
    m.title,
    m.year,
    COUNT(r.rating) AS male_high_ratings
FROM movies m
JOIN ratings r ON m.id = r.movie_id
JOIN users u ON r.user_id = u.id
WHERE m.year > 2000
  AND m.genres LIKE '%Comedy%'
  AND r.rating >= 4.5
  AND u.gender = 'male'
GROUP BY m.id, m.title, m.year
ORDER BY m.year, m.title;"
echo " "

echo "7. Провести анализ занятий (профессий) пользователей - вывести количество пользователей для каждого рода занятий. Найти самую распространенную и самую редкую профессию посетитетей сайта."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT occupation, COUNT(*) AS user_count FROM users GROUP BY occupation ORDER BY user_count DESC;"
sqlite3 movies_rating.db -box -echo "SELECT occupation, COUNT(*) AS user_count FROM users GROUP BY occupation ORDER BY user_count DESC LIMIT 1;"
sqlite3 movies_rating.db -box -echo "SELECT occupation, COUNT(*) AS user_count FROM users GROUP BY occupation ORDER BY user_count ASC LIMIT 1;"
echo " "
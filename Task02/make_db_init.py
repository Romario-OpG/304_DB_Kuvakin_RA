
#!/usr/bin/env python3
import os
import re
import csv

def parse_title(title):
    match = re.search(r'\((\d{4})\)$', title)
    if match:
        year = int(match.group(1))
        clean_title = title[:match.start()].strip()
    else:
        year = None
        clean_title = title.strip()
    return clean_title, year

def escape_sql(s):
    return s.replace("'", "''")

def main():

    with open('db_init.sql', 'w', encoding='utf-8') as f:
        f.write("DROP TABLE IF EXISTS ratings;\n")
        f.write("DROP TABLE IF EXISTS tags;\n")
        f.write("DROP TABLE IF EXISTS movies;\n")
        f.write("DROP TABLE IF EXISTS users;\n")

        # Create tables

        f.write("""CREATE TABLE users (
    id INTEGER PRIMARY KEY,
    name TEXT,
    email TEXT,
    gender TEXT,
    register_date TEXT,
    occupation TEXT
);\n\n""")

        f.write("""CREATE TABLE movies (
    id INTEGER PRIMARY KEY,
    title TEXT,
    year INTEGER,
    genres TEXT
);\n\n""")

        f.write("""CREATE TABLE ratings (
    id INTEGER PRIMARY KEY,
    user_id INTEGER,
    movie_id INTEGER,
    rating REAL,
    timestamp INTEGER
);\n\n""")

        f.write("""CREATE TABLE tags (
    id INTEGER PRIMARY KEY,
    user_id INTEGER,
    movie_id INTEGER,
    tag TEXT,
    timestamp INTEGER
);\n\n""")


        user_file = 'users.txt'
        with open(user_file, 'r', encoding='utf-8') as users_file:
            for line in users_file:
                parts = line.strip().split('|')
                if len(parts) != 6:
                    continue
                uid, name, email, gender, reg_date, occ = parts
                f.write(f"INSERT INTO users (id, name, email, gender, register_date, occupation) "
                        f"VALUES ({uid}, '{escape_sql(name)}', '{email}', '{gender}', '{reg_date}', '{escape_sql(occ)}');\n")
        f.write('\n')

        with open('movies.csv', 'r', encoding='utf-8') as movies_file:
            reader = csv.reader(movies_file)
            header = next(reader)  # skip header
            for row in reader:
                if not row:
                    continue
                full_row = ','.join(row)
                parts = full_row.split(',', 2)
                if len(parts) < 3:
                    continue
                movie_id, title, genres_str = parts
                title_clean, year = parse_title(title)
                genres_str = genres_str if genres_str != "(no genres listed)" else ""
                f.write(f"INSERT INTO movies (id, title, year, genres) "
                        f"VALUES ({movie_id}, '{escape_sql(title_clean)}', {year if year else 'NULL'}, '{escape_sql(genres_str)}');\n")
        f.write('\n')

        rating_id = 1
        if os.path.exists('ratings.csv'):
            with open('ratings.csv', 'r', encoding='utf-8') as ratings_file:
                reader = csv.reader(ratings_file)
                header = next(reader, None)
                for row in reader:
                    if len(row) < 4:
                        continue
                    user_id, movie_id, rating, timestamp = row[:4]
                    f.write(f"INSERT INTO ratings (id, user_id, movie_id, rating, timestamp) "
                            f"VALUES ({rating_id}, {user_id}, {movie_id}, {rating}, {timestamp});\n")
                    rating_id += 1
        f.write('\n')

        tag_id = 1
        if os.path.exists('tags.csv'):
            with open('tags.csv', 'r', encoding='utf-8') as tags_file:
                reader = csv.reader(tags_file)
                for row in reader:
                    if len(row) < 4:
                        continue
                    if row[0].startswith('!'):
                        continue
                    try:
                        user_id = int(row[0])
                        movie_id = int(row[1])
                        tag = row[2]
                        timestamp = int(row[3])
                    except (ValueError, IndexError):
                        continue
                    f.write(f"INSERT INTO tags (id, user_id, movie_id, tag, timestamp) "
                            f"VALUES ({tag_id}, {user_id}, {movie_id}, '{escape_sql(tag)}', {timestamp});\n")
                    tag_id += 1

if __name__ == '__main__':
    main()
    
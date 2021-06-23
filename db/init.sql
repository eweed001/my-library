--- BOOKS ---
CREATE TABLE books (
  id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
  title TEXT NOT NULL,
  author_first_name TEXT NOT NULL,
  author_last_name TEXT NOT NULL,
  publication_year INT NOT NULL,
  average_rating REAL NOT NULL,
  isbn INTEGER NOT NULL,
  available INTEGER NOT NULL,
  description TEXT
);

--- GENRES ---
CREATE TABLE genres
(
  id INTEGER NOT NULL PRIMARY KEY  AUTOINCREMENT UNIQUE,
  genre TEXT NOT NULL
);

--- BOOKS AND GENRES ---
CREATE TABLE books_and_genres
(
  id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
  book_id INTEGER NOT NULL,
  genre_id INTEGER NOT NULL,
  FOREIGN KEY
(book_id) REFERENCES books
(id),
  FOREIGN KEY
(genre_id) REFERENCES genres
(id)
);

--- Book covers ---
CREATE TABLE book_covers (
  id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
  book_id INTEGER NOT NULL,
  citation TEXT NOT NULL,
  extension TEXT NOT NULL,
  FOREIGN KEY
(book_id) REFERENCES books
(id)
);


--- Users ---

CREATE TABLE users (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	name TEXT NOT NULL,
	username TEXT NOT NULL UNIQUE,
	password TEXT NOT NULL
);

INSERT INTO users
  (id, name, username, password)
VALUES
  (1, 'Todd', 'todd', '$2y$10$QtCybkpkzh7x5VN11APHned4J8fu78.eFXlyAMmahuAaNcbwZ7FH.');
-- password: monkey
INSERT INTO users
  (id, name, username, password)
VALUES
  (2, 'Rachel', 'rachel', '$2y$10$QtCybkpkzh7x5VN11APHned4J8fu78.eFXlyAMmahuAaNcbwZ7FH.');
-- password: monkey

--- Sessions ---

CREATE TABLE sessions (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	user_id INTEGER NOT NULL,
	session TEXT NOT NULL UNIQUE,
  last_login   TEXT NOT NULL,

  FOREIGN KEY
(user_id) REFERENCES users
(id)
);

--- Groups ----

CREATE TABLE groups (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	name TEXT NOT NULL UNIQUE
);

INSERT INTO groups
  (id, name)
VALUES
  (1, 'Librarian');


--- Group Membership

CREATE TABLE memberships (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
  group_id INTEGER NOT NULL,
  user_id INTEGER NOT NULL,

  FOREIGN KEY
(group_id) REFERENCES groups
(id),
  FOREIGN KEY
(user_id) REFERENCES users
(id)
);

INSERT INTO memberships
  (group_id, user_id)
VALUES
  (1, 1);
-- User 'Todd' is a librarian.

-- inserting original set of genres to genres table --

INSERT INTO genres
  (id, genre)
VALUES
  (1, "Fiction");

INSERT INTO genres
  (id, genre)
VALUES
  (2, "Fantasy");


INSERT INTO genres
  (id, genre)
VALUES
  (3, "Horror");

INSERT INTO genres
  (id, genre)
VALUES
  (4, "Mystery");


INSERT INTO genres
  (id, genre)
VALUES
  (5, "Non-fiction");


INSERT INTO genres
  (id, genre)
VALUES
  (6, "Romance");

INSERT INTO genres
  (id, genre)
VALUES
  (7, "Sci-Fi");

INSERT INTO genres
  (id, genre)
VALUES
  (8, "Thriller");

INSERT INTO genres
  (id, genre)
VALUES
  (9, "Other");

-- Inserting books seed data --

INSERT INTO books
  (id, title, author_first_name, author_last_name, publication_year,
  average_rating, isbn, available)
VALUES
  (1, "A Thousand Splendid Suns", "Khaled", "Hosseini", 2007, 4.6, 9781594489501, 1);

INSERT INTO books_and_genres
  (id, book_id, genre_id)
VALUES
  (1, 1, 1);


INSERT INTO books
  (id, title, author_first_name, author_last_name, publication_year,
  average_rating, isbn, available, description)
VALUES
  (2, "The Fellowship of The Ring", "J.R.R", "Tolkein", 1954, 4.6, 345235096125, 1, 'The first of three books in Tolkeins epic novel The Lord of The Rings. It follows the journey of Frodo Baggins, the ring bearer, his loyal compannion Samwise Gamgee and the 7 other members of the fellowship on their journey to destroy the One Ring.');

INSERT INTO books_and_genres
  (id, book_id, genre_id)
VALUES
  (2, 2, 2);



INSERT INTO books
  (id, title, author_first_name, author_last_name, publication_year,
  average_rating, isbn, available)
VALUES
  (3, "The Green Mile", "Stephen", "King", 1996, 4.6, 452278902, 1);


INSERT INTO books_and_genres
  (id, book_id, genre_id)
VALUES
  (3, 3, 3);



INSERT INTO books
  (id, title, author_first_name, author_last_name, publication_year,
  average_rating, isbn, available)
VALUES
  (4, "Mystic River", "Dennis", "Lehane", 2001, 4.3, 380731851, 1);


INSERT INTO books_and_genres
  (id, book_id, genre_id)
VALUES
  (4, 4, 4);





INSERT INTO books
  (id, title, author_first_name, author_last_name, publication_year,
  average_rating, isbn, available)
VALUES
  (5, "Weapons of Math Destruction", "Cathy", "O'Neil", 2016, 5.0, 9780553418835, 1);


INSERT INTO books_and_genres
  (id, book_id, genre_id)
VALUES
  (5, 5, 5);



INSERT INTO books
  (id, title, author_first_name, author_last_name, publication_year,
  average_rating, isbn, available)
VALUES
  (6, "Eleanor Oliphant is Completely Fine", "Gail", "Honeyman", 2017, 4.5, 9780735220690, 1);

INSERT INTO books_and_genres
  (id, book_id, genre_id)
VALUES
  (6, 6, 6);

INSERT INTO books_and_genres
  (id, book_id, genre_id)
VALUES
  (11, 6, 1);



INSERT INTO books
  (id, title, author_first_name, author_last_name, publication_year,
  average_rating, isbn, available)
VALUES
  (7, "11/22/63", "Stephen", "King", 2011, 4.2, 9781501120602, 0);

INSERT INTO books_and_genres
  (id, book_id, genre_id)
VALUES
  (7, 7, 7);


INSERT INTO books
  (id, title, author_first_name, author_last_name, publication_year,
  average_rating, isbn, available)
VALUES
  (8, "The Woman in the Window", "A.J.", "Finn", 2018, 4.4, 9780062905086, 0);

INSERT INTO books_and_genres
  (id, book_id, genre_id)
VALUES
  (8, 8, 8);


INSERT INTO books
  (id, title, author_first_name, author_last_name, publication_year,
  average_rating, isbn, available)
VALUES
  (9, "The Girl With the Dragon Tattoo", "Stieg", "Larsson", 2005, 4.1, 307269752, 0);

INSERT INTO books_and_genres
  (id, book_id, genre_id)
VALUES
  (9, 9, 8);



INSERT INTO books
  (id, title, author_first_name, author_last_name, publication_year,
  average_rating, isbn, available, description)
VALUES
  (10, "Firestarter", "Stephen", "King", 1980, 4.1, 9781501192319, 0, 'First published in 1980 and later adapted into a movie in 1984 (with talks of a remake in the works), Firestarter is Kings eight novel. The story introduces Charlie McGee who has inherited pyrokinetic powers from her parents due to experimentation they endured while in college. The government percieves Charlie as a threat and the novel follows the attempts at controlling and capturing the young girl.' );


INSERT INTO books_and_genres
  (id, book_id, genre_id)
VALUES
  (10, 10, 3);


INSERT INTO books_and_genres
  (id, book_id, genre_id)
VALUES
  (12, 4, 8);


INSERT INTO books
  (id, title, author_first_name, author_last_name, publication_year,
  average_rating, isbn, available)
VALUES
  (11, "Once Upon a River", "Diane", "Setterfield", 2018, 3.96, 9780743298070, 1);


INSERT INTO books_and_genres
  (id, book_id, genre_id)
VALUES
  (13, 11, 2);


INSERT INTO books_and_genres
  (id, book_id, genre_id)
VALUES
  (14, 11, 1);


INSERT INTO books
  (id, title, author_first_name, author_last_name, publication_year,
  average_rating, isbn, available)
VALUES
  (12, "It", "Stephen", "King", 1986, 4.24, 9781982127794, 1);


INSERT INTO books_and_genres
  (id, book_id, genre_id)
VALUES
  (15, 12, 3);



--- cover seed data ---
INSERT INTO book_covers
  (id, book_id, citation, extension)
VALUES
  (1, 1, 'https://www.goodreads.com/book/show/128029.A_Thousand_Splendid_Suns', 'jpg');

INSERT INTO book_covers
  (id, book_id, citation, extension)
VALUES
  (2, 2, 'https://www.goodreads.com/book/show/838626.The_Fellowship_of_the_Ring', 'jpg');

INSERT INTO book_covers
  (id, book_id, citation, extension)
VALUES
  (3, 3, 'https://www.goodreads.com/book/show/978711.The_Green_Mile', 'jpg');

INSERT INTO book_covers
  (id, book_id, citation, extension)
VALUES
  (4, 4, 'https://www.goodreads.com/book/show/425113.Mystic_River', 'jpg');

INSERT INTO book_covers
  (id, book_id, citation, extension)
VALUES
  (5, 5, 'https://www.goodreads.com/book/show/28186015-weapons-of-math-destruction?from_search=true&from_srp=true&qid=bMgJQxgGI6&rank=1', 'jpg');

INSERT INTO book_covers
  (id, book_id, citation, extension)
VALUES
  (6, 6, 'https://www.goodreads.com/book/show/39961982-eleanor-oliphant-is-completely-fine', 'jpg');

INSERT INTO book_covers
  (id, book_id, citation, extension)
VALUES
  (7, 7, 'https://www.goodreads.com/book/show/10644930-11-22-63?from_search=true&from_srp=true&qid=zEWj553L2e&rank=1', 'jpg');

INSERT INTO book_covers
  (id, book_id, citation, extension)
VALUES
  (8, 8, 'https://www.goodreads.com/book/show/42980952-the-woman-in-the-window', 'jpg');


INSERT INTO book_covers
  (id, book_id, citation, extension)
VALUES
  (9, 9, 'https://www.goodreads.com/book/show/5291539-the-girl-with-the-dragon-tattoo', 'jpg');


INSERT INTO book_covers
  (id, book_id, citation, extension)
VALUES
  (10, 10, 'https://www.goodreads.com/book/show/29430667-firestarter', 'jpg');


INSERT INTO book_covers
  (id, book_id, citation, extension)
VALUES
  (11, 11, 'https://www.goodreads.com/book/show/40130093-once-upon-a-river?from_search=true&from_srp=true&qid=SB0OyplKhX&rank=1', 'jpg');

INSERT INTO book_covers
  (id, book_id, citation, extension)
VALUES
  (12, 12, 'https://www.goodreads.com/book/show/43319881-it', 'jpg');

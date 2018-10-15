
-- this is an sqlite version 2 formatted schema
-- NOTE: add column may NOT be used on this db
--      if it is (eg. using sqlite3) the db becomes unreadable for sqlite < 3.1.3

create table 'user' (
    'id' integer primary key not null,
    'email' text not null unique check(length(email) < 321),
    'password_digest' text not null,
    'created' text not null,
    'role_id' integer not null default 1);

create table 'news' (
    'id' integer primary key not null,
    'date' text not null,
    'news_status_id' integer not null default 1);

create table 'news_l10n' (
    'id' integer primary key not null,
    'news_id' integer not null,
    'locale' text not null,
    'title' text not null check(length('title') < 200),
    'content' text not null check(length('content') < 100000),
    'image' text);

create table 'role' (
    'id' integer primary key not null,
    'name' text not null unique);
insert into 'role' ('id', 'name') values (1, 'user');
insert into 'role' ('id', 'name') values (2, 'admin');

create table 'news_status' (
    'id' integer primary key not null,
    'name' text not null unique);
insert into 'news_status' ('id', 'name') values (1, 'active');
insert into 'news_status' ('id', 'name') values (2, 'inactive');
insert into 'news_status' ('id', 'name') values (3, 'deleted');

DROP DATABASE IF EXISTS pafb;
CREATE DATABASE pafb;

\c pafb;

CREATE TABLE test (
	id SERIAL PRIMARY KEY,
	val VARCHAR(3) NOT NULL
);
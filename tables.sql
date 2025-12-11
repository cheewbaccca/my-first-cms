DROP TABLE IF EXISTS articles;
CREATE TABLE articles
(
  id              smallint unsigned NOT NULL auto_increment,
  publicationDate date NOT NULL,   
  categoryId      smallint unsigned NOT NULL,
  title           varchar(255) NOT NULL,                     
  summary         text NOT NULL,                             
  content         mediumtext NOT NULL,                      
 
  PRIMARY KEY     (id)
);

DROP TABLE IF EXISTS users;
CREATE TABLE users
(
  id              smallint unsigned NOT NULL auto_increment,
  login           varchar(50) NOT NULL UNIQUE,
  password        varchar(255) NOT NULL,
  is_active       tinyint(1) NOT NULL DEFAULT 1,
  
  PRIMARY KEY     (id)
);


DROP TABLE IF EXISTS categories;
CREATE TABLE categories
(
  id              smallint unsigned NOT NULL auto_increment,
  name            varchar(255) NOT NULL,
  description     text NOT NULL, 
  
  PRIMARY KEY     (id)
);

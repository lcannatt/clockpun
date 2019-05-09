DROP SCHEMA `clockpun_db`;
CREATE SCHEMA `clockpun_db` DEFAULT CHARACTER SET utf8 ;
USE `clockpun_db`;
CREATE USER 'clockpun'@'localhost' IDENTIFIED BY 'password';
GRANT ALL ON clockpun_db.* TO 'clockpun'@'localhost';

CREATE TABLE `clockpun_db`.`user` (
  `user_id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(25) NOT NULL,
  `password` CHAR(60) NOT NULL,
  `last_name` VARCHAR(45) NULL,
  `first_name` VARCHAR(45) NOT NULL,
  `email` VARCHAR(64) NULL,
  `boss_id` INT(11) NULL,
  `department` INT(11) NULL,
  `flags` INT(11) NULL,
  `recovery_code` VARCHAR(45) NULL,
  `tpr1` VARCHAR(45) NULL,
  `tpr2` VARCHAR(45) NULL,
  PRIMARY KEY (`user_id`))
COMMENT = 'user data';
-- Creates default god mode account username: '1' password: 'tapir'

INSERT INTO user (username,password,last_name,first_name,email,boss_id,department,flags) VALUES ('1','$2y$11$wdyPlvBE3zYQB6iV9Qv1e.SAeYA4Ho70.yR/zfBQv.ffa3oE2yING','User','Supreme','l.cannatti@tricentis.com',1,-1,63);
-- INSERT INTO user (username,password,last_name,first_name,email,boss_id,department,flags) VALUES ('hr','$2y$11$wdyPlvBE3zYQB6iV9Qv1e.SAeYA4Ho70.yR/zfBQv.ffa3oE2yING','User','HR','l.cannatti@tricentis.com',1,1,15);
-- INSERT INTO user (username,password,last_name,first_name,email,boss_id,department,flags) VALUES ('mgr','$2y$11$wdyPlvBE3zYQB6iV9Qv1e.SAeYA4Ho70.yR/zfBQv.ffa3oE2yING','User','Manager','l.cannatti@tricentis.com',1,1,7);
-- INSERT INTO user (username,password,last_name,first_name,email,boss_id,department,flags) VALUES ('usr','$2y$11$wdyPlvBE3zYQB6iV9Qv1e.SAeYA4Ho70.yR/zfBQv.ffa3oE2yING','User','Employee','l.cannatti@tricentis.com',1,1,3);
-- INSERT INTO user (username,password,last_name,first_name,email,boss_id,department,flags) VALUES ('blocked','$2y$11$wdyPlvBE3zYQB6iV9Qv1e.SAeYA4Ho70.yR/zfBQv.ffa3oE2yING','User','Blocked','l.cannatti@tricentis.com',1,1,0);

CREATE TABLE `clockpun_db`.`user_devices` (
  `token_id` INT NOT NULL AUTO_INCREMENT,
  `token` CHAR(16) NULL,
  `user_id` INT(11) NULL,
  `last_login` DATETIME NULL,
  PRIMARY KEY (`token_id`));

CREATE TABLE `clockpun_db`.`time_entered` (
  `time_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `time_start` DATETIME NULL,
  `time_end` DATETIME NULL,
  `category` INT NULL,
  `comment` VARCHAR(150) NULL,
  PRIMARY KEY (`time_id`))
COMMENT = 'tracks time entered by users';

CREATE TABLE `clockpun_db`.`category_defs` (
  `cat_id` INT NOT NULL AUTO_INCREMENT,
  `cat_name` VARCHAR(45) NULL,
  PRIMARY KEY (`cat_id`))
COMMENT = 'General purpose category definitions table';

ALTER TABLE `clockpun_db`.`time_entered` 
ADD INDEX `User_idx` (`user_id` ASC);
ALTER TABLE `clockpun_db`.`time_entered` 
ADD CONSTRAINT `fk_timeUser`
  FOREIGN KEY (`user_id`)
  REFERENCES `clockpun_db`.`user` (`user_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `clockpun_db`.`time_entered` 
ADD INDEX `fk_timeCat_idx` (`category` ASC);
ALTER TABLE `clockpun_db`.`time_entered` 
ADD CONSTRAINT `fk_timeCat`
  FOREIGN KEY (`category`)
  REFERENCES `clockpun_db`.`category_defs` (`cat_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

--LOCAL COPY ONLY:
INSERT INTO category_defs (cat_name) VALUES ('Work'),('PTO'),('Home Office'),('Training');
-- INSERT INTO time_entered (user_id,time_start,time_end,category,comment) 
-- VALUES (1,timestampadd(HOUR,-1,CURRENT_TIMESTAMP()),CURRENT_TIMESTAMP(),1,'working'),
-- (1,timestampadd(HOUR,-4,CURRENT_TIMESTAMP()),timestampadd(HOUR,-1,CURRENT_TIMESTAMP()),1,'working'),
-- (1,timestampadd(HOUR,-5,CURRENT_TIMESTAMP()),timestampadd(HOUR,-4,CURRENT_TIMESTAMP()),4,'training');
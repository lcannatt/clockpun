CREATE SCHEMA `clockpun_db` DEFAULT CHARACTER SET utf8 ;
USE `clockpun_db`;
CREATE USER 'clockpun'@'localhost' IDENTIFIED WITH mysql_native_password BY 'password';
GRANT ALL ON clockpun_db.* TO 'clockpun'@'localhost';

CREATE TABLE `clockpun_db`.`user` (
  `user_id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(25) NOT NULL,
  `password` CHAR(60) NOT NULL,
  `last_name` VARCHAR(45) NULL,
  `first_name` VARCHAR(45) NOT NULL;
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
INSERT INTO user (username,password,first_name,boss_id,flags) VALUES ('1','$2y$11$wdyPlvBE3zYQB6iV9Qv1e.SAeYA4Ho70.yR/zfBQv.ffa3oE2yING','admin',-1,63)

CREATE TABLE `clockpun_db`.`user_devices` (
  `token_id` INT NOT NULL AUTO_INCREMENT,
  `token` CHAR(16) NULL,
  `user_id` INT(11) NULL,
  `last_login` DATETIME NULL,
  PRIMARY KEY (`token_id`));

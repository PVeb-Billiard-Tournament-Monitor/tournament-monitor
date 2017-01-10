-- MySQL Script generated by MySQL Workbench
-- уторак, 10. јануар 2017. 00:55:28 CET
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema billiard_db
-- -----------------------------------------------------
DROP SCHEMA IF EXISTS `billiard_db` ;

-- -----------------------------------------------------
-- Schema billiard_db
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `billiard_db` DEFAULT CHARACTER SET utf8 ;
USE `billiard_db` ;

-- -----------------------------------------------------
-- Table `billiard_db`.`player`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `billiard_db`.`player` ;

CREATE TABLE IF NOT EXISTS `billiard_db`.`player` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NULL,
  `phone` VARCHAR(30) NULL,
  `img_link` VARCHAR(200) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `billiard_db`.`billiard_club`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `billiard_db`.`billiard_club` ;

CREATE TABLE IF NOT EXISTS `billiard_db`.`billiard_club` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `username` VARCHAR(45) NOT NULL,
  `password` VARCHAR(45) NOT NULL,
  `img_link` VARCHAR(45) NULL,
  `email` VARCHAR(100) NULL,
  `phone` VARCHAR(30) NULL,
  `city` VARCHAR(100) NOT NULL,
  `address` VARCHAR(200) NOT NULL,
  `zip_code` VARCHAR(45) NULL,
  `num_of_tables` INT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `billiard_db`.`tournament`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `billiard_db`.`tournament` ;

CREATE TABLE IF NOT EXISTS `billiard_db`.`tournament` (
  `type` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`type`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `billiard_db`.`hosting_tournament`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `billiard_db`.`hosting_tournament` ;

CREATE TABLE IF NOT EXISTS `billiard_db`.`hosting_tournament` (
  `date` DATE NOT NULL,
  `billiard_club_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `entry_fee` INT UNSIGNED NULL DEFAULT 0,
  `prize` INT NULL DEFAULT 0,
  `tournament_type` VARCHAR(45) NOT NULL,
  `tournament_key` VARCHAR(50) NOT NULL,
  `active` TINYINT(1) NOT NULL,
  PRIMARY KEY (`date`, `billiard_club_id`, `tournament_type`),
  INDEX `fk_hosting_tournament_billiard_club_idx` (`billiard_club_id` ASC),
  INDEX `fk_hosting_tournament_tournament1_idx` (`tournament_type` ASC),
  CONSTRAINT `fk_hosting_tournament_billiard_club`
    FOREIGN KEY (`billiard_club_id`)
    REFERENCES `billiard_db`.`billiard_club` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_hosting_tournament_tournament1`
    FOREIGN KEY (`tournament_type`)
    REFERENCES `billiard_db`.`tournament` (`type`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `billiard_db`.`playing_tournament`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `billiard_db`.`playing_tournament` ;

CREATE TABLE IF NOT EXISTS `billiard_db`.`playing_tournament` (
  `player_id` INT UNSIGNED NOT NULL,
  `tournament_date` DATE NOT NULL,
  `billiard_club_id` INT UNSIGNED NOT NULL,
  `tournament_type` VARCHAR(45) NOT NULL,
  `next_round` INT NOT NULL DEFAULT 1,
  `active` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`player_id`, `tournament_date`, `billiard_club_id`, `tournament_type`),
  INDEX `fk_playing_tournament_hosting_tournament1_idx` (`tournament_date` ASC, `billiard_club_id` ASC, `tournament_type` ASC),
  CONSTRAINT `fk_playing_tournament_player1`
    FOREIGN KEY (`player_id`)
    REFERENCES `billiard_db`.`player` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_playing_tournament_hosting_tournament1`
    FOREIGN KEY (`tournament_date` , `billiard_club_id` , `tournament_type`)
    REFERENCES `billiard_db`.`hosting_tournament` (`date` , `billiard_club_id` , `tournament_type`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `billiard_db`.`match`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `billiard_db`.`match` ;

CREATE TABLE IF NOT EXISTS `billiard_db`.`match` (
  `player_id_1` INT NOT NULL,
  `player_id_2` INT NOT NULL,
  `round` TINYINT NOT NULL DEFAULT 1,
  `score_1` TINYINT NOT NULL DEFAULT 0,
  `score_2` TINYINT NOT NULL DEFAULT 0,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `table_id` TINYINT NOT NULL,
  `winner_id` INT NULL,
  `tournament_date` DATE NOT NULL,
  `billiard_club_id` INT UNSIGNED NOT NULL,
  `tournament_type` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`player_id_1`, `player_id_2`, `tournament_date`, `billiard_club_id`, `tournament_type`),
  INDEX `fk_match_hosting_tournament1_idx` (`tournament_date` ASC, `billiard_club_id` ASC, `tournament_type` ASC),
  CONSTRAINT `fk_match_hosting_tournament1`
    FOREIGN KEY (`tournament_date` , `billiard_club_id` , `tournament_type`)
    REFERENCES `billiard_db`.`hosting_tournament` (`date` , `billiard_club_id` , `tournament_type`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `billiard_db`.`currently_registered_tables`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `billiard_db`.`currently_registered_tables` ;

CREATE TABLE IF NOT EXISTS `billiard_db`.`currently_registered_tables` (
  `tournament_key` VARCHAR(50) NOT NULL,
  `table_number` INT NOT NULL,
  PRIMARY KEY (`tournament_key`, `table_number`))
ENGINE = InnoDB;

USE `billiard_db`;

DELIMITER $$

USE `billiard_db`$$
DROP TRIGGER IF EXISTS `billiard_db`.`match_BEFORE_UPDATE` $$
USE `billiard_db`$$
CREATE DEFINER = CURRENT_USER TRIGGER `billiard_db`.`match_BEFORE_UPDATE` BEFORE UPDATE ON `match` FOR EACH ROW
BEGIN
	if new.active = false then
		if (new.score_1 > new.score_2) then
			set new.winner_id = new.player_id_1;
		else
			set new.winner_id = new.player_id_2;
		end if;
	end if;
END$$


DELIMITER ;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `billiard_db`.`player`
-- -----------------------------------------------------
START TRANSACTION;
USE `billiard_db`;
INSERT INTO `billiard_db`.`player` (`id`, `name`, `last_name`, `email`, `phone`, `img_link`) VALUES (DEFAULT, 'Milos', 'Milosevic', 'milos.milosevic@github.com', '+3811234456', '../images/players/placeholder.jpg');
INSERT INTO `billiard_db`.`player` (`id`, `name`, `last_name`, `email`, `phone`, `img_link`) VALUES (DEFAULT, 'Marko', 'Markovic', 'marko.markovic@gmail.com', '+3815674325', '../images/players/placeholder.jpg');
INSERT INTO `billiard_db`.`player` (`id`, `name`, `last_name`, `email`, `phone`, `img_link`) VALUES (DEFAULT, 'Nikola', 'Nikolic', 'nikola.nikolic@yahoo.com', '+3817685469', '../images/players/placeholder.jpg');
INSERT INTO `billiard_db`.`player` (`id`, `name`, `last_name`, `email`, `phone`, `img_link`) VALUES (DEFAULT, 'Jovan', 'Jovanovic', 'jovan.jovanovic@ptt.yu', '+3811232346', '../images/players/placeholder.jpg');

COMMIT;


-- -----------------------------------------------------
-- Data for table `billiard_db`.`billiard_club`
-- -----------------------------------------------------
START TRANSACTION;
USE `billiard_db`;
INSERT INTO `billiard_db`.`billiard_club` (`id`, `name`, `username`, `password`, `img_link`, `email`, `phone`, `city`, `address`, `zip_code`, `num_of_tables`) VALUES (DEFAULT, 'Cue Ball', 'cue_ball', '123456', NULL, 'cue_ball@gmail.com', '+3817584930', 'Belgrade', 'Majke Jevrosime 19', '11000', 2);

COMMIT;


-- -----------------------------------------------------
-- Data for table `billiard_db`.`tournament`
-- -----------------------------------------------------
START TRANSACTION;
USE `billiard_db`;
INSERT INTO `billiard_db`.`tournament` (`type`) VALUES ('Hendikep');
INSERT INTO `billiard_db`.`tournament` (`type`) VALUES ('Drzavni');
INSERT INTO `billiard_db`.`tournament` (`type`) VALUES ('Sponzorisan');

COMMIT;


-- -----------------------------------------------------
-- Data for table `billiard_db`.`hosting_tournament`
-- -----------------------------------------------------
START TRANSACTION;
USE `billiard_db`;
INSERT INTO `billiard_db`.`hosting_tournament` (`date`, `billiard_club_id`, `name`, `entry_fee`, `prize`, `tournament_type`, `tournament_key`, `active`) VALUES ('NOW()', 1, 'bla bla', 0, 0, 'Drzavni', 'proba123', true);

COMMIT;


-- -----------------------------------------------------
-- Data for table `billiard_db`.`playing_tournament`
-- -----------------------------------------------------
START TRANSACTION;
USE `billiard_db`;
INSERT INTO `billiard_db`.`playing_tournament` (`player_id`, `tournament_date`, `billiard_club_id`, `tournament_type`, `next_round`, `active`) VALUES (1, 'NOW()', 1, 'Drzavni', DEFAULT, DEFAULT);
INSERT INTO `billiard_db`.`playing_tournament` (`player_id`, `tournament_date`, `billiard_club_id`, `tournament_type`, `next_round`, `active`) VALUES (2, 'NOW()', 1, 'Drzavni', DEFAULT, DEFAULT);
INSERT INTO `billiard_db`.`playing_tournament` (`player_id`, `tournament_date`, `billiard_club_id`, `tournament_type`, `next_round`, `active`) VALUES (3, 'NOW()', 1, 'Drzavni', DEFAULT, DEFAULT);
INSERT INTO `billiard_db`.`playing_tournament` (`player_id`, `tournament_date`, `billiard_club_id`, `tournament_type`, `next_round`, `active`) VALUES (4, 'NOW()', 1, 'Drzavni', DEFAULT, DEFAULT);

COMMIT;


CREATE TABLE `fabapp-v0.9`.`alert_messages` ( `Id` INT NOT NULL , `Name` VARCHAR(50) NOT NULL , `Message` TEXT NOT NULL , 
PRIMARY KEY (`Id`), UNIQUE (`Name`)) ENGINE = MyISAM;
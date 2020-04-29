CREATE TABLE `fabapp-v0.9`.`alert_messages` ( `Id` INT NOT NULL , `Name` VARCHAR(50) NOT NULL , `Message` TEXT NOT NULL , 
PRIMARY KEY (`Id`), UNIQUE (`Name`)) ENGINE = MyISAM;

INSERT INTO `alert_messages` (`Id`, `Name`, `Message`) 
VALUES ('1', 'completed_ticket', 'Your ticket has been completed! Please report to the FabLab to pick up your items. Please remember to pay your balance. Thankyou for using UTA FabLab. :)');
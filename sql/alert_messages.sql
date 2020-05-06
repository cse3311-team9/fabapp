CREATE TABLE `fabapp-v0.9`.`alert_messages` ( `Id` INT NOT NULL AUTO_INCREMENT, `Name` VARCHAR(50) NOT NULL , `Message` TEXT NOT NULL , 
PRIMARY KEY (`Id`), UNIQUE (`Name`)) ENGINE = MyISAM;

# Optional
INSERT INTO alert_messages
SET Name = 'completed_ticket', Message = 'Your ticket has been completed! Please report to the FabLab to pick up your items. Please remember to pay your balance. Thank you for using UTA FabLab. :)';

# If table already created just run this alter table statement
ALTER TABLE `alert_messages` ADD `EventId` INT NULL DEFAULT NULL AFTER `Message`, ADD UNIQUE (`EventId`);
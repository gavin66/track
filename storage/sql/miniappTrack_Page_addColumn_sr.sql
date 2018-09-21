DELIMITER //
DROP PROCEDURE IF EXISTS miniappTrack_Page_addColumn_sr;
CREATE PROCEDURE miniappTrack_Page_addColumn_sr()
  BEGIN
    DECLARE v_accountId varchar(256);
    DECLARE v_tableName varchar(256);
    DECLARE done bool DEFAULT FALSE ;

    DECLARE Cursor_wx_page CURSOR FOR (SELECT `accountId`,`tableName` FROM kmsocial_third_data.tableInfo where `tableName` like 'wx_page%');
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE ;
    OPEN Cursor_wx_page;
    myLoop: LOOP
      FETCH Cursor_wx_page INTO v_accountId,v_tableName;
      IF done THEN
        LEAVE myLoop;
      END IF;
      IF NOT EXISTS(SELECT 1 FROM information_schema.COLUMNS where TABLE_NAME = v_tableName AND COLUMN_NAME = 'sr') THEN
        set @query = concat('ALTER TABLE ','`',v_accountId,'`.`',v_tableName,'`', ' ADD COLUMN sr text NULL AFTER sceneid;');
        PREPARE stmt from @query;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
      END IF;
    END LOOP myLoop;
    CLOSE Cursor_wx_page;
  END //
DELIMITER ;
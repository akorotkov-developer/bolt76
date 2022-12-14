CREATE TABLE b_strprofi_storage
(
    ID INT(32) NOT NULL auto_increment,
    TIMESTAMP_X TIMESTAMP NOT NULL,
    DATA TEXT NOT NULL,
    PERCENT int(11) NULL default '0',
    PRIMARY KEY b_strprofi_storage(ID)
);
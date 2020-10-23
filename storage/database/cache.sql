CREATE TABLE `cache` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `key` VARCHAR(32) NOT NULL,
    `value` LONGBLOB NULL,
    `compress` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0,1',
    `expires` TIMESTAMP NOT NULL,
    PRIMARY KEY (`id`, `key`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;


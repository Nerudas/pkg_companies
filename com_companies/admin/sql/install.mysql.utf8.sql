CREATE TABLE IF NOT EXISTS `#__companies` (
  `id`          INT(11)          NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(255)     NOT NULL DEFAULT '',
  `alias`       VARCHAR(400)     NOT NULL DEFAULT '',
  `about`       LONGTEXT         NOT NULL DEFAULT '',
  `contacts`    MEDIUMTEXT       NOT NULL DEFAULT '',
  `requisites`  MEDIUMTEXT       NOT NULL DEFAULT '',
  `logo`        TEXT             NOT NULL DEFAULT '',
  `header`      TEXT             NOT NULL DEFAULT '',
  `portfolio`   LONGTEXT         NOT NULL DEFAULT '',
  `state`       TINYINT(3)       NOT NULL DEFAULT '0',
  `created`     DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by`  INT(11)          NOT NULL DEFAULT '0',
  `modified`    DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
  `attribs`     TEXT             NOT NULL DEFAULT '',
  `metakey`     MEDIUMTEXT       NOT NULL DEFAULT '',
  `metadesc`    MEDIUMTEXT       NOT NULL DEFAULT '',
  `access`      INT(10)          NOT NULL DEFAULT '0',
  `hits`        INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `region`      CHAR(7)          NOT NULL DEFAULT '*',
  `metadata`    MEDIUMTEXT       NOT NULL DEFAULT '',
  `tags_search` MEDIUMTEXT       NOT NULL DEFAULT '',
  `tags_map`    LONGTEXT         NOT NULL DEFAULT '',
  UNIQUE KEY `id` (`id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  AUTO_INCREMENT = 0;

CREATE TABLE IF NOT EXISTS `#__companies_employees` (
  `user_id`    INT(10)      NOT NULL,
  `company_id` INT(11)      NOT NULL,
  `position`   VARCHAR(255) NOT NULL DEFAULT '',
  `as_company` TINYINT(3)   NOT NULL DEFAULT '0',
  `key`        TEXT         NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`, `company_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  AUTO_INCREMENT = 0;

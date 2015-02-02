DROP TABLE IF EXISTS `phpshop_modules_retailcrm_system`;
CREATE TABLE IF NOT EXISTS `phpshop_modules_retailcrm_system` (
  `code` varchar(64) NOT NULL default '',
  `value` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;


INSERT INTO `phpshop_modules_retailcrm_system` VALUES ('options', '{"status":"0","email":"integration@retailcrm.ru"}');
DROP TABLE IF EXISTS `config_web`;

#
# Table structure for table 'config_web'
#

CREATE TABLE `config_web` (
  `key` varchar(100) NOT NULL,
  `value` longtext DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) UNSIGNED DEFAULT 0 COMMENT '0 = INACTIVE, 1 = ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB;

#
# Dumping data for table 'config_web'
#

INSERT INTO `config_web` (`key`, `value`, `is_active`) VALUES
     ('app_name','Codeigniter',1);
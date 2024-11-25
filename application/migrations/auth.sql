DROP TABLE IF EXISTS `roles`;

#
# Table structure for table 'roles'
#

CREATE TABLE `roles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `uc_roles_name` UNIQUE (`name`)
) ENGINE=InnoDB;

#
# Dumping data for table 'roles'
#

INSERT INTO `roles` (`name`, `description`) VALUES
     ('devel','Developer'),
     ('admin','Administrator'),
     ('member','General User');


DROP TABLE IF EXISTS `permissions`;

#
# Table structure for table 'permissions'
#

CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '{resource}.{action}',
  `description` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `uc_permissions_name` UNIQUE (`name`)
) ENGINE=InnoDB;

#
# Dumping data for table 'permissions'
#

INSERT INTO `permissions` (`name`, `description`) VALUES
     ('read.user','Read data'),
     ('create.user','Create data'),
     ('update.user','Update data'),
     ('delete.user','Delete data'),
     ('read.role','Read data'),
     ('create.role','Create data'),
     ('update.role','Update data'),
     ('delete.role','Delete data'),
     ('read.permission','Read data'),
     ('create.permission','Create data'),
     ('update.permission','Update data'),
     ('delete.permission','Delete data');


DROP TABLE IF EXISTS `users`;

#
# Table structure for table 'users'
#

CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(192) NOT NULL,
  `name` varchar(192) NOT NULL,
  `password` varchar(192) NOT NULL,
  `activation_selector` varchar(255) DEFAULT NULL,
  `activation_code` varchar(255) DEFAULT NULL,
  `forgotten_password_selector` varchar(255) DEFAULT NULL,
  `forgotten_password_code` varchar(255) DEFAULT NULL,
  `forgotten_password_time` timestamp DEFAULT NULL,
  `remember_selector` varchar(255) DEFAULT NULL,
  `remember_code` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp DEFAULT NULL,
  `is_active` tinyint(1) UNSIGNED DEFAULT 0 COMMENT '0 = INACTIVE, 1 = ACTIVE',
  `phone` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `uc_users_email` UNIQUE (`email`),
  CONSTRAINT `uc_users_activation_selector` UNIQUE (`activation_selector`),
  CONSTRAINT `uc_users_forgotten_password_selector` UNIQUE (`forgotten_password_selector`),
  CONSTRAINT `uc_users_remember_selector` UNIQUE (`remember_selector`)
) ENGINE=InnoDB;


#
# Dumping data for table 'users'
#

INSERT INTO `users` (`name`, `password`, `email`, `is_active`) VALUES
     ('Developer','$2y$08$200Z6ZZbp3RAEXoaWcMA6uJOFicwNZaqk4oDhqTUiFXFe63MG.Daa','devel@gmail.com','1'),
     ('Administrator','$2y$08$200Z6ZZbp3RAEXoaWcMA6uJOFicwNZaqk4oDhqTUiFXFe63MG.Daa','admin@gmail.com','1'),
     ('Member','$2y$08$200Z6ZZbp3RAEXoaWcMA6uJOFicwNZaqk4oDhqTUiFXFe63MG.Daa','member@gmail.com','1');
  

DROP TABLE IF EXISTS `user_roles`;

#
# Table structure for table 'user_roles'
#

CREATE TABLE `user_roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `role_id` int(11) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `uc_user_roles` UNIQUE (`user_id`, `role_id`),
  CONSTRAINT `fk_user_roles_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_roles_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB;

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
     (1,1),
     (2,2),
     (3,3);


DROP TABLE IF EXISTS `role_permissions`;

#
# Table structure for table 'role_permissions'
#

CREATE TABLE `role_permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(11) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `uc_role_permissions` UNIQUE (`role_id`, `permission_id`),
  CONSTRAINT `fk_role_permissions_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_role_permissions_permission_id` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB;

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
     (1,1),
     (2,2),
     (3,3);


DROP TABLE IF EXISTS `login_attempts`;

#
# Table structure for table 'login_attempts'
#

CREATE TABLE `login_attempts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `login` text NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

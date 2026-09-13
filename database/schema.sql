-- 帳號登入 + 身分階層權限 Demo Schema
-- 對應 classes/manager.php（登入）與 classes/permission.php（角色/權限）

-- 身分組：數字越大權限越高。root 為隱含的最高層級，不存在此表，
-- 而是由 `managers`.`role` 為 NULL 表示。
CREATE TABLE `roles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NOT NULL,
  `text` VARCHAR(64) NOT NULL,
  `rank` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 權限清單，僅作為範例保留一筆，未與角色綁定（無 role_permissions 這種細粒度授權表）
CREATE TABLE `permissions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NOT NULL,
  `text` VARCHAR(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 登入帳號。`role` = NULL 表示 root（可控制所有人，不對應 `roles` 表）
CREATE TABLE `managers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(64) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` INT DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `managers_role` (`role`),
  CONSTRAINT `managers_role` FOREIGN KEY (`role`) REFERENCES `roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Demo seed data
-- 身分階層：dev > admin > common（root 為隱含最高層級，不在此表中）
INSERT INTO `roles` (`id`, `name`, `text`, `rank`) VALUES
(1, 'dev', 'Dev', 20),
(2, 'admin', 'Admin', 10),
(3, 'common', 'Common', 0);

INSERT INTO `permissions` (`id`, `name`, `text`) VALUES
(1, 'demo', 'Demo Permission');

-- 內建帳號：admin / admin，身分為 root（`role` = NULL）
-- 密碼為 password_hash('admin', PASSWORD_ARGON2ID) 的結果，正式環境請務必更換密碼。
INSERT INTO `managers` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '$argon2id$v=19$m=65536,t=4,p=1$TXdwZDc1dEh0ZVVXRW5oRQ$HerwqJE83BBG6Ld5ecFARDu2vS/6jLLTxFt/ZTGghoE', NULL);

-- 帳號登入 + 身分階層權限 Demo Schema
-- 對應 classes/manager.php（登入）與 classes/permission.php（角色/權限）

-- 身分組：數字越大權限越高。同時也是 managers.role 的 FK 對象（見下方 `managers` 表），
-- root 也是這裡的一筆（rank 最高），不再用 managers.role = NULL 表示 root。
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

-- 登入帳號。`role` 關聯 `roles`.`id`，NOT NULL，預設為 common（見下方 seed data 的 id）；
-- root 為最高層級，須明確寫上（不再用 NULL 表示），任何人（含其他 root）都無法控制 root。
CREATE TABLE `managers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `role` INT NOT NULL DEFAULT 3,
  `account` VARCHAR(32) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account` (`account`),
  KEY `managers_role` (`role`),
  CONSTRAINT `managers_role` FOREIGN KEY (`role`) REFERENCES `roles` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 登入事件紀錄。不論登入成功或失敗都會寫入一筆（`commit` = 'login' / 'login_failed'），
-- 成功登入才會有 `token`／`expire`；`token` 存活時間可由 configs/manager.php 的 timeout.login 設定，
-- 每次通過驗證的請求都會延長 `expire`。登出時直接把該筆 `expire` 設為當下時間讓 token 失效。
-- 短期內 `login_failed` 累積達到 configs/captcha.php 的 max_attempts 時，下一次登入需要驗證碼。
CREATE TABLE `manager_events` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `manager` INT NOT NULL,
  `commit` VARCHAR(16) NOT NULL,
  `token` VARCHAR(64) DEFAULT NULL,
  `ip` VARCHAR(40) DEFAULT NULL,
  `expire` TIMESTAMP NULL DEFAULT NULL,
  `datetime` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  PRIMARY KEY (`id`),
  KEY `manager_events_token` (`token`, `expire`),
  KEY `manager_events_manager` (`manager`, `commit`, `datetime`),
  CONSTRAINT `manager_events_manager` FOREIGN KEY (`manager`) REFERENCES `managers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Demo seed data
-- 身分階層：root > dev > admin > common
INSERT INTO `roles` (`id`, `name`, `text`, `rank`) VALUES
(1, 'dev', 'Dev', 20),
(2, 'admin', 'Admin', 10),
(3, 'common', 'Common', 0),
(4, 'root', 'Root', 30);

INSERT INTO `permissions` (`id`, `name`, `text`) VALUES
(1, 'demo', 'Demo Permission');

-- 內建帳號：admin / admin，身分為 root（roles.id=4）
-- 密碼為 password_hash('admin', PASSWORD_ARGON2ID) 的結果，正式環境請務必更換密碼。
INSERT INTO `managers` (`id`, `role`, `account`, `password`, `name`) VALUES
(1, 4, 'admin', '$argon2id$v=19$m=65536,t=4,p=1$TXdwZDc1dEh0ZVVXRW5oRQ$HerwqJE83BBG6Ld5ecFARDu2vS/6jLLTxFt/ZTGghoE', 'Admin');

-- ============================================================
-- Aurora Blog 极光博客 数据库初始化脚本
-- 字符集: utf8mb4 | 引擎: InnoDB | 适用: MySQL 5.7+ / 8.0
-- 说明: 管理员账号由 install.php 安装向导创建
-- ============================================================

SET NAMES utf8mb4;

-- 管理员用户表
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL DEFAULT '',
  `nickname` VARCHAR(50) NOT NULL DEFAULT '',
  `avatar` VARCHAR(255) NOT NULL DEFAULT '',
  `bio` VARCHAR(500) NOT NULL DEFAULT '',
  `role` ENUM('admin','editor') NOT NULL DEFAULT 'admin',
  `remember_token` VARCHAR(64) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员用户';

-- 分类表
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(80) NOT NULL,
  `description` VARCHAR(255) NOT NULL DEFAULT '',
  `sort` INT NOT NULL DEFAULT 0,
  `post_count` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='文章分类';

-- 标签表
CREATE TABLE IF NOT EXISTS `tags` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(80) NOT NULL,
  `post_count` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`),
  UNIQUE KEY `uk_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='文章标签';

-- 文章表
CREATE TABLE IF NOT EXISTS `posts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL,
  `summary` VARCHAR(500) NOT NULL DEFAULT '',
  `content` MEDIUMTEXT,
  `cover` VARCHAR(255) NOT NULL DEFAULT '',
  `category_id` INT UNSIGNED DEFAULT NULL,
  `user_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `status` ENUM('draft','published','trash') NOT NULL DEFAULT 'draft',
  `is_top` TINYINT(1) NOT NULL DEFAULT 0,
  `views` INT UNSIGNED NOT NULL DEFAULT 0,
  `likes` INT UNSIGNED NOT NULL DEFAULT 0,
  `comment_status` TINYINT(1) NOT NULL DEFAULT 1,
  `comment_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `published_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`),
  KEY `idx_cat` (`category_id`),
  KEY `idx_status_pub` (`status`,`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='文章';

-- 文章-标签 关联表
CREATE TABLE IF NOT EXISTS `post_tags` (
  `post_id` INT UNSIGNED NOT NULL,
  `tag_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`post_id`,`tag_id`),
  KEY `idx_tag` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='文章标签关联';

-- 评论表
CREATE TABLE IF NOT EXISTS `comments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` INT UNSIGNED NOT NULL,
  `parent_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `nickname` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL DEFAULT '',
  `website` VARCHAR(200) NOT NULL DEFAULT '',
  `content` TEXT NOT NULL,
  `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `ip` VARCHAR(45) NOT NULL DEFAULT '',
  `user_agent` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_post_status` (`post_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='评论';

-- 友情链接表
CREATE TABLE IF NOT EXISTS `links` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(80) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `logo` VARCHAR(255) NOT NULL DEFAULT '',
  `description` VARCHAR(255) NOT NULL DEFAULT '',
  `sort` INT NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='友情链接';

-- 站点设置表
CREATE TABLE IF NOT EXISTS `settings` (
  `skey` VARCHAR(50) NOT NULL,
  `svalue` TEXT,
  PRIMARY KEY (`skey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='站点设置';

-- 访问统计表（按天聚合 PV/UV）
CREATE TABLE IF NOT EXISTS `visits` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `vdate` DATE NOT NULL,
  `pv` INT UNSIGNED NOT NULL DEFAULT 0,
  `uv` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_date` (`vdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='访问统计';

-- 登录失败记录（防暴力破解）
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `ip` VARCHAR(45) NOT NULL,
  `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip_time` (`ip`,`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='登录尝试';

-- ============ 默认数据 ============

INSERT INTO `settings` (`skey`,`svalue`) VALUES
('site_name','Aurora 极光博客'),
('site_desc','记录技术、生活与思考，用文字点亮极光。'),
('site_slogan','Stay curious, keep writing.'),
('site_motto','「用文字点亮极光」'),
('site_hero_eyebrow','✦ Welcome to my space'),
('site_hero_bg',''),
('site_keywords','博客,技术,生活,思考,极光'),
('site_icp',''),
('site_logo',''),
('site_avatar',''),
('site_about','我是 Aurora，一名热爱技术与写作的开发者。\n\n这个博客用来记录：**技术笔记**、**生活感悟**、**读书心得**。\n\n欢迎交流，愿我们在文字中相遇。'),
('site_social_github','https://github.com'),
('site_social_twitter',''),
('site_social_email','hello@aurora.blog'),
('site_notice',''),
('site_notice_enabled','0'),
('pwa_enabled','1'),
('pwa_theme_color','#0a0a0c'),
('pwa_bg_color','#0a0a0c'),
('theme_accent','#f59e0b'),
('theme_mode','dark'),
('smtp_host',''),
('smtp_port','587'),
('smtp_user',''),
('smtp_pass',''),
('smtp_from',''),
('smtp_notify_email',''),
('comment_switch','1'),
('comment_audit','1'),
('page_size','10'),
('footer_text','用 ❤ 与代码构建')
;

INSERT INTO `categories` (`name`,`slug`,`description`,`sort`) VALUES
('技术','tech','编程、架构、工具与效率',1),
('生活','life','日常记录与随想',2),
('读书','reading','书评与摘录',3),
('思考','thinking','深度思考与观点',4);

INSERT INTO `tags` (`name`,`slug`) VALUES
('PHP','php'),
('前端','frontend'),
('数据库','database'),
('随笔','essay');

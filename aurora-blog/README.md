# ✦ Aurora Blog 极光博客

> 一款功能完整、UI 高端大气的个人博客系统。原生 PHP 8 + MySQL，零第三方依赖，专为 **Ubuntu + 宝塔面板** 部署优化。

---

## ✨ 功能总览

### 🌐 前台（访客端）
- 首页：Hero 极光主题 + 置顶文章 + 文章卡片流 + 分页
- 文章详情：Markdown 渲染、自动目录（TOC）、上一篇/下一篇、相关推荐、点赞
- 分类 / 标签 / 归档 / 搜索（全文模糊匹配）
- 评论区：审核机制、嵌套回复、反垃圾（蜜罐 + 频率限制）
- 关于页 + 友情链接展示
- RSS 订阅（`/rss.xml`）、SEO（title/description/keywords）
- **暗色 / 亮色主题一键切换**（localStorage 记忆）
- 全响应式（PC / 平板 / 手机）

### 🛠 后台（可视化管理系统）
- **仪表盘**：文章/评论/分类统计卡片 + 近 14 天 PV/UV 趋势图（纯 SVG 图表，零 CDN 依赖）
- **文章管理**：发布/草稿/回收站三态、置顶、Markdown 编辑器（工具栏 + 实时预览 + 图片上传）、封面图、定时发布、标签自动关联
- **分类管理 / 标签管理**：增删改、文章数统计
- **评论管理**：待审/通过/拒绝/删除，一键处理
- **友情链接**：增删改、排序、显示开关
- **访问统计**：7/30/90 天 PV/UV 趋势、热门文章 Top10、分类分布
- **站点设置**：名称、描述、SEO、ICP、头像、社交链接、关于页、评论开关、每页文章数
- **个人资料**：昵称/邮箱修改、密码修改

### 🔒 安全设计
- PDO 预处理防 SQL 注入（全站参数化查询）
- 输出统一 `htmlspecialchars` 转义防 XSS
- CSRF Token 全表单校验
- 密码 `password_hash()` bcrypt 加密
- 登录失败 5 次锁定 15 分钟
- 图片上传白名单 + 随机文件名 + 5MB 限制
- 自研 Markdown 解析器：先转义再输出白名单标签

---

## 📁 目录结构

```
aurora-blog/
├── public/                  # ⭐ 网站根目录（宝塔站点目录指向这里）
│   ├── index.php            # 前台入口
│   ├── admin/index.php      # 后台入口 (/admin/index.php)
│   ├── assets/css/          # main.css(前台) / admin.css(后台)
│   ├── assets/js/           # main.js(前台) / admin.js(后台)
│   ├── uploads/             # 图片上传目录（需写权限 755）
│   └── .htaccess            # Apache 备用
├── app/
│   ├── core/                # 核心框架 (DB/Auth/View/Helper/路由/Markdown)
│   ├── controllers/         # 前台控制器
│   ├── views/               # 前台模板
│   └── admin/               # 后台控制器 + 视图
├── config/config.sample.php # 配置示例（安装向导自动生成 config.php）
├── database/blog.sql        # 数据库初始化脚本
├── tools/install.php        # 🚀 安装向导（装完请删除）
├── nginx.conf               # 宝塔伪静态规则
└── README.md
```

---

## 🚀 宝塔部署教程（Ubuntu）

### 第 1 步：环境准备
宝塔面板安装 **LNMP**（Nginx + MySQL 5.7/8.0 + PHP 7.4 及以上，推荐 PHP 8.x）。

> PHP 需启用扩展：`pdo_mysql`、`mbstring`、`fileinfo`（宝塔默认已启用）。

### 第 2 步：上传项目
1. 将 `aurora-blog` 整个目录上传到服务器，例如 `/www/wwwroot/aurora-blog`
2. 宝塔 → 网站 → 添加站点：
   - 域名：你的域名（如 `blog.example.com`）
   - 根目录：**`/www/wwwroot/aurora-blog/public`** ← 指向 public！
   - PHP 版本：选择 7.4 或 8.x

### 第 3 步：创建数据库
宝塔 → 数据库 → 添加数据库（记下名称/用户/密码，安装向导要用）。

### 第 4 步：配置伪静态
网站 → 设置 → **伪静态** → 粘贴 `nginx.conf` 文件内容（或直接选 ThinkPHP 模板改一下），核心一行：

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 第 5 步：目录权限
```bash
chmod -R 755 /www/wwwroot/aurora-blog
chmod -R 775 /www/wwwroot/aurora-blog/public/uploads
chmod 775 /www/wwwroot/aurora-blog/config
```

### 第 6 步：运行安装向导
浏览器访问：`http://你的域名/install.php`

1. 环境检测（全绿 ✓）
2. 填写数据库信息 + 管理员账号密码
3. 点击「开始安装」→ 自动建库建表、生成配置、创建管理员
4. 🎉 完成！

### 第 7 步：收尾（重要！）
- ⚠️ **删除 `tools/install.php`**：`rm /www/wwwroot/aurora-blog/tools/install.php`
- 后台入口：`http://你的域名/admin/index.php`
- 前台：`http://你的域名`

---

## ⚙️ 手动安装（可选）

不习惯向导？手动三步：

```bash
# 1. 导入数据库
mysql -u root -p < database/blog.sql

# 2. 生成配置文件
cp config/config.sample.php config/config.php
vim config/config.php   # 填写数据库信息，修改 app.key 为随机字符串

# 3. 手动插入管理员（密码会 bcrypt 加密）
php -r "echo password_hash('你的密码', PASSWORD_DEFAULT);"
mysql -u root -p -e "USE aurora_blog; INSERT INTO users (username, password_hash, email, nickname) VALUES ('admin', '<上面输出的hash>', '', 'admin');"
```

---

## ❓ 常见问题

| 问题 | 解决 |
|------|------|
| 页面 404 / 伪静态不生效 | 检查站点根目录是否为 `public`，伪静态是否已配置 |
| 数据库连接失败 | 检查 `config/config.php` 的 host/端口/账号密码 |
| 图片上传失败 | `public/uploads` 目录权限不足，`chmod -R 775` |
| 后台登录 403 令牌错误 | 清浏览器 Cookie 重试（CSRF 会话过期） |
| 修改了站点名没生效 | 前台设置存在数据库，后台「站点设置」里改 |
| 想启用 HTTPS | 宝塔 → SSL → Let's Encrypt 一键申请 |

---

## 📌 技术栈

| 层 | 技术 |
|----|------|
| 后端 | 原生 PHP 8（OOP + PDO + 单文件路由） |
| 数据库 | MySQL 5.7+ / 8.0（utf8mb4） |
| 前端 | 原生 HTML5 / CSS3（玻璃拟态）/ JS（ES6+） |
| 图表 | 纯 SVG 自绘（零 CDN 依赖，离线可用） |
| 部署 | Nginx 伪静态 / Apache .htaccess 双支持 |

---

## 🛡 安全建议

1. 删除 `tools/install.php`
2. 定期修改后台密码（建议 12 位以上混合密码）
3. 生产环境将 `config/config.php` 中 `debug` 保持 `false`
4. 如非必要，不要给数据库使用 root 账号（建议单独建库建账号）

---

*Aurora Blog v1.0 · 用 ❤ 与代码构建 · MIT License*

# SQL注入测试站点

> 这是一个专门设计用于教学SQL注入技术的易受攻击的Web应用程序。该项目提供了一个具有已知SQL注入漏洞的简单登录页面，用于教育目的。

⚠️ **警告：** 此应用程序是故意设置漏洞的，仅用于教育用途。请勿在任何生产环境中部署此应用。

---

## 概述

本项目模拟了一个具有故意SQL注入漏洞的基础登录页面，帮助学生和专业人士学习：

- 基本的SQL注入技术
- SQLMap的使用
- Web应用安全测试
- 数据库利用

---

## 前置要求

运行此项目需要安装以下软件：

- PHP 7.0或更高版本
- SQLite3
- Python 3.x（用于SQLMap）
- SQLMap

---

## 安装

1. 克隆仓库：

   ```bash
   git clone https://github.com/ctkqiang/TestSiteSQLinjection.git
   cd TestSiteSQLinjection
   ```

2. 初始化数据库：

   ```sql
   -- 首次运行时将自动执行以下SQL
   CREATE TABLE IF NOT EXISTS users (
       id INTEGER PRIMARY KEY,
       username TEXT,
       password TEXT
   );

   -- 已预先填充示例用户数据
   -- 包括管理员和普通用户账户
   ```

---

## 运行应用

1. 启动PHP开发服务器：

   ```bash
   php -S 0.0.0.0:3000
   ```

2. 访问应用：

   - 打开浏览器并访问 `http://localhost:3000`
   - 将显示登录页面

---

## SQL注入测试

### 手动测试

尝试这些SQL注入载荷来探索漏洞：

1. **基本认证绕过：**

   ```sql
   ' OR '1'='1
   ```

2. **基于UNION的注入：**

   ```sql
   ' UNION SELECT username, password, id FROM users--
   ```

3. **基于注释的绕过：**

   ```sql
   admin'--
   ```

### 使用SQLMap进行自动化测试

运行SQLMap来自动化SQL注入测试：

```bash
python3 sqlmap.py -u "http://localhost:3000/index.php" --data="username=admin&password=' OR 1=1--" --dbms=sqlite --dump
```

**SQLMap参数说明：**

- `-u`：目标URL
- `--data`：带注入点的POST数据
- `--dbms`：指定数据库类型（SQLite）
- `--dump`：获取数据库内容

---

## 成功指标

当成功利用漏洞时，应用程序将：

1. 在浏览器控制台记录"hacked"
2. 显示检索到的用户信息
3. 显示成功登录消息

---

## 数据库结构

应用程序使用SQLite，其架构如下：

```sql
users
├── id (INTEGER PRIMARY KEY)
├── username (TEXT)
└── password (TEXT)
```

数据库预先填充了测试账户，包括：

- **普通用户：** john.doe, jane.smith等
- **管理员用户：** admin/admin123, admin/secretpass123

---

## 安全提示

此应用程序包含故意的漏洞，例如：

- 未转义的SQL查询
- SQL语句中直接使用用户输入
- 明文密码存储
- 无输入净化

**禁止：**

- 在生产环境中使用此代码
- 在公共服务器上部署
- 使用真实凭据或敏感数据

---

## 教育目标

通过使用此项目，你将学习：

1. SQL注入漏洞如何产生
2. 识别SQL注入点的方法
3. 使用自动化工具进行安全测试
4. 通过利用漏洞理解数据库结构
5. 正确输入净化的重要性

---

## 贡献

欢迎贡献！你可以通过以下方式帮助：

- 添加新的测试漏洞
- 改进文档
- 创建额外的学习资源
- 添加更多测试用例

---

### 想成为黑客高手？

🚀 我已将10年的专业知识浓缩成一本强大的电子书。学习高级技术、实际示例和分步命令，掌握使用SQLMap进行SQL注入的技巧。

[立即购买](https://ko-fi.com/s/5ad8a06662)，开启你的学习之旅！

#SQL注入 #黑客 #网络安全 #道德黑客 #电子书
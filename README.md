# 🚀 Starter Kit Fullstack PHP Native

![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-777BB4?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/mysql-%2300f.svg?style=flat-square&logo=mysql&logoColor=white)
![SQLite](https://img.shields.io/badge/sqlite-%2307405e.svg?style=flat-square&logo=sqlite&logoColor=white)
![Docker](https://img.shields.io/badge/docker-%230db7ed.svg?style=flat-square&logo=docker&logoColor=white)
![Bootstrap](https://img.shields.io/badge/bootstrap-%23563D7C.svg?style=flat-square&logo=bootstrap&logoColor=white)

A powerful, lightweight, **Fullstack Mini-Framework** built with **Native PHP**. It follows strict **MVC Architecture**, utilizes **PDO** for database abstraction (compatible with MySQL & SQLite), and features a modern, responsive Dashboard UI.

Designed for developers who want a solid foundation without the overhead of heavy frameworks like Laravel or Symfony, while keeping modern standards like **JWT Authentication**, **Composer Autoloading**, and **Docker** support.

---

## ✨ Features

*   **🏗 MVC Architecture**: Clean separation of concerns (Models, Views, Controllers).
*   **🔌 Database Agnostic**: Switch between **MySQL** and **SQLite** just by changing the `.env`.
*   **🔒 Secure Authentication**:
    *   Full Login/Register/Forgot Password flow.
    *   **JWT** (JSON Web Tokens) for API-first security.
    *   Password Hashing using `bcrypt`.
*   **🛡 Security Best Practices**:
    *   XSS Protection & Output Sanitization.
    *   CSRF Protection Middleware.
    *   Secure Headers.
*   **🎨 Modern UI**: Beautiful, responsive dashboard built with **Bootstrap 5**.
*   **⚙️ API & Views**: Hybrid architecture serving both RESTful JSON APIs and Server-Side Rendered Views.
*   **🐳 Docker Ready**: Production-ready `Dockerfile` with multi-container setup (App + MySQL).
*   **🧪 Automated API Testing**: Included Python scripts for endpoint verification.

---

## 📂 Project Structure

```text
├── api_tests/            # Python scripts for API Testing
├── config/               # App & Database configurations
├── database/             # Migrations & SQLite file
├── public/               # Web Root (Entry Point)
│   ├── assets/           # CSS, JS, Images
│   └── index.php         # Main Bootstrapper
├── routes/               # Route definitions (Web & API)
├── src/                  # Application Logic
│   ├── Controllers/      # Request Handlers
│   ├── Core/             # Framework Core (Router, Model, Request)
│   ├── Middlewares/      # Auth & Security Middleware
│   ├── Models/           # Database Models
│   ├── Services/         # Business Logic Layer
│   └── Utils/            # Helpers & Validators
├── views/                # HTML Templates (Layouts, Pages)
├── .env.example          # Environment variables template
├── composer.json         # Dependencies
├── Dockerfile            # Docker Configuration
└── README.md             # Documentation
```

---

## 🛠️ Getting Started (Local Development)

**Recommended:** We suggest running the project locally first to understand the structure before containerizing it.

### Prerequisites
*   PHP >= 8.2
*   Composer
*   MySQL (or use the built-in SQLite)

### 1. Installation

Clone the repository and install dependencies:

```bash
git clone https://github.com/mnabielap/starter-kit-fullstack-phpnative-template.git
cd starter-kit-fullstack-phpnative-template
composer install
```

### 2. Environment Setup

Copy the example environment file:

```bash
cp .env.example .env
```

Open `.env` and configure your database.
*   **For SQLite:** Set `DB_CONNECTION=sqlite` (No further setup needed).
*   **For MySQL:** Set `DB_CONNECTION=mysql` and fill in your credentials (`DB_HOST`, `DB_DATABASE`, etc.).

### 3. Database Migration

Since this is a native project, you need to import the schema manually.

*   **MySQL:** Import `database/schema.mysql.sql` into your database tool (phpMyAdmin / TablePlus).
*   **SQLite:** The app creates the file automatically, but you may need to run:
    ```bash
    sqlite3 database/db.sqlite < database/schema.sqlite.sql
    ```

### 4. Run the Application

You can use the built-in PHP server:

```bash
cd public
php -S localhost:3000
```

Open your browser and visit: **`http://localhost:3000`**

---

## 🐳 Getting Started (Docker)

If you prefer using Docker, follow these steps to set up a persistent environment with a separate MySQL container.

### 1. Create Network
Create a shared network for the application and database to communicate.

```bash
docker network create fullstack_phpnative_network
```

### 2. Create Volumes
Create volumes to ensure your Database data and Uploaded files persist even if containers are deleted.

```bash
docker volume create fullstack_phpnative_db_volume
docker volume create fullstack_phpnative_media_volume
docker volume create fullstack_phpnative_mysql_data
```

### 3. Setup Environment
Create a specific `.env` file for Docker:

```bash
cp .env.example .env.docker
```

**Important:** Inside `.env.docker`, set `DB_HOST` to the name of the mysql container we will create next:
```ini
DB_CONNECTION=mysql
DB_HOST=fullstack-phpnative-mysql
```

### 4. Run MySQL Container
Start the MySQL database container.

```bash
docker run -d \
  --name fullstack-phpnative-mysql \
  --network fullstack_phpnative_network \
  -v fullstack_phpnative_mysql_data:/var/lib/mysql \
  -e MYSQL_ROOT_PASSWORD=rootpassword \
  -e MYSQL_DATABASE=starter_kit_db \
  mysql:8.0
```

*Wait a few seconds for MySQL to initialize.* Then, import the schema:
```bash
docker exec -i fullstack-phpnative-mysql mysql -uroot -prootpassword starter_kit_db < database/schema.mysql.sql
```

### 5. Build & Run App Container
Build the image and run the application container.

```bash
# Build Image
docker build -t fullstack-phpnative-app .

# Run Container
docker run -d -p 5005:5005 \
  --env-file .env.docker \
  --network fullstack_phpnative_network \
  -v fullstack_phpnative_db_volume:/var/www/html/database \
  -v fullstack_phpnative_media_volume:/var/www/html/public/uploads \
  --name fullstack-phpnative-container \
  fullstack-phpnative-app
```

🚀 **Done!** Access your app at: **`http://localhost:5005`**

---

## 📦 Docker Management Cheat Sheet

Here are the essential commands to manage your containers.

#### 📜 View Logs
See what's happening inside your application.
```bash
docker logs -f fullstack-phpnative-container
```

#### 🛑 Stop Container
Safely stop the running application.
```bash
docker stop fullstack-phpnative-container
```

#### ▶️ Start Container
Resume a stopped container.
```bash
docker start fullstack-phpnative-container
```

#### 🗑 Remove Container
Remove the container (your data stays safe in the volumes).
```bash
docker stop fullstack-phpnative-container
docker rm fullstack-phpnative-container
```

#### 📂 View Volumes
List all persistent storage volumes.
```bash
docker volume ls
```

#### ⚠️ Remove Volume
**WARNING:** This deletes your database and uploads **permanently**.
```bash
docker volume rm fullstack_phpnative_db_volume
docker volume rm fullstack_phpnative_mysql_data
```

---

## 🧪 API Testing

This project comes with a suite of Python scripts to test the API endpoints automatically without needing Postman.

### Setup
1.  Ensure you have Python installed.
2.  Navigate to the `api_tests` folder (or where you placed the scripts).
3.  The scripts use `utils.py` to manage configuration and tokens automatically.

### Running Tests
Run the scripts simply by executing the file. No arguments needed.

**1. Authentication Flow:**
```bash
# Register a new user
python A1.auth_register.py

# Login (Saves token to secrets.json)
python A2.auth_login.py

# Refresh Token
python A3.auth_refresh_tokens.py
```

**2. User Management (Admin):**
*Note: You must log in as an Admin first.*

```bash
# Login as Admin
python B0.admin_login.py

# Create a User
python B1.user_create.py

# Get All Users
python B2.user_get_all.py

# Get Specific User
python B3.user_get_one.py

# Update User
python B4.user_update.py

# Delete User
python B5.user_delete.py
```

---

## 📝 License

This project is open-source and available under the **MIT License**.
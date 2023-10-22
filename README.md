# Writeomatic.app

Welcome to `Writeomatic.app`, a web application powered by the Laravel framework.

## 📋 Table of Contents

- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Running the App](#running-the-app)

## 🔧 Prerequisites

1. PHP >= 8.1
2. [Composer](https://getcomposer.org/) - PHP's dependency manager.
3. Laravel CLI
4. MySQL (Create a SQL export from the host and import it locally to ur SQL, as there seems to be an issue with the migrations and seeders currently)
5. A web server (like Apache or Nginx) or you can use Laravel's built-in server.

## 📦 Installation

1. **Clone the Repository:**
    ```bash
    git clone https://github.com/your-repo/writeomatic.app.git
    cd writeomatic.app
    ```

2. **Install PHP Dependencies:**
    ```bash
    composer install
    ```

3. **Copy Configuration File (Make sure to set the variable values aswell):**
    ```bash
    cp .env.example .env
    ```

## ⚙️ Configuration

1. **Generate Application Key:**
    ```bash
    php artisan key:generate
    ```

2. **Configure Environment Variables:**  
    Edit the `.env` file to set up your database, mail, cache, and other configurations including third-party services like Google, Facebook, GitHub, and Unsplash.

## 🗄️ Database Setup

1. **Run Migrations:**  
    This command will set up your database tables.
    ```bash
    php artisan migrate
    ```

## 🚀 Running the App

1. **Start the Laravel Development Server:**
    ```bash
    php artisan serve
    ```
   Navigate to `http://localhost:8000` in your web browser.

## 🔑 API Credentials

Make sure to fill in your third-party service credentials (e.g., Google, Facebook, GitHub, Unsplash) in the `.env` file before using features that depend on these services.

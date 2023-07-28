# Electroshop - Laravel E-commerce Project 👨‍💻

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

Welcome to your Electroshop project! This repository is meant to help you get started with building an e-commerce website for technology devices using the Laravel framework, one of the most popular PHP frameworks for web development. 🚀

## Getting Started 🛠️

Follow the instructions below to set up the Electroshop project on your local machine using XAMPP:

### Prerequisites 📋

Before you begin, ensure that you have the following installed:

- [XAMPP](https://www.apachefriends.org/index.html) (or any other local development environment that includes PHP, MySQL, and Apache)

### Installation ⚙️

1. Clone the repository to your local machine:

   ```bash
   git clone https://github.com/your-username/electroshop.git
   cd electroshop
   ```

2. Install PHP dependencies using Composer:

   ```bash
   composer install
   ```

3. Install JavaScript dependencies using npm:

   ```bash
   npm install
   ```

4. Create a copy of the `.env.example` file and name it `.env`. Update the necessary environment variables such as database connection settings.

   ```bash
   cp .env.example .env
   ```

5. Generate a new application key:

   ```bash
   php artisan key:generate
   ```

6. Set up your database: Create a new database in XAMPP (e.g., `electroshop_db`) and update the `.env` file with your database credentials.

7. Run the database migrations to create the required tables:

   ```bash
   php artisan migrate
   ```

8. Finally, start the development server:

   ```bash
   php artisan serve
   ```

Congratulations! You now have the Electroshop project up and running using XAMPP. Visit `http://localhost:8000` in your web browser to access the application. 🎉

## Project Structure 📁

The project follows a standard Laravel directory structure with some additional directories:

- `app`: Contains the core application files.
- `config`: Contains configuration files.
- `database`: Contains database-related files, including migrations and seeders.
- `public`: The web server's document root.
- `resources`: Contains assets, views, and language files.
- `routes`: Contains route definitions.
- `storage`: Contains logs, cache, and other storage-related files.
- `tests`: Contains test cases.
- `vendor`: Contains Composer dependencies.
- `node_modules`: Contains npm packages.

## Contributing 👥

If you find any issues or have suggestions for improvements, feel free to create an issue or submit a pull request. Your contributions are greatly appreciated! 🙌

## Resources 📚

- Laravel Official Website: [https://laravel.com/](https://laravel.com/)
- Laravel Documentation: [https://laravel.com/docs/](https://laravel.com/docs/)

Happy coding and enjoy building your Electroshop e-commerce project! 😃✨

Feel free to update any other information or features you plan to include in your Electroshop project. Happy coding!

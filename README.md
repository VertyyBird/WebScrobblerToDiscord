
# WebScrobblerToDiscord

This project is a web application that allows users of a Discord server to generate a API key to push Web-Scrobbler scrobbles to via webhook. The application includes an admin interface for managing users.

## Features

- User authentication and session management
- Admin interface for managing users
- Generate and revoke API keys
- Separate views for admins and regular users

## Requirements

- PHP 7.4 or higher
- MySQL or MariaDB

## Installation

1. Clone the repository:
   ```sh
   git clone https://github.com/VertyyBird/WebScrobblerToDiscord.git
   cd WebScrobblerToDiscord```
2. Configure the database:
 * Create a MySQL database and user.
 * Import the provided SQL schema to set up the database structure.
 ```mysql -u your_database_username -p your_database_name < sql/schema.sql```
 * Update the `config-example.php` file with your database credentials and rename it to `config.php`.
3. Start the PHP built-in server:
```php -S localhost:8000```
4. Open your browser and navigate to `http://localhost:8000`

## Configuration
The configuration file `config-example.php` contains the database connection details and other settings and then rename the file to `config.php`. Update this file with your specific configuration.
Some things like the site URL are still hardcoded, so you'll have to go through and change those if you want to use this for your own project.

## Usage
* **Admin Interface:** Admin users can log in and manage other users, including suspending, unsuspending, deleting users, and issuing or revoking API keys.
* **User Interface:** Regular users can log in and view their account details and generate an API key. Planning on implementing a way to sign in to Discord so the user doesn't have to input their Discord username. Also want some sort of statistics so the user has some reason to come back.

## Contributing
Contributions are welcome! Please open an issue or submit a pull request for any improvements or bug fixes.
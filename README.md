# Vaccination Registration System

This project is a Laravel 11-based vaccination registration system that allows users to register for COVID vaccinations
and check their vaccination status.

## Features

- User registration for vaccination
- Vaccination status checking
- Automated email reminders for upcoming vaccinations
- Vaccination center management

## Requirements

- Laravel ^11.0
- PHP 8.3+
- Composer
- MySQL 8.0+
- Node.js 18+ and npm

## Installation

### General Approach

1. Clone the repository:
   ```
   git clone https://github.com/takielias/vaccine-box.git
   cd vaccine-box
   ```

2. Install PHP dependencies:
   ```
   composer install
   ```

3. Copy the `.env.example` file to `.env` and configure your database settings:
   ```
   cp .env.example .env
   ```

4. Generate an application key:
   ```
   php artisan key:generate
   ```

5. Run database migrations and seeders:
   ```
   php artisan migrate --seed
   ```

6. Install and compile front-end dependencies:
   ```
   npm install
   npm run build
   ```

7. Start the development server:
   ```
   php artisan serve
   ```

8. Visit `http://localhost:8000` in your browser.

### If you familiar with ddev. You may try the following approach. It's a docker based local development solution.

### DDEV Approach

1. Install DDEV if you haven't already: [DDEV Installation](https://ddev.readthedocs.io/en/stable/users/install/)

2. Clone the repository:
   ```
   git clone https://github.com/takielias/vaccine-box.git
   cd vaccine-box
   ```

3. Start DDEV:
   ```
   ddev start
   ```

4. Install dependencies and set up the project:
   ```
   ddev composer install
   ddev npm install
   ddev npm run build
   ```

5. Copy the `.env.example` file to `.env`:
   ```
   ddev cp .env.example .env
   ```

6. Generate an application key:
   ```
   ddev artisan key:generate
   ```

7. Run database migrations and seeders:
   ```
   ddev artisan migrate --seed
   ```

8. Visit `https://vaccine-box.ddev.site` in your browser.

## Scheduled Tasks

The project includes a scheduled task to send email reminders. To run the scheduler locally:

```
php artisan schedule:work
```

Or with DDEV:

```
ddev artisan schedule:work
```

## Email Schedule

Before start checking Email Schedule, make sure you have a valid `RESEND_API_KEY`. You may generate free api key from https://resend.com.
Put the key in you `.env`

```ssh
RESEND_API_KEY=Your Resend Api Key
```
Make sure you have started `artisan schedule:work`

Execute the following command to check the Email Sending Feature

```ssh
php artisan app:send-emails
```

Or with DDEV:

```
ddev artisan app:send-emails
```

## Running Tests

To run the test suite:

#### You may change `.env.testing` according to your needs.

```
php artisan test
```

Or with DDEV:

```
ddev artisan test
```

## License

This project is licensed under the MIT License.

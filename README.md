# Budget Tracker – Evozon PHP Internship Hackathon 2025

## Starting from the skeleton

Prerequisites:

- PHP >= 8.1 with the usual extension installed, including PDO.
- [Composer](https://getcomposer.org/download)
- Sqlite3 (or another database tool that allows handling SQLite databases)
- Git
- A good PHP editor: PHPStorm or something similar

About the skeleton:

- The skeleton is built on Slim (`slim/slim : ^4.0`)
- The templating engine of choice is Twig (`slim/twig-view`)
- The dependency injection container of choice is `php-di/php-di`
- The database access layer of choice is plain PDO
- The configuration should be provided in a .env file (`vlucas/phpdotenv`)
- There is logging support by using `monolog/monolog`
- Input validation should be simply done using `webmozart/assert` and throwing Slim dedicated HTTP exceptions

## Step-by-step set-up

Install dependencies:

```
composer install
```

Set up the database:

```
cd database
./apply_migrations.sh
```

Note: be aware that, if you are using WSL2 (Windows Subsystem for Linux), you'll have trouble opening SQLite databases
with a DB management app (PHPStorm, for example) in Windows **when they are stored within the virtualized WSL2 drive**.
The solution is to store the `db.sqlite` file on the Windows drive (`/mnt/c`) and configure the path to the file in the
application config (`.env`):

```
cd database
./apply_migrations.sh /mnt/c/Users/<user>/AppData/Local/Temp/db.sqlite
```

Copy `.env.example` to `.env` and configure as necessary:

```
cp .env.example .env
```

Run the built-in server on http://localhost:8000

```
composer start
```

## Features

## Tasks

### Before you start coding

Make sure you inspect the skeleton and identify the important parts:

- `public/index.php` - the web entry point
- `app/Kernel.php` - DI container and application setup
- classes under `app` - this is where most of your code will go
- templates under `templates` are almost complete, at least in terms of static mark-up; all you need is to make use of
  the Twig syntax to make them dynamic.

### Main tasks — for having a functional application

Start coding: search for `// TODO: ...` and fill in the necessary logic. Don't limit yourself to that; you can do
whatever you want, design it the way you see fit. The TODOs are a starting point that you may choose to use.

### Extra tasks — for extra points

Solve extra requirements for extra points. Some of them you can implement from the start, others we prefer you to attack
after you have a fully functional application, should you have time left. More instructions on this in the assignment.

### Deliver well designed quality code

Before delivering your solution, make sure to:

- format every file and make sure there is no commented code left, and code looks spotless

- run static analysis tools to check for code issues:

```
composer analyze
```

- run unit tests (in case you added any):

```
composer test
```

A solution with passing analysis and unit tests will receive extra points.

## Delivery details

Participant:
- Full name: Ardai Artur Sebastian
- Email address:ardaiartur@gmail.com

Features fully implemented:
- Page / Route
Register /register
(GET, POST)
Login /login
(GET, POST)
Logout /logout
(GET)
Expenses – List /expenses
(GET)
Expenses – Add /expenses/create
(GET)
Expenses – Edit /expenses/{id}/edit
(GET)
Expenses – Delete /expenses/{id}/delete 
(POST)
Dashboard /
(GET)
Expenses - CSV Import (as part of Expenses - List page)

Extra:
Throughout the application:
use prepared statements always when querying the DB.
ensure a user may change/delete only his/her own expenses.
Optimize the database schema:
add the relevant table indexes given the implemented use cases. Ensure that the change to the database schema is provided as an incremental migration_*.sql file.
Register user:
using the proper password hashing function in PHP.
implement a “password again” input for ensuring no password typos.
make the register user form CSRF-proof.
Login user:
using the proper password verify function in PHP.
prevent session fixation attacks.
make the login user form CSRF-proof
Categories and budgets per category:
move categories and budget thresholds as configuration options defined in the .env file.
Delete expense:
show a success/failure message on the Expenses – List page, when redirecting after delete (flash message).
CSV file upload:
showing a success message at the top of the  Expenses – List when redirecting after import, containing the total number of imported expenses (flash message).

Other instructions about setting up the application (if any): ...

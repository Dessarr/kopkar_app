# Technical Context: Kopkar App

## Technologies Used

### Backend

-   **Laravel Framework**: PHP 8.x with Laravel 9.x/10.x
-   **Database**: SQLite (development), MySQL/PostgreSQL (production ready)
-   **ORM**: Eloquent ORM for database operations
-   **Authentication**: Laravel's built-in authentication system

### Frontend

-   **Blade Templates**: Laravel's templating engine
-   **Tailwind CSS**: Utility-first CSS framework for styling
-   **JavaScript**: Vanilla JS with some jQuery for interactions
-   **Icons**: Heroicons and Font Awesome for UI icons

### Development Tools

-   **Composer**: PHP dependency management
-   **Artisan**: Laravel command-line interface
-   **Vite**: Asset bundling and development server
-   **PHPUnit**: Testing framework

### Third-party Packages

-   **Laravel Excel**: Excel file import/export functionality
-   **DomPDF**: PDF generation for reports
-   **Carbon**: Date/time manipulation

## Development Setup

### Prerequisites

-   PHP 8.0 or higher
-   Composer
-   Node.js and npm
-   SQLite or MySQL/PostgreSQL

### Installation Steps

1. Clone repository
2. Run `composer install`
3. Copy `.env.example` to `.env`
4. Configure database settings
5. Run `php artisan migrate`
6. Run `php artisan db:seed`
7. Run `npm install && npm run dev`

### Database Structure

-   **24 Migration Files**: Comprehensive database schema
-   **Seeders**: Sample data for development
-   **Factories**: Test data generation

## Technical Constraints

### Performance

-   SQLite for development may have limitations with concurrent users
-   Large datasets require pagination
-   File uploads need size limits

### Security

-   File upload validation required
-   Input sanitization for all user inputs
-   CSRF protection on all forms
-   Proper authentication middleware

### Scalability

-   Database indexing for large datasets
-   Caching strategy for frequently accessed data
-   Asset optimization for production

## Dependencies

### Core Laravel Packages

-   `laravel/framework`
-   `laravel/sanctum` (if API needed)
-   `laravel/tinker`

### Additional Packages

-   `maatwebsite/excel`: Excel operations
-   `barryvdh/laravel-dompdf`: PDF generation
-   `nesbot/carbon`: Date handling

### Development Dependencies

-   `phpunit/phpunit`: Testing
-   `mockery/mockery`: Test mocking
-   `fakerphp/faker`: Test data generation

## Configuration Files

-   `config/app.php`: Application settings
-   `config/database.php`: Database configuration
-   `config/excel.php`: Excel package settings
-   `config/filesystems.php`: File storage settings
-   `config/mail.php`: Email configuration

## Environment Variables

-   `DB_CONNECTION`: Database type
-   `DB_DATABASE`: Database name/path
-   `APP_ENV`: Environment (local/production)
-   `APP_DEBUG`: Debug mode
-   `MAIL_*`: Email configuration

## File Structure

```
app/
├── Http/Controllers/    # Application controllers
├── Models/             # Eloquent models
├── Services/           # Business logic services
├── Imports/            # Excel import classes
└── Exports/            # Excel export classes

resources/
├── views/              # Blade templates
├── css/                # Stylesheets
└── js/                 # JavaScript files

database/
├── migrations/         # Database schema
├── seeders/            # Data seeders
└── factories/          # Model factories
```

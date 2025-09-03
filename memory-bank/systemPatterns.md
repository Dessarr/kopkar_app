# System Patterns: Kopkar App

## Architecture Overview

The application follows Laravel's MVC architecture with additional service layers for business logic.

## Key Technical Decisions

-   **Framework**: Laravel (PHP) for robust web application development
-   **Database**: SQLite for development, with migration support for production databases
-   **Frontend**: Blade templates with Tailwind CSS for responsive design
-   **File Storage**: Laravel's storage system for member photos and documents
-   **Import/Export**: Laravel Excel for handling bulk data operations

## Design Patterns in Use

### 1. Service Layer Pattern

-   `PinjamanService.php`: Handles loan-related business logic
-   `ActivityLogService.php`: Manages activity logging across the system

### 2. Repository Pattern (Implicit)

-   Models act as repositories with Eloquent ORM
-   100+ models covering all business entities

### 3. Import/Export Pattern

-   `BillingUploadImport.php`: Handles billing data imports
-   `SetoranImport.php`: Manages savings deposit imports
-   `ToserdaImport.php`: Processes TOSERDA data imports
-   `AnggotaExport.php`: Exports member data

### 4. Middleware Pattern

-   Authentication middleware for protected routes
-   Custom middleware for specific business rules

## Component Relationships

### Core Models

-   **Anggota**: Central member model
-   **Simpanan**: Savings management
-   **Pinjaman**: Loan management
-   **Billing**: Monthly billing system
-   **TransaksiKas**: Cash transaction tracking

### View Structure

```
resources/views/
├── layouts/          # Base layouts
├── member/           # Member-facing views
├── admin/            # Admin interface
├── auth/             # Authentication views
├── billing/          # Billing management
├── pinjaman/         # Loan management
├── simpanan/         # Savings management
├── laporan/          # Reports
└── master-data/      # Master data management
```

### Controller Organization

-   **Member Controllers**: Handle member-facing functionality
-   **Admin Controllers**: Manage administrative operations
-   **Billing Controllers**: Process billing operations
-   **Report Controllers**: Generate various reports

## Database Design Patterns

-   **Normalized Structure**: Proper foreign key relationships
-   **Audit Trail**: Activity logging for important operations
-   **Soft Deletes**: Maintains data integrity
-   **Timestamps**: Automatic created/updated tracking

## Security Patterns

-   **Authentication**: Laravel's built-in auth system
-   **Authorization**: Role-based access control
-   **CSRF Protection**: Built-in CSRF tokens
-   **Input Validation**: Request validation classes
-   **File Upload Security**: Secure file handling

## Performance Patterns

-   **Eager Loading**: Optimized database queries
-   **Caching**: Strategic caching for frequently accessed data
-   **Pagination**: Large dataset handling
-   **Asset Optimization**: Minified CSS/JS for production

# Student Database Management System

## Overview
This system provides a comprehensive solution for managing student data, course enrollments, grades, and academic reporting. It allows users to generate visual reports, charts, and PDF documents based on the student database.

## System Requirements
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, etc.)
- GD Library for PHP (for image generation)
- Browser with JavaScript enabled

## Installation

### Prerequisites
1. Ensure WAMP, XAMPP, or similar web server stack is installed and running
2. Verify PHP and MySQL are properly configured

### Setup Instructions
1. Clone or download this repository to your web server's document root (e.g., `www` or `htdocs` folder)
2. Navigate to the setup page in your browser:
   ```
   http://localhost/StudentID_FullName_db3/setup.php
   ```
3. Follow the on-screen instructions to set up the database

## Configuration
1. Open `config.php` to configure your database connection:
   - Database hostname (default: localhost)
   - Database username
   - Database password
   - Database name

2. Customize chart and report settings as needed.

## Usage Guide

### Accessing the System
1. Open your web browser and navigate to:
   ```
   http://localhost/StudentID_FullName_db3/
   ```
2. Log in with your credentials (if authentication is enabled)

### Generating Reports
1. From the main dashboard, click on "Generate Reports"
2. Select the report type you wish to generate
3. Choose filtering options if applicable
4. Click "Generate" to create the report

### View and Export Options
- **View Online**: Reports are displayed directly in your browser
- **Export to PDF**: Click the "Export to PDF" button to download a PDF version
- **Print**: Use the "Print" button to print the report directly

### Chart Generation
1. Select the data you wish to visualize
2. Choose the chart type (bar, pie, line)
3. Customize chart options if needed
4. Click "Generate Chart" to create and display the visualization

## Features
- Student information management
- Course and enrollment tracking
- Grade recording and GPA calculation
- Visual data representation through charts
- PDF report generation
- Data filtering and sorting capabilities

## Troubleshooting

### Common Issues
1. **Database Connection Errors**
   - Verify database credentials in `config.php`
   - Ensure MySQL service is running

2. **Chart Generation Issues**
   - Confirm GD Library is enabled in PHP
   - Check write permissions for the charts directory

3. **PDF Generation Problems**
   - Verify proper configuration of PDF generation libraries
   - Ensure temp directory has appropriate permissions

### Error Reporting
If you encounter errors, check the PHP error logs or enable debugging mode by adding the following to the top of `index.php`:
```php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

## Support
For additional support or to report issues, please contact the system administrator.

---
© 2025 Student Database Management System

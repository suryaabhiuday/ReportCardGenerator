# Report Card Generator

A web-based application for managing student records, marks, and generating report cards. Built with PHP, MySQL, and modern web technologies.

## Features

- Admin and Student user roles
- Student management (add, edit, delete)
- Subject management
- Marks management
- Excel import functionality for bulk data entry
- PDF report card generation
- Secure authentication system

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP (or similar local development environment)
- Composer (for dependency management)

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/ReportCardGenerator.git
   cd ReportCardGenerator
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Create a MySQL database and import the schema:
   - Open phpMyAdmin
   - Create a new database named `report_card_generator`
   - Import the `database.sql` file

4. Configure your web server:
   - Point your web server to the project directory
   - Ensure the web server has write permissions for the uploads directory

5. Access the application:
   - Open your browser and navigate to `http://localhost/ReportCardGenerator`
   - Default admin credentials:
     - Username: admin
     - Password: admin123

## Usage

### Admin Features

1. **Student Management**
   - Add new students
   - Edit student details
   - Delete students
   - Bulk import students via Excel

2. **Subject Management**
   - Add new subjects
   - Edit subject details
   - Delete subjects
   - Bulk import subjects via Excel

3. **Marks Management**
   - Add marks for students
   - Edit marks
   - Delete marks
   - Bulk import marks via Excel

4. **Report Card Generation**
   - Generate PDF report cards for students
   - View and download report cards

### Student Features

1. **View Marks**
   - View marks for all subjects
   - View marks by semester

2. **Download Report Card**
   - Download PDF report card

## Excel Import

The system supports bulk data import through Excel files. To use this feature:

1. Download the template from the admin dashboard
2. Fill in your data following the template format
3. Upload the Excel file through the import interface
4. Select which data to import (students, subjects, marks)
5. Review the import results

## Security

- Password hashing using PHP's password_hash()
- Session-based authentication
- Input validation and sanitization
- SQL injection prevention using prepared statements
- XSS prevention through output escaping

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## Acknowledgments

- [TCPDF](https://github.com/tecnickcom/TCPDF) for PDF generation
- [Bootstrap](https://getbootstrap.com/) for the frontend framework
- [Bootstrap Icons](https://icons.getbootstrap.com/) for icons 
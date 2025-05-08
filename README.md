# Report Card Generator

A web-based application for generating and managing student report cards. Built with PHP, MySQL, and TCPDF.

## Features

- User Authentication (Admin and Student roles)
- Student Management
- Subject Management
- Marks Management
- Report Card Generation (PDF)
- Responsive Design

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

3. Create a MySQL database named `report_card_generator`

4. Import the database schema:
   - Open phpMyAdmin
   - Select the `report_card_generator` database
   - Import the `database.sql` file

5. Configure the database connection:
   - Copy `config/database.example.php` to `config/database.php`
   - Update the database credentials in `config/database.php`

6. Start your local server (XAMPP)

## Usage

1. Access the application at `http://localhost/ReportCardGenerator`

2. Default admin credentials:
   - Username: admin
   - Password: admin123

3. Admin Features:
   - Add/Edit/Delete Students
   - Add/Edit/Delete Subjects
   - Add/Edit/Delete Marks
   - View all student records

4. Student Features:
   - View personal marks
   - Download report cards
   - View academic performance

## Security

- Passwords are hashed using PHP's password_hash()
- Session-based authentication
- Input validation and sanitization
- SQL injection prevention using prepared statements

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- [TCPDF](https://github.com/tecnickcom/TCPDF) for PDF generation
- [Bootstrap](https://getbootstrap.com/) for the frontend framework
- [Bootstrap Icons](https://icons.getbootstrap.com/) for icons 
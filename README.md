# Support Portal V3

## Overview

Support Portal V3 is a web-based support ticket management system developed using **PHP**, **MySQL**, **HTML**, **CSS**, and **JavaScript**. It enables users to submit support requests while allowing technicians and administrators to manage, assign, and resolve tickets efficiently.

This project was developed as part of the Diploma in Information and Communication Technology coursework at Daystar University.

---

## Features

* User registration and login
* Secure authentication
* User dashboard
* Create support tickets
* View ticket history
* Technician dashboard
* Update ticket status
* Admin dashboard
* User management
* Knowledge Base
* Responsive user interface

---

## Technologies Used

* PHP
* MySQL
* HTML5
* CSS3
* JavaScript
* Bootstrap
* XAMPP
* phpMyAdmin

---

## Installation

1. Install XAMPP.

2. Copy the project folder into:

   ```
   C:\xampp\htdocs\
   ```

3. Start **Apache** and **MySQL** from the XAMPP Control Panel.

4. Open phpMyAdmin and create a database named:

   ```
   support_portal
   ```

5. Import the project's SQL database file.

6. Open your browser and navigate to:

   ```
   http://localhost/support_portal_v3/
   ```

---

## Project Structure

```
support_portal_v3/
│
├── admin/
├── technician/
├── user/
├── knowledge/
├── config/
├── css/
├── images/
├── includes/
├── uploads/
├── index.php
├── login.php
├── register.php
├── logout.php
└── README.md
```

---

## User Roles

### User

* Register an account
* Log in
* Submit support tickets
* View ticket status

### Technician

* View assigned tickets
* Update ticket status
* Resolve support requests

### Administrator

* Manage users
* Manage technicians
* Monitor all tickets
* Access system reports

---

## Database

Database Name:

```
support_portal
```

Ensure your database connection settings in `config/db.php` match your local XAMPP configuration.

---

## Future Improvements

* Email notifications
* File attachments
* Live chat support
* Advanced reporting dashboard
* Ticket prioritization
* Search and filtering
* Two-factor authentication

---

## Author

**Damian Ng'aNg'a Rugunya**

Admission Number: **24-2753**

**Daystar University**

Diploma in Information and Communication Technology

---

## License

This project is intended for educational purposes as part of coursework at Daystar University.

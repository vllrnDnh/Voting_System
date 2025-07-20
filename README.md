# TECHVote - Voting System

A comprehensive voting system for educational institutions built with PHP and MySQL.

## Project Structure

```text
Voting_System/
├── admin/                      # Admin-specific functionality
│   ├── admin_dashboard.php     # Admin dashboard
│   ├── assign_candidates.php   # Candidate assignment logic
│   ├── candidate_assignment_ui.php # UI for candidate assignment
│   ├── toggle_org_visibility.php # Organization visibility controls
│   └── view_assigned_candidates.php # View assigned candidates
├── assets/                     # Static assets
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   ├── js/
│   │   └── candidate_assignment.js # Candidate assignment functionality
│   └── images/
│       └── FEU-IT.jpg         # Institution logo
├── database/                   # Database related files (reserved for future use)
├── docs/                      # Documentation and notes
│   ├── Notes.txt              # Development notes
│   ├── admin dashboard (KIER).txt # Admin dashboard documentation
│   ├── user dashboard (KIER).txt # User dashboard documentation
│   └── style.css (KIER).txt   # Style documentation
├── includes/                   # Shared PHP includes
│   ├── db.php                 # Database connection
│   └── session_security.php   # Session security functions
├── uploads/                    # User uploaded files
│   └── org_logos/             # Organization logos
├── dashboard.php              # Main dashboard redirect
├── fetch_members.php          # Fetch organization members
├── get_org_members.php        # Get organization members API
├── get_position_ui.php        # Position UI components
├── login.php                  # User login
├── logout.php                 # User logout
├── org_gallery.php            # Organization gallery
├── process_register.php       # Registration processing
├── register.php               # User registration
├── upload_profile.php         # Profile upload handler
├── user_dashboard.php         # User dashboard
└── vote.php                   # Voting interface
```

## Features

- **User Management**: Registration, login, profile management
- **Admin Panel**: Organization management, candidate assignment
- **Voting System**: Secure voting interface
- **Organization Management**: Gallery view, member management
- **Profile Management**: Image uploads, user information

## Setup Instructions

1. Place files in your web server directory (e.g., `c:\xampp\htdocs\Voting_System`)
2. Import your existing database schema to MySQL/MariaDB
3. Configure database connection in `includes/db.php`
4. Ensure proper file permissions for the `uploads/` directory

## File Organization

- **PHP Files**: Main application logic in root directory
- **Admin Files**: Administrative functions in `admin/` folder
- **Assets**: CSS, JS, and images in `assets/` folder
- **Database**: Reserved for future database-related files
- **Documentation**: Notes and documentation in `docs/` folder
- **Includes**: Shared PHP includes in `includes/` folder

## Security Features

- Session management
- CSRF token validation
- Input sanitization
- File upload validation
- Role-based access control

## Technologies Used

- PHP 7.4+
- MySQL/MariaDB
- HTML5
- CSS3
- JavaScript
- Bootstrap Icons

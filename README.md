# Enaara Management System

A comprehensive Human Resource Management System (HRMS) designed for multi-tenant organizations to manage employees, attendance, leaves, shifts, geofencing, and organizational hierarchy.

## 📋 Table of Contents

- [Project Overview](#project-overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Installation & Setup](#installation--setup)
- [System Architecture](#system-architecture)
- [Module Details](#module-details)
- [Current System Flow](#current-system-flow)
- [Project Structure](#project-structure)
- [Development Status](#development-status)
- [Contributing](#contributing)

## 🎯 Project Overview

**Enaara Management System** is a Laravel-based HRMS application built for Enaara Developers and its child organizations (Madison Square Mall Rawalpindi, Madison Square Mall Lahore, and Royal Swiss Lahore). The system provides a centralized platform for managing all aspects of human resources, from employee onboarding to attendance tracking, leave management, and organizational structure.

### Key Objectives

- **Multi-Tenant Support**: Manage multiple organizations under a parent organization
- **Comprehensive Attendance Management**: Track daily logs, shifts, and attendance patterns
- **Leave Management**: Complete leave request workflow with approval chains
- **Geofencing**: Location-based attendance tracking and compliance monitoring
- **Shift Planning**: Advanced shift scheduling and roster management
- **Regularization**: Handle attendance discrepancies and exceptions
- **Real-time Dashboard**: Comprehensive analytics and insights

## ✨ Features

### 1. **Dashboard**
- Real-time attendance overview with charts (7, 14, 28 days)
- Department-wise attendance statistics
- Pending approvals and exceptions monitoring
- Geofence compliance tracking
- Quick stats and system alerts
- "Who is Out Today" widget
- Workforce strength visualization

### 2. **Organization Management**
- Multi-level organization hierarchy (Parent → Child organizations)
- Organization cards with admin assignment
- Bulk policy management
- Organization detail views with employee counts
- Admin/HR manager assignment per organization

### 3. **Department Management**
- Department creation and management
- Department head assignment
- Employee transfer between departments
- Department-wise leave policies
- Bulk policy application

### 4. **User & Employee Management**
- User account management
- Employee profiles with detailed information
- Employee ID generation (EMP-001, EMP-002, etc.)
- Role-based access control
- Employee search and filtering

### 5. **Daily Logs**
- Daily attendance tracking
- Check-in/Check-out logs
- Attendance history
- Exception tracking (late arrivals, early departures)
- Detailed log views with timestamps

### 6. **Shift Planner**
- Shift creation and management
- Roster calendar view
- Bulk shift assignment
- Shift conflict detection
- Multiple shift types (Morning, Evening, Night, Weekend)
- Employee shift assignment

### 7. **Regularization**
- Attendance discrepancy handling
- Regularization request workflow
- Evidence attachment support
- Multiple regularization categories:
  - Missed Punch
  - On-Duty (Outside)
  - Technical Error
  - Late Regularization
- Approval workflow with audit trail
- Request cards and table views

### 8. **Geofencing**
- Interactive map-based geofence creation
- Multiple geofence zones per location
- Radius-based boundaries (meters)
- Hard-lock and Soft-lock fence types
- Real-time employee location tracking
- In-zone/Out-zone status monitoring
- VPN/Proxy detection
- Fence violation tracking

### 9. **Leave Management**

#### Leave Requests
- Multiple leave types:
  - Annual Leave
  - Sick Leave
  - Casual Leave
  - Compensatory Off
  - Emergency Leave
  - Personal Leave
  - Maternity Leave
- Multi-level approval workflow:
  - Supervisor → HR/Dept Head → Super Admin
- Leave balance tracking
- Medical certificate upload (for sick leave)
- Leave request detail views
- Bulk approval functionality
- Away today tracking

#### My Leaves
- Personal leave dashboard
- Quick leave request creation
- Leave timeline visualization
- Proxy assignment for approvals during leave
- Upcoming holidays display
- Leave balance overview

#### Leave Calendar
- Department-wise leave calendar
- Holiday management
- Blackout date configuration
- Impact level indicators (Low, Medium, High, Critical)
- Event detail views
- Calendar legend and filters

#### Balance Tracker
- Leave balance tracking per employee
- Annual, Sick, and Casual leave balances
- Balance adjustment functionality
- Join date-based balance calculation
- Organization-wise balance reports

### 10. **Authentication & Authorization**
- User login/registration
- Session management
- Role-based access control
- Password hashing and security

## 🛠 Technology Stack

### Backend
- **Framework**: Laravel 12.0
- **PHP**: 8.2+
- **Database**: SQLite (development) / MySQL (production ready)
- **Authentication**: Laravel Auth

### Frontend
- **CSS Framework**: Bootstrap 5.3.8
- **JavaScript**: Vanilla JS with ES6+
- **Build Tool**: Vite 7.0.7
- **Icons**: Bootstrap Icons
- **Charts**: Chart.js (via custom implementation)
- **Maps**: Leaflet.js (for geofencing)

### Development Tools
- **Package Manager**: Composer, NPM
- **Code Quality**: Laravel Pint
- **Testing**: PHPUnit
- **Logging**: Laravel Pail

## 📦 Installation & Setup

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js and NPM
- SQLite (for development) or MySQL/PostgreSQL (for production)

### Step 1: Clone the Repository

```bash
git clone <repository-url>
cd enaara-management
```

### Step 2: Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### Step 3: Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

Update `.env` file with your database configuration:

```env
DB_CONNECTION=sqlite
# OR for MySQL
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=enaara_management
# DB_USERNAME=root
# DB_PASSWORD=
```

### Step 4: Database Setup

```bash
# Create SQLite database (if using SQLite)
touch database/database.sqlite

# Run migrations
php artisan migrate
```

### Step 5: Build Assets

```bash
# Development build
npm run dev

# Production build
npm run build
```

### Step 6: Start Development Server

```bash
# Using Laravel's built-in server
php artisan serve

# OR using the dev script (includes queue, logs, and vite)
composer run dev
```

The application will be available at `http://localhost:8000`

## 🏗 System Architecture

### Application Flow

```
User Login
    ↓
Dashboard (Overview)
    ↓
┌─────────────────────────────────────────┐
│  Multi-Module Access                    │
├─────────────────────────────────────────┤
│  • Organization Management              │
│  • Department Management                │
│  • Employee Management                  │
│  • Daily Logs (Attendance)              │
│  • Shift Planner                        │
│  • Regularization                       │
│  • Geofencing                           │
│  • Leave Management                     │
│    - Leave Requests                     │
│    - My Leaves                          │
│    - Leave Calendar                     │
│    - Balance Tracker                    │
└─────────────────────────────────────────┘
```

### Multi-Tenant Structure

```
Enaara Developers (Parent Organization)
├── Madison Square Mall Rawalpindi
├── Madison Square Mall Lahore
└── Royal Swiss Lahore
```

Each organization can have:
- Multiple departments
- Multiple employees
- Independent leave policies
- Separate geofence zones
- Custom shift schedules

## 📱 Module Details

### 1. Dashboard Module

**Location**: `resources/views/admin/dashboard/`

**Components**:
- `index.blade.php` - Main dashboard layout
- `counters.blade.php` - Key metric counters
- `attendance-overview.blade.php` - Attendance charts
- `department-chart.blade.php` - Department-wise statistics
- `pending-approvals.blade.php` - Pending leave approvals
- `exceptions-table.blade.php` - Attendance exceptions
- `regularization-pending.blade.php` - Pending regularizations
- `who-is-out.blade.php` - Employees on leave
- `geofence-compliance.blade.php` - Geofence status
- `system-alerts.blade.php` - System notifications

**Data Source**: `public/js/dummy-data.js` → `dashboard` object

### 2. Organization Module

**Location**: `resources/views/admin/organization/`

**Features**:
- Organization cards display
- Add/Edit organization
- Admin assignment
- Bulk policy management
- Organization hierarchy visualization

**Routes**:
- `GET /admin/organization` - List all organizations

### 3. Department Module

**Location**: `resources/views/admin/departments/`

**Features**:
- Department cards with employee counts
- Department head assignment
- Employee transfer between departments
- Department detail views
- Bulk policy application

**Routes**:
- `GET /admin/departments` - List all departments

### 4. Employee Module

**Location**: `resources/views/admin/employee/`

**Features**:
- Employee table with search/filter
- Employee detail views
- Employee profile management

**Routes**:
- `GET /admin/employee` - List all employees

### 5. Daily Logs Module

**Location**: `resources/views/admin/daily-logs/`

**Features**:
- Daily attendance logs
- Check-in/Check-out timestamps
- Exception tracking
- Log detail views

**Routes**:
- `GET /admin/daily-logs` - View daily logs

### 6. Shift Planner Module

**Location**: `resources/views/admin/shift-planner/`

**Features**:
- Shift creation and management
- Roster calendar view
- Bulk shift assignment
- Shift conflict detection
- Multiple shift types

**Routes**:
- `GET /admin/shift-planner` - Shift planner interface

### 7. Regularization Module

**Location**: `resources/views/admin/regularization/`

**Features**:
- Regularization request management
- Evidence attachment
- Approval workflow
- Audit trail
- Request categorization

**Routes**:
- `GET /admin/regularization` - Regularization requests

### 8. Geofencing Module

**Location**: `resources/views/admin/geofencing/`

**Features**:
- Interactive map interface
- Geofence creation (radius-based)
- Hard-lock and Soft-lock types
- Employee location tracking
- Violation monitoring

**Routes**:
- `GET /admin/geofencing` - Geofencing management

### 9. Leave Management Modules

#### Leave Requests
**Location**: `resources/views/admin/leave-requests/`

**Features**:
- Leave request creation
- Multi-level approval workflow
- Leave balance checking
- Medical certificate upload
- Bulk approval

**Routes**:
- `GET /admin/leave-requests` - Leave requests list

#### My Leaves
**Location**: `resources/views/admin/my-leaves/`

**Features**:
- Personal leave dashboard
- Quick leave request
- Leave timeline
- Proxy assignment
- Upcoming holidays

**Routes**:
- `GET /admin/my-leaves` - Personal leaves

#### Leave Calendar
**Location**: `resources/views/admin/leave-calendar/`

**Features**:
- Department-wise calendar
- Holiday management
- Blackout dates
- Impact level indicators

**Routes**:
- `GET /admin/leave-calendar` - Leave calendar

#### Balance Tracker
**Location**: `resources/views/admin/balance-tracker/`

**Features**:
- Leave balance per employee
- Balance adjustment
- Balance history

**Routes**:
- `GET /admin/balance-tracker` - Balance tracker

## 🔄 Current System Flow

### Authentication Flow

```
1. User visits root URL (/)
   ↓
2. Redirected to Dashboard (or Login if not authenticated)
   ↓
3. User logs in via AuthController
   ↓
4. Session created, redirected to /admin/dashboard
```

### Leave Request Flow

```
1. Employee creates leave request
   ↓
2. System checks leave balance
   ↓
3. Request submitted to Supervisor
   ↓
4. Supervisor approves/rejects
   ↓
5. If approved → Forwarded to HR/Dept Head
   ↓
6. HR/Dept Head approves/rejects
   ↓
7. If required → Forwarded to Super Admin
   ↓
8. Final approval → Leave granted, balance updated
```

### Regularization Flow

```
1. Employee notices attendance discrepancy
   ↓
2. Employee submits regularization request
   ↓
3. Evidence attached (if applicable)
   ↓
4. Request categorized (Missed Punch, On-Duty, etc.)
   ↓
5. Supervisor reviews
   ↓
6. HR/Admin reviews
   ↓
7. Super Admin final approval (if required)
   ↓
8. Attendance record updated
```

### Geofencing Flow

```
1. Admin creates geofence zone on map
   ↓
2. Zone assigned to specific groups/departments
   ↓
3. Employee check-in attempts
   ↓
4. System checks GPS location
   ↓
5. If in-zone → Check-in allowed
   ↓
6. If out-zone → Check-in blocked (hard-lock) or warned (soft-lock)
   ↓
7. Violations logged and notified
```

### Shift Assignment Flow

```
1. Admin creates shift schedule
   ↓
2. Shift assigned to employees
   ↓
3. System checks for conflicts
   ↓
4. Conflicts flagged for resolution
   ↓
5. Roster published
   ↓
6. Employees notified
```

## 📁 Project Structure

```
enaara-management/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── AuthController.php
│   ├── Models/
│   │   └── User.php
│   └── Providers/
│       └── AppServiceProvider.php
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
│   ├── css/              # Compiled CSS files
│   ├── js/               # JavaScript files
│   │   ├── app.js
│   │   ├── dashboard.js
│   │   ├── dummy-data.js # Centralized dummy data
│   │   └── helpers.js
│   └── images/
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   ├── app.js
│   │   └── bootstrap.js
│   └── views/
│       ├── admin/        # Admin panel views
│       │   ├── dashboard/
│       │   ├── organization/
│       │   ├── departments/
│       │   ├── employee/
│       │   ├── daily-logs/
│       │   ├── shift-planner/
│       │   ├── regularization/
│       │   ├── geofencing/
│       │   ├── leave-requests/
│       │   ├── my-leaves/
│       │   ├── leave-calendar/
│       │   ├── balance-tracker/
│       │   └── users/
│       ├── auth/         # Authentication views
│       └── layouts/      # Layout templates
│           └── partials/
│               ├── header.blade.php
│               ├── sidebar.blade.php
│               └── footer.blade.php
├── routes/
│   └── web.php           # Web routes
├── storage/
├── tests/
├── vendor/
├── .env
├── .env.example
├── composer.json
├── package.json
├── vite.config.js
└── README.md
```

## 🚀 Development Status

### ✅ Completed Features

- [x] Authentication system (Login/Register/Logout)
- [x] Dashboard with real-time statistics
- [x] Organization management (CRUD)
- [x] Department management (CRUD)
- [x] Employee management interface
- [x] Daily logs tracking interface
- [x] Shift planner with calendar view
- [x] Regularization request system
- [x] Geofencing with map integration
- [x] Leave request management
- [x] Leave calendar with holiday management
- [x] Balance tracker
- [x] Multi-tenant organization structure
- [x] Responsive UI with Bootstrap 5
- [x] Centralized dummy data system
- [x] Pakistani name localization

### 🚧 In Progress / Planned

- [ ] Backend API integration
- [ ] Database models and relationships
- [ ] Real-time notifications
- [ ] Email notifications
- [ ] Report generation
- [ ] Advanced analytics
- [ ] Mobile app integration
- [ ] Biometric device integration
- [ ] API documentation
- [ ] Unit and feature tests

### 📝 Notes

- Currently using dummy data from `public/js/dummy-data.js`
- All views are frontend-only (no backend API calls yet)
- Database migrations are basic (users table only)
- Authentication is functional but needs role-based access control
- Geofencing uses Leaflet.js for map rendering
- All employee names have been localized to Pakistani names

## 👥 Sample Data

The system includes comprehensive dummy data with Pakistani names:

- **Organizations**: Enaara Developers (Parent), Madison Square Mall Rawalpindi, Madison Square Mall Lahore, Royal Swiss Lahore
- **Employees**: Ahmed Ali, Zainab Malik, Bilal Ahmed, Hira Ali, Hamza Khan, Sana Sheikh, etc.
- **Departments**: Sales, HR, IT, Operations, Finance, Security
- **Sample Leave Requests**: Various leave types with different statuses
- **Geofence Zones**: Multiple zones in Rawalpindi and Lahore

## 🔐 Security Considerations

- Password hashing using Laravel's Hash facade
- CSRF protection on forms
- Session management
- Input validation (to be implemented)
- SQL injection prevention (Laravel Eloquent)
- XSS protection (Blade templating)

## 🧪 Testing

```bash
# Run tests
php artisan test

# Or using PHPUnit directly
vendor/bin/phpunit
```

## 📝 Code Style

The project uses Laravel Pint for code formatting:

```bash
# Format code
./vendor/bin/pint
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is proprietary software developed for Enaara Developers.

## 📞 Contact & Support

For questions or support, please contact the development team.

---

**Last Updated**: December 2024
**Version**: 1.0.0
**Status**: Development

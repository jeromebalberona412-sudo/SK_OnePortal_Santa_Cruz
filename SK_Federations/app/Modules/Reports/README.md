# Reports Module

## Overview
The Reports module provides comprehensive reporting and monitoring of barangay compliance, accomplishment submissions, and ABYIP reports.

## Features
- **Time-based Filtering**: Filter reports by week, month, year, or all time
- **Summary Cards**: Display key metrics including:
  - Barangay Compliance Rate
  - Accomplishment Submission Rate
  - ABYIP Submission Rate
- **Two Report Tabs**:
  - Accomplishment Reports
  - ABYIP Reports
- **Status Filtering**: Filter barangay cards by compliance status (Compliant, Partial, Non-Compliant)
- **Barangay Overview Cards**: Display detailed information for each barangay including:
  - Barangay name
  - SK Chairman
  - Report submission status
  - Date submitted
  - Compliance status badge

## Installation
1. The module is automatically loaded via the service provider
2. Routes are registered in `Routes/web.php`
3. Views are loaded from `Views/` directory
4. Assets are published to `public/modules/reports/`

## Usage
Access the Reports module via the sidebar menu or navigate to `/reports`

## File Structure
```
Reports/
├── Controllers/
│   └── ReportsController.php
├── Providers/
│   └── ReportsServiceProvider.php
├── Routes/
│   └── web.php
├── Views/
│   └── index.blade.php
├── assets/
│   └── css/
│       └── reports.css
├── module.json
└── README.md
```

## API Endpoints
- `GET /reports` - Display reports dashboard

## Future Enhancements
- Export reports to PDF/Excel
- Advanced filtering and search
- Report generation and scheduling
- Email notifications for submissions

# Programs Module

## Overview

The Programs module handles all program-related functionality for the Kabataan application. It provides APIs for fetching program categories, viewing programs by category, and submitting program applications.

## Features

- **Program Categories**: 8 predefined program categories (Education, Anti-Drugs, Agriculture, Disaster Preparedness, Sports Development, Gender and Development, Health, Others)
- **Dynamic Program Fetching**: Fetch programs by category via API
- **Application Submission**: Submit program applications with validation
- **Modal Management**: Integrated with Dashboard modals for displaying programs
- **Responsive Design**: Works seamlessly with mobile and desktop interfaces

## File Structure

```
Kabataan/app/Modules/Programs/
├── Controllers/
│   └── ProgramController.php      # Main controller for program operations
├── Routes/
│   └── web.php                    # API routes for programs
├── Providers/
│   └── ProgramServiceProvider.php # Service provider for module registration
├── assets/
│   └── js/
│       └── programs.js            # Frontend JavaScript for program interactions
└── README.md                       # This file
```

## API Endpoints

All endpoints require authentication (`auth` middleware).

### Get All Program Categories

```
GET /api/programs/categories
```

**Response:**
```json
[
  {
    "id": "education",
    "name": "Education",
    "icon": "book",
    "color": "#2196F3",
    "activeCount": 1,
    "programs": [...]
  },
  ...
]
```

### Get Programs by Category

```
GET /api/programs/category/{categoryId}
```

**Parameters:**
- `categoryId` (string): Category ID (e.g., "education", "anti-drugs")

**Response:**
```json
{
  "id": "education",
  "name": "Education",
  "icon": "book",
  "color": "#2196F3",
  "activeCount": 1,
  "programs": [
    {
      "id": 1,
      "name": "Scholarship Assistance Program",
      "description": "Financial assistance for deserving students...",
      "deadline": "2026-03-31",
      "slots": 50,
      "status": "active"
    }
  ]
}
```

### Get Single Program

```
GET /api/programs/{programId}
```

**Parameters:**
- `programId` (integer): Program ID

**Response:**
```json
{
  "id": 1,
  "name": "Scholarship Assistance Program",
  "description": "Financial assistance for deserving students...",
  "deadline": "2026-03-31",
  "slots": 50,
  "status": "active"
}
```

### Submit Program Application

```
POST /api/programs/apply
```

**Request Body:**
```json
{
  "program_id": 1,
  "full_name": "Juan Dela Cruz",
  "email": "juan@example.com",
  "contact_number": "09123456789",
  "address": "House No. 1, Street, Barangay, Santa Cruz, Laguna",
  "school": "University of the Philippines",
  "course": "BS Computer Science",
  "year_level": "3",
  "gpa": "1.75",
  "parent_name": "Jose Dela Cruz",
  "occupation": "Farmer",
  "family_income": "10k-20k",
  "siblings": 2,
  "essay": "I deserve this scholarship because..."
}
```

**Response:**
```json
{
  "success": true,
  "message": "Application submitted successfully",
  "application_id": "APP_123456789"
}
```

## Program Categories

### 1. Education 🎓
- **Color**: #2196F3 (Blue)
- **Programs**: Scholarship Assistance Program
- **Status**: 1 active program

### 2. Anti-Drugs 🚫
- **Color**: #E91E63 (Pink)
- **Programs**: None currently
- **Status**: 0 active programs

### 3. Agriculture 🌱
- **Color**: #4CAF50 (Green)
- **Programs**: None currently
- **Status**: 0 active programs

### 4. Disaster Preparedness 🚩
- **Color**: #FF9800 (Orange)
- **Programs**: None currently
- **Status**: 0 active programs

### 5. Sports Development ⚽
- **Color**: #00BCD4 (Cyan)
- **Programs**: None currently
- **Status**: 0 active programs

### 6. Gender and Development 👥
- **Color**: #9C27B0 (Purple)
- **Programs**: None currently
- **Status**: 0 active programs

### 7. Health ❤️
- **Color**: #F44336 (Red)
- **Programs**: None currently
- **Status**: 0 active programs

### 8. Others ⚡
- **Color**: #607D8B (Slate)
- **Programs**: None currently
- **Status**: 0 active programs

## Frontend Integration

### JavaScript Module

The `programs.js` file provides a `window.programsModule` object with the following methods:

#### `fetchCategories()`
Fetches all program categories from the API.

```javascript
const categories = await window.programsModule.fetchCategories();
```

#### `fetchByCategory(categoryId)`
Fetches programs for a specific category.

```javascript
const category = await window.programsModule.fetchByCategory('education');
```

#### `fetchProgram(programId)`
Fetches a single program by ID.

```javascript
const program = await window.programsModule.fetchProgram(1);
```

#### `submitApplication(formData)`
Submits a program application.

```javascript
const result = await window.programsModule.submitApplication({
  program_id: 1,
  full_name: 'Juan Dela Cruz',
  // ... other fields
});
```

#### `openCategoryModal(categoryId)`
Opens the appropriate modal for a category (handles both education and generic categories).

```javascript
window.programsModule.openCategoryModal('education');
```

#### `openApplicationForm(programId)`
Opens the scholarship application form for a specific program.

```javascript
window.programsModule.openApplicationForm(1);
```

#### `submitScholarshipApplication(formElement)`
Submits the scholarship application form and shows success modal.

```javascript
window.programsModule.submitScholarshipApplication(formElement);
```

### Dashboard Integration

The Programs module is automatically integrated with the Dashboard module:

1. **Program Category Clicks**: Clicking a program category in the sidebar triggers `window.programsModule.openCategoryModal()`
2. **Apply Buttons**: Clicking "Apply Now" opens the scholarship application form
3. **Form Submission**: Submitting the form calls `window.programsModule.submitScholarshipApplication()`

## Usage Example

### In Blade Template

```blade
<!-- Program categories are already in the dashboard -->
<div class="program-categories">
    <div class="program-category" data-category="education">
        <!-- Category content -->
    </div>
</div>

<!-- Include the programs JavaScript -->
@vite(['app/Modules/Programs/assets/js/programs.js'])
```

### In JavaScript

```javascript
// Open education programs modal
window.programsModule.openCategoryModal('education');

// Submit application
const formData = {
  program_id: 1,
  full_name: 'Juan Dela Cruz',
  email: 'juan@example.com',
  // ... other fields
};

const result = await window.programsModule.submitApplication(formData);
if (result.success) {
  console.log('Application submitted:', result.application_id);
}
```

## Configuration

### Service Provider Registration

The module is registered in `Kabataan/bootstrap/providers.php`:

```php
App\Modules\Programs\Providers\ProgramServiceProvider::class,
```

### Vite Configuration

The programs JavaScript is included in `Kabataan/vite.config.js`:

```javascript
'app/Modules/Programs/assets/js/programs.js',
```

## Future Enhancements

### Database Integration
- [ ] Create `programs` table
- [ ] Create `program_categories` table
- [ ] Create `program_applications` table
- [ ] Create `Program` and `ProgramApplication` models

### Features
- [ ] Real database queries instead of hardcoded data
- [ ] Program filtering by barangay
- [ ] Application status tracking
- [ ] Email notifications for applications
- [ ] Application review dashboard
- [ ] Program schedule management
- [ ] Participant list management
- [ ] Certificate generation

### API Enhancements
- [ ] Pagination for programs
- [ ] Search functionality
- [ ] Sorting and filtering
- [ ] Application history
- [ ] Application status updates

### Frontend Enhancements
- [ ] Generic category modal for non-education programs
- [ ] Program details page
- [ ] Application tracking page
- [ ] Program calendar view
- [ ] Program recommendations

## Testing

### Manual Testing Checklist

- [ ] Fetch categories API returns all 8 categories
- [ ] Fetch category API returns education programs
- [ ] Fetch category API returns 404 for invalid category
- [ ] Clicking program category opens correct modal
- [ ] Education category shows scholarship program
- [ ] Other categories show "No Programs Available" modal
- [ ] Apply button opens scholarship form
- [ ] Form submission sends data to API
- [ ] Success modal appears after submission
- [ ] Form resets after submission

### API Testing

```bash
# Get all categories
curl http://localhost:8000/api/programs/categories

# Get education programs
curl http://localhost:8000/api/programs/category/education

# Get single program
curl http://localhost:8000/api/programs/1

# Submit application
curl -X POST http://localhost:8000/api/programs/apply \
  -H "Content-Type: application/json" \
  -d '{
    "program_id": 1,
    "full_name": "Juan Dela Cruz",
    ...
  }'
```

## Troubleshooting

### Module Not Loading
- Ensure `ProgramServiceProvider` is registered in `bootstrap/providers.php`
- Check that routes are properly loaded
- Verify Vite configuration includes programs JavaScript

### API Endpoints Not Working
- Ensure user is authenticated (routes require `auth` middleware)
- Check CSRF token is included in POST requests
- Verify request headers include `Content-Type: application/json`

### JavaScript Errors
- Ensure `programs.js` is loaded before dashboard JavaScript
- Check browser console for specific error messages
- Verify `window.programsModule` is accessible

## Support

For issues or questions about the Programs module, please refer to the main project documentation or contact the development team.

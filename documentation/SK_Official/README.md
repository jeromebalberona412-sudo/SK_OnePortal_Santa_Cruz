# Schedule Programs Module

## 📁 Organized Structure

This module is organized by committee for better maintainability and scalability.

### Committee Folders

| Folder | Committee Name | Description |
|--------|---------------|-------------|
| `scholarship/` | Equitable Access to Quality Education | Scholarship programs and scholar management |
| `sports/` | Sports Development | Sports programs and athlete management |
| `environmental/` | Environmental Protection | Environmental programs |
| `disaster/` | Disaster Risk Reduction and Resiliency | Disaster preparedness programs |
| `livelihood/` | Youth Employment and Livelihood | Livelihood and employment programs |
| `medicines/` | Medicines | Medicine distribution programs |
| `antidrug/` | Anti-Drug and Peace and Order | Anti-drug campaigns |
| `gender/` | Gender Sensitivity | Gender equality programs |
| `feeding/` | Feeding Program for KK Members | Feeding programs |
| `others/` | Others | Other programs |

### Folder Structure

```
schedule_programs/
├── views/
│   ├── {committee}/
│   │   ├── schedule.blade.php      (Program scheduling)
│   │   ├── requests.blade.php      (Application requests)
│   │   ├── list.blade.php          (Approved list)
│   │   ├── evaluation.blade.php    (Evaluation)
│   │   └── partials/               (Committee-specific partials)
│   ├── partials/                   (Shared partials)
│   └── schedule-programs.blade.php (Main dashboard)
├── assets/
│   ├── css/
│   │   ├── {committee}/            (Committee-specific CSS)
│   │   ├── shared/                 (Shared CSS)
│   │   └── schedule-programs.css   (Main CSS)
│   └── js/
│       ├── {committee}/            (Committee-specific JS)
│       ├── shared/                 (Shared JS)
│       └── schedule-programs.js    (Main JS)
├── Controllers/
└── routes/
```

## 🚀 Quick Start

### Adding a New Committee

1. Create folder structure:
```bash
mkdir views/{committee}
mkdir assets/css/{committee}
mkdir assets/js/{committee}
```

2. Create view files:
- `schedule.blade.php`
- `requests.blade.php`
- `list.blade.php`
- `evaluation.blade.php`

3. Add routes in `routes/web.php`:
```php
Route::get('/{committee}-schedule', function () {
    return view('schedule_programs::{committee}.schedule');
})->name('{committee}.schedule');
```

4. Add assets to `vite.config.js`

5. Build: `npm run build`

### Using Shared Components

#### Shared CSS
```php
@vite(['app/Modules/schedule_programs/assets/css/shared/sk-report-editor.css'])
```

#### Shared JS
```php
@vite(['app/Modules/schedule_programs/assets/js/shared/spfb-form-builder.js'])
```

#### Shared Partials
```php
@include('schedule_programs::partials.time-dropdown-fields')
```

## 📝 View Naming Convention

### Routes
```php
schedule_programs::{committee}.{page}
```

Examples:
- `schedule_programs::scholarship.schedule`
- `schedule_programs::sports.requests`
- `schedule_programs::environmental.list`

### Includes
```php
schedule_programs::{committee}.partials.{partial}
```

Examples:
- `schedule_programs::scholarship.partials.tabs`
- `schedule_programs::sports.partials.page-top`

## 🎨 Asset Paths

### CSS
```
app/Modules/schedule_programs/assets/css/{committee}/{file}.css
```

### JavaScript
```
app/Modules/schedule_programs/assets/js/{committee}/{file}.js
```

### Shared Assets
```
app/Modules/schedule_programs/assets/{css|js}/shared/{file}.{css|js}
```

## 🔧 Development

### Build Assets
```bash
npm run build
```

### Watch for Changes
```bash
npm run dev
```

### Clear Cache
```bash
php artisan view:clear
php artisan cache:clear
```

## 📚 Documentation

- `SCHEDULE_PROGRAMS_REORGANIZATION.md` - Detailed reorganization guide
- `REORGANIZATION_SUCCESS.md` - Completion report
- `REORGANIZATION_COMPLETE.md` - Implementation summary

## ✅ Testing

Test all routes after changes:
- Scholarship: `/scholar-application-form`, `/scholarship-application-request`, `/scholar-list`, `/scholar-evaluation`
- Sports: `/sports-application-form`, `/sports-requests`, `/sport_list`, `/sports-evaluation`
- Other committees: `/{committee}-schedule`, `/{committee}-requests`, etc.

## 🐛 Troubleshooting

### Build Errors
1. Check `vite.config.js` has correct paths
2. Verify files exist at specified paths
3. Run `npm run build` again

### View Not Found
1. Check route uses correct view path with dots
2. Verify file exists in correct folder
3. Clear view cache: `php artisan view:clear`

### Assets Not Loading
1. Run `npm run build`
2. Check `@vite` paths in blade files
3. Clear browser cache

## 📞 Support

For issues or questions:
1. Check documentation in `documentation/SK_Official/`
2. Review backup in `backup_[timestamp]/`
3. Contact development team

---

**Last Updated**: May 27, 2026  
**Structure Version**: 2.0  
**Status**: ✅ Production Ready

# Vehicle Management System

## Overview

The Vehicle Management system provides a fully dynamic and functional admin interface for managing vehicles in the ATV/UTV rental system. It features popup modals for create and edit operations, real-time status updates, and a modern, responsive design.

## Features

### 🚀 Dynamic Operations
- **Create Vehicles**: Add new vehicles through a popup modal
- **Edit Vehicles**: Modify existing vehicles with real-time form updates
- **Delete Vehicles**: Remove vehicles with confirmation dialog
- **Toggle Status**: Activate/deactivate vehicles with a single click

### 🎨 Modern UI/UX
- **Popup Modals**: Clean, modern modal design for all operations
- **Real-time Updates**: No page refreshes required
- **Image Upload**: Drag-and-drop image upload with preview
- **Form Validation**: Real-time validation with error messages
- **Loading States**: Visual feedback during operations
- **Responsive Design**: Works on desktop and mobile devices

### 🔧 Technical Features
- **AJAX Operations**: All CRUD operations use AJAX for seamless experience
- **CSRF Protection**: Secure form submissions with CSRF tokens
- **File Upload**: Secure image upload with validation
- **Error Handling**: Comprehensive error handling and user feedback
- **Database Integration**: Full integration with Laravel Eloquent models

## How to Use

### Accessing Vehicle Management
1. Navigate to the admin panel
2. Click on "Vehicle Management" in the sidebar
3. You'll see the main vehicle listing page

### Adding a New Vehicle
1. Click the "Add New Vehicle" button
2. Fill in the required fields:
   - **Vehicle Type**: Select from available vehicle types
   - **Vehicle Name**: Enter the vehicle name
   - **Details**: Optional description
   - **Operation Start Date**: When the vehicle becomes available
   - **Vehicle Image**: Upload an image (optional)
   - **Active Status**: Toggle to activate/deactivate
3. Click "Save Vehicle"

### Editing a Vehicle
1. Click the edit icon (pencil) next to any vehicle
2. The form will populate with current data
3. Make your changes
4. Click "Save Vehicle"

### Toggling Vehicle Status
1. Click the toggle icon (on/off switch) next to any vehicle
2. The status will update immediately without page refresh

### Deleting a Vehicle
1. Click the delete icon (trash) next to any vehicle
2. Confirm the deletion in the popup dialog
3. The vehicle will be removed from the list

## Database Schema

### Vehicles Table
```sql
vehicles
├── id (Primary Key)
├── vehicle_type_id (Foreign Key)
├── name (String)
├── details (Text, Nullable)
├── image_path (String, Nullable)
├── is_active (Boolean, Default: true)
├── op_start_date (Date, Nullable)
├── created_at (Timestamp)
└── updated_at (Timestamp)
```

### Vehicle Types Table
```sql
vehicle_types
├── id (Primary Key)
├── name (String)
├── description (Text, Nullable)
├── is_active (Boolean, Default: true)
├── created_at (Timestamp)
└── updated_at (Timestamp)
```

## API Endpoints

### Vehicle Management
- `GET /admin/vehicles` - List all vehicles
- `POST /admin/vehicles` - Create new vehicle
- `GET /admin/vehicles/{id}/edit` - Get vehicle for editing
- `PUT /admin/vehicles/{id}` - Update vehicle
- `DELETE /admin/vehicles/{id}` - Delete vehicle
- `PATCH /admin/vehicles/{id}/toggle` - Toggle vehicle status

### AJAX Responses
All endpoints return JSON responses for AJAX requests:

**Success Response:**
```json
{
    "success": true,
    "message": "Vehicle created successfully.",
    "vehicle": {
        "id": 1,
        "name": "Adventure Bike",
        "vehicle_type": {
            "id": 1,
            "name": "Regular"
        }
    }
}
```

**Error Response:**
```json
{
    "success": false,
    "errors": {
        "name": ["The name field is required."]
    }
}
```

## File Structure

```
resources/views/admin/
├── vehical-management.blade.php    # Main vehicle management view
└── layouts/
    └── admin.blade.php             # Admin layout with jQuery

app/Http/Controllers/Admin/
└── VehicleController.php           # Vehicle CRUD controller

app/Models/
├── Vehicle.php                     # Vehicle model
└── VehicleType.php                 # Vehicle type model

database/
├── migrations/
│   ├── create_vehicles_table.php
│   └── create_vehicle_types_table.php
└── seeders/
    └── VehicleSeeder.php           # Sample data seeder
```

## Styling

The system uses custom CSS with:
- **Bootstrap 5**: For responsive grid and components
- **Bootstrap Icons**: For action icons
- **Custom Gradients**: Orange theme matching the design
- **Smooth Animations**: CSS transitions for better UX
- **Modal Styling**: Custom modal design with rounded corners

## Browser Compatibility

- ✅ Chrome (Latest)
- ✅ Firefox (Latest)
- ✅ Safari (Latest)
- ✅ Edge (Latest)
- ✅ Mobile browsers

## Security Features

- **CSRF Protection**: All forms include CSRF tokens
- **File Upload Validation**: Image type and size validation
- **SQL Injection Prevention**: Uses Laravel Eloquent ORM
- **XSS Protection**: Laravel's built-in XSS protection
- **Authentication**: Admin-only access required

## Troubleshooting

### Common Issues

1. **Images not displaying**
   - Ensure storage link is created: `php artisan storage:link`
   - Check file permissions on storage directory

2. **AJAX requests failing**
   - Verify CSRF token is included in requests
   - Check browser console for JavaScript errors

3. **Form validation errors**
   - Ensure all required fields are filled
   - Check file upload size limits

### Debug Mode
Enable debug mode in `.env`:
```
APP_DEBUG=true
```

## Future Enhancements

- [ ] Bulk operations (import/export)
- [ ] Advanced filtering and search
- [ ] Vehicle availability calendar
- [ ] Maintenance tracking
- [ ] Vehicle history log
- [ ] Image gallery support
- [ ] Vehicle specifications management

## Support

For technical support or feature requests, please contact the development team.

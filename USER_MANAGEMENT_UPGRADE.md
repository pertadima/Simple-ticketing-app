# User Management System Upgrade

## Overview

The user management system has been successfully upgraded to support separate admin and API user management with role-based access control.

## New Structure

### 1. Database Tables

#### Admin Users (`admin_users`)
- **Purpose**: Admin panel access with role-based permissions
- **Primary Key**: `admin_user_id`
- **Features**: 
  - Role-based access control
  - Activity tracking (last login)
  - Account status management
  - Email verification

#### API Users (`api_users`)  
- **Purpose**: Customer API access (renamed from `users`)
- **Primary Key**: `user_id`
- **Features**:
  - API authentication with Sanctum
  - Order and ticket management
  - Email verification

#### Roles (`roles`)
- **Purpose**: Define admin permissions and access levels
- **Primary Key**: `role_id`
- **Features**:
  - JSON-based permissions
  - Hierarchical role structure
  - Active/inactive status

#### Admin User Roles (`admin_user_roles`)
- **Purpose**: Many-to-many relationship between admin users and roles
- **Features**:
  - Multiple roles per admin user
  - Timestamp tracking

### 2. Pre-configured Roles

1. **Super Administrator** (`super_admin`)
   - Full system access
   - All permissions including user management
   - System configuration access

2. **Administrator** (`admin`)
   - General admin access
   - Most CRUD operations
   - Cannot delete users or manage roles

3. **Event Manager** (`event_manager`)
   - Event and ticket management
   - Category management
   - Dashboard access

4. **Order Manager** (`order_manager`)
   - Order management
   - Customer view access
   - Transaction oversight

5. **Viewer** (`viewer`)
   - Read-only access
   - Dashboard and report viewing
   - No modification rights

### 3. Default Admin Accounts

After running the seeders, you'll have these admin accounts:

- **Super Admin**: `admin@example.com` / `password`
- **Admin Manager**: `manager@example.com` / `password`  
- **Event Manager**: `events@example.com` / `password`

### 4. Authentication Configuration

#### Admin Panel (Filament)
- **Guard**: `web`
- **Provider**: `admin_users`
- **Model**: `App\Models\AdminUser`

#### API Access
- **Guard**: `api` (Sanctum)
- **Provider**: `api_users`
- **Model**: `App\Models\ApiUser`

### 5. Permission System

Permissions follow the pattern: `{resource}.{action}`

#### Available Permissions:
- `users.view`, `users.create`, `users.edit`, `users.delete`
- `events.view`, `events.create`, `events.edit`, `events.delete`
- `orders.view`, `orders.create`, `orders.edit`, `orders.delete`
- `tickets.view`, `tickets.create`, `tickets.edit`, `tickets.delete`
- `categories.view`, `categories.create`, `categories.edit`, `categories.delete`
- `roles.view`, `roles.create`, `roles.edit`, `roles.delete`
- `admin_users.view`, `admin_users.create`, `admin_users.edit`, `admin_users.delete`
- `dashboard.view`, `system.manage`

## Models and Their Methods

### AdminUser Model

```php
// Role management
$adminUser->hasRole('super_admin')
$adminUser->hasAnyRole(['admin', 'super_admin'])
$adminUser->hasAllRoles(['admin', 'viewer'])
$adminUser->assignRole('admin')
$adminUser->removeRole('viewer')

// Permission checking
$adminUser->hasPermission('users.create')
$adminUser->getAllPermissions()

// Activity tracking
$adminUser->updateLastLogin()
```

### Role Model

```php
// Permission management
$role->hasPermission('events.create')
$role->addPermission('new.permission')
$role->removePermission('old.permission')
$role->setPermissions(['users.view', 'events.view'])
```

### ApiUser Model

```php
// Same as original Users model
$apiUser->orders()
$apiUser->tickets()
```

## Filament Resources

### Admin Management
- **AdminUserResource**: Manage admin accounts and role assignments
- **RoleResource**: Manage roles and permissions
- **UsersResource**: Renamed to "API Users" - manages customer accounts

### Navigation Groups
- **Admin Management**: AdminUser and Role management
- **API User Management**: Customer/API user management
- **Existing Groups**: Events, Orders, Tickets, etc.

## Migration Guide

### Database Changes
All existing data is preserved:
1. Original `users` table renamed to `api_users`
2. New `admin_users`, `roles`, and `admin_user_roles` tables created
3. Foreign key references updated automatically

### Code Changes Required

1. **Update API Controllers** (✅ Completed)
   - Changed `Users::` to `ApiUser::`
   - Updated type hints and relationships

2. **Authentication Guards** (✅ Completed)
   - Web guard now uses `admin_users` provider
   - API guard uses `api_users` provider

3. **Filament Configuration** (✅ Completed)
   - Admin panel uses AdminUser model
   - API Users resource manages customer data

## Security Features

### Role-Based Access Control
- Middleware: `CheckAdminRole`
- Usage: `->middleware('admin.role:super_admin')`
- Permission checks: `->middleware('admin.role:,users.create')`

### Account Security
- Admin account status management
- Last login tracking
- Email verification requirements
- Password hashing with Laravel's built-in hasher

## Usage Examples

### Creating Admin Users
```php
$admin = AdminUser::create([
    'name' => 'John Admin',
    'email' => 'john@admin.com',
    'password' => 'secure_password',
    'is_active' => true
]);

$admin->assignRole('event_manager');
```

### Checking Permissions in Controllers
```php
public function store(Request $request)
{
    if (!auth()->user()->hasPermission('events.create')) {
        abort(403, 'Insufficient permissions');
    }
    
    // Create event logic
}
```

### Using Middleware
```php
Route::group(['middleware' => ['auth', 'admin.role:super_admin']], function () {
    Route::resource('admin-users', AdminUserController::class);
});
```

## Testing the Setup

1. **Admin Login**: Visit `/admin` and login with any of the default accounts
2. **Role Management**: Navigate to Admin Management > Roles
3. **User Management**: Check both Admin Users and API Users sections
4. **Permission Testing**: Try accessing different resources with different role accounts

## Maintenance

### Adding New Permissions
1. Add to RoleSeeder permissions arrays
2. Update RoleResource checkbox options
3. Re-seed roles: `php artisan db:seed --class=RoleSeeder`

### Creating New Roles
1. Use the Roles resource in Filament admin panel
2. Or add to RoleSeeder and re-seed

### Backup Strategy
- Regular backups of `admin_users`, `roles`, and `admin_user_roles` tables
- Export role configurations before major changes
- Test permission changes in staging environment

## Troubleshooting

### Common Issues

1. **403 Errors**: Check user roles and permissions
2. **Login Issues**: Verify guard configuration in `config/auth.php`
3. **Migration Errors**: Ensure foreign key constraints are properly set

### Debug Commands
```bash
# Check migration status
php artisan migrate:status

# Verify admin user setup
php artisan tinker --execute="App\Models\AdminUser::with('roles')->get()"

# Check role permissions
php artisan tinker --execute="App\Models\Role::with('adminUsers')->get()"
```

This upgrade provides a robust, scalable user management system with proper separation of concerns between admin and customer access while maintaining backward compatibility.

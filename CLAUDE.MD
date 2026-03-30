# Refined CMS - Laravel Package Documentation

**Package Name**: refineddigital/cms
**Version**: 1.31.6
**Type**: Laravel CMS Core Package
**Author**: Matthias Law (matthias@refineddigital.co.nz)
**License**: MIT
**Repository**: https://gitlab.com/refineddigital/cms

---

## Overview

Refined CMS is a comprehensive, modular content management system built on Laravel 12+. It provides a complete admin interface, hierarchical page management, media library, user management, and extensible module architecture for building sophisticated content-driven websites.

### Key Characteristics

- **Modular Architecture**: Plugin-based system with independent, self-contained modules
- **Repository Pattern**: Clean separation of data access logic
- **Multi-Tenancy Support**: Built-in support for multiple sites/domains
- **Block-Based Content**: Flexible content blocks with 11+ content types
- **Vue.js Admin Interface**: Modern, reactive admin UI built with Vue 2.6
- **Hierarchical Structure**: Nested pages, media categories, and content organization
- **SEO-Optimized**: Automatic URI management, meta tags, sitemap generation

---

## System Requirements

### PHP Dependencies (composer.json)

```json
{
  "php": "^8.2",
  "laravel/framework": "^12.0",
  "laravel/ui": "^4.5",
  "laravel/sanctum": "^4.0",
  "barryvdh/laravel-debugbar": "^3.13",
  "intervention/image": "^3.6",
  "spatie/eloquent-sortable": "^4.2",
  "spatie/laravel-activitylog": "^4.8",
  "spatie/laravel-html": "^3.9",
  "silber/page-cache": "^1.1",
  "spatie/laravel-sluggable": "^3.6",
  "protonemedia/laravel-cross-eloquent-search": "^3.4",
  "friendsofphp/php-cs-fixer": "^3.62"
}
```

### Frontend Dependencies (package.json)

**Core Libraries**:
- Vue.js 2.6.14 (admin UI framework)
- jQuery 3.6.0 + jQuery UI 1.12.1
- Axios 0.21.1 (HTTP client)
- Laravel Mix 6.0.24 (asset compilation)

**UI Components**:
- Dragula 3.7.3 (drag-and-drop)
- Dropzone 5.9.2 (file uploads)
- Flatpickr 4.6.9 (date picker)
- Trumbowyg (rich text editor)
- SweetAlert 2.1.2 (alerts/modals)
- Vue Multiselect 2.1.6
- Vuedraggable 2.24.3

---

## Architecture Overview

### Directory Structure

```
core/
├── assets/                    # Compiled frontend assets (public)
├── config/
│   ├── modules.php           # Module registration
│   └── users.php             # User configuration
├── resources/
│   ├── js/                   # Vue components, directives, mixins
│   ├── sass/                 # Admin interface styles
│   ├── img/                  # Images
│   └── svg/                  # SVG icons
├── src/
│   ├── Commands/             # Artisan commands
│   │   ├── InstallCMS.php
│   │   ├── InstallDatabase.php
│   │   ├── InstallSymLink.php
│   │   ├── CreateModule.php
│   │   └── CreateContentBlock.php
│   ├── Database/
│   │   ├── Migrations/       # Core database tables
│   │   └── Seeds/            # Default data seeders
│   └── Modules/
│       ├── Core/             # Foundation module
│       ├── Pages/            # Page management
│       ├── Users/            # Authentication & authorization
│       ├── Media/            # File management
│       ├── Tags/             # Tagging system
│       ├── Settings/         # Key-value settings
│       ├── SiteSettings/     # Global site config
│       └── Content/          # Content block storage
├── composer.json
├── package.json
└── webpack.mix.js
```

### Module Pattern

Each module follows this structure:

```
Module/
├── Providers/
│   └── ModuleServiceProvider.php    # Module registration
├── Http/
│   ├── Controllers/                 # Request handlers
│   ├── Requests/                    # Form validation
│   ├── Repositories/                # Data access
│   └── routes.php                   # Module routes
├── Models/                          # Eloquent models
├── Resources/
│   └── views/                       # Blade templates
├── Config/                          # Module config
├── Traits/                          # Reusable behaviors
└── Scopes/                          # Query scopes
```

---

## Core Modules

### 1. Core Module (`/src/Modules/Core/`)

**Purpose**: Foundation layer providing base classes, helpers, aggregates, and utilities for all other modules.

#### Key Components

**Base Models**:
- `CoreModel`: Base Eloquent model with built-in features:
  - Scopes: `active()`, `order()`, `paging()`, `keywords()`, `search()`
  - Sortable trait integration
  - Excerpt generation from HTML
  - Image handling helpers
  - Automatic keyword searching

**Helper Classes** (via `src/Modules/Core/Helpers/helpers.php`):

```php
help()            // Core utility functions (RefinedDigital\CMS\Modules\Core\Helpers\Help)
format()          // Data formatting utilities
image()           // Image manipulation (RefinedImage)
files()           // File handling (RefinedFile)
menu()            // Menu generation
settings()        // Settings repository access
pages()           // Page helpers
search()          // Cross-model search
users()           // User repository
tags()            // Tag helpers
paymentGateways() // Payment gateway integration
core()            // Core helper functions
breadcrumbs()     // Breadcrumb generation
refinedErrorHandler() // Error view handling
refined_asset()   // Multi-tenancy aware asset URLs
```

**Help Class Features** (`Help.php`):
- Link validation and processing
- Email/phone obfuscation (anti-spam)
- File size formatting (bytes to KB/MB)
- URL parsing and building
- Multi-tenancy support detection
- Video embedding (YouTube/Vimeo)
- Base HREF management
- Page hierarchy traversal

**Aggregates** (Singleton Pattern):
- `ModuleAggregate`: Manages admin menu items and hierarchy
- `ContentAggregate`: Registers content block types
- `RouteAggregate`: Discovers and registers module routes
- `PublicRouteAggregate`: Public-facing routes
- `FrontEndPublicRouteAggregate`: Frontend public routes
- `AssetAggregate`: CSS/JS asset management
- `SitemapXMLAggregate`: Sitemap generation
- `PackageAggregate`: Package registration
- `PaymentGatewayAggregate`: Payment gateway registry
- `CustomModuleAggregate`: Custom module discovery

**Traits**:
- `ClearResponseCacheTrait`: Automatic cache invalidation
- `EditFormFieldsTrait`: Dynamic form field definitions
- `ExtraFillableFieldsTrait`: Runtime fillable attributes
- `HasSettings`: Model-level settings storage
- `HasImages`: Image relationship management
- `HasFiles`: File attachment handling

**Controllers**:
- `CoreController`: Base CRUD controller
  - `index()`: Paginated listing with search
  - `create()`: Form rendering
  - `store()`: Create new records
  - `edit()`: Edit form rendering
  - `update()`: Update records
  - `destroy()`: Soft delete
  - `restore()`: Undelete
  - `position()`: Reorder sortable items
  - `active()`: Toggle active status

**Enums**:
- `PageContentType`: Defines content block types
  - RICH: Rich text editor
  - STATIC: Static HTML
  - PLAIN: Plain text
  - IMAGE: Single image upload
  - FILE: File attachment
  - SELECT: Dropdown selection
  - LINK: URL/link input
  - NUMBER: Numeric input
  - REPEATABLE: Repeating field groups
  - PASSWORD: Password input
  - COLOUR: Color picker

**Middleware**:
- `Admin`: Restricts access to admin users
- `UserLevel`: Role-based access control
- `InjectTenantParameter`: Multi-tenancy support

**View Composers**:
- `MenuComposer`: Builds admin navigation menu

---

### 2. Pages Module (`/src/Modules/Pages/`)

**Purpose**: Hierarchical page management with content blocks, templates, and URI routing.

#### Database Schema

**pages** table:
```
id, name, banner, page_holder_id, parent, template_id,
active, position, in_menu, in_sitemap, hide_sub_pages,
content (JSON), settings (JSON), created_at, updated_at, deleted_at
```

**page_holders** table:
```
id, name, active, position, form_id
```

**templates** table:
```
id, name, view, source, active, position
```

**uri** table (polymorphic):
```
id, uri, model_id, model_type
```

#### Key Models

**Page**:
```php
class Page extends CoreModel
{
    use SoftDeletes, IsPage, HasContentBlocks, SortablePageTrait;

    // Relationships
    public function parent()      // Parent page
    public function pageHolder()  // Page group
    public function uri()         // Polymorphic URI
    public function content()     // Content blocks
    public function settings()    // Page settings

    // Casts
    protected $casts = [
        'content' => 'object',
        'settings' => 'object',
    ];
}
```

**Traits**:

1. **IsPage**:
   - Auto-generates and maintains URIs via morphOne relationship
   - Handles SEO metadata (title, description, keywords)
   - Manages breadcrumb generation
   - Saves page data on model save event
   - Methods: `getUri()`, `getBreadcrumb()`, `getMetaTitle()`

2. **HasContentBlocks**:
   - Saves content blocks to `Content` table
   - Supports 11+ field types via PageContentType enum
   - Handles repeatable field groups
   - Processes file uploads and media references
   - Method: `saveContent($data)` - called automatically on save

#### Controllers

**PageController**:

Key Methods:
```php
index()           // Tree view of all pages with holders
create()          // New page form
store()           // Create page with URI generation
edit($id)         // Edit page form with content blocks
update($id)       // Update page and regenerate URI if needed
destroy($id)      // Soft delete page
position()        // Reorder pages via drag-and-drop
updateParent()    // Move page to different parent/holder
getTree()         // Full page tree (admin API)
getTreeBasic()    // Simplified tree (frontend API)
render($uri)      // Public page rendering by URI
xmlSitemap()      // Generate sitemap.xml
```

**TemplateController**:
- CRUD operations for page templates
- Template selection per page

#### Repository Pattern

**PageRepository**:
```php
getTree()           // Recursive page hierarchy
getBranch($id)      // Children of page holder/parent
findByUri($uri)     // Resolve page from URI
store($data)        // Create with URI
update($id, $data)  // Update and refresh URI
destroy($id)        // Soft delete
```

#### Page Rendering Flow

1. Request comes to `/some/page/uri`
2. Catch-all route sends to `PageController@render($uri)`
3. `PageRepository::findByUri($uri)` queries `uri` table
4. Page model loaded with content blocks
5. Template determined from `page->template_id`
6. View rendered: `templates/{template_view}`
7. Content blocks available via `$page->content`

---

### 3. Users Module (`/src/Modules/Users/`)

**Purpose**: User authentication, authorization, user groups, and role-based access control.

#### Database Schema

**users** table:
```
id, first_name, last_name, email, password,
user_level_id, active, position,
created_at, updated_at, deleted_at
```

**user_levels** table:
```
id, name, position, created_at, updated_at
```

**user_groups** table:
```
id, name, active, position, created_at, updated_at, deleted_at
```

**user_user_group** (pivot):
```
user_id, user_group_id
```

#### Key Models

**User**:
```php
class User extends Authenticatable
{
    use Notifiable, SoftDeletes, SortableTrait, UserLevel;

    // Virtual attributes
    protected $appends = ['name']; // Computed: first_name + last_name

    // Relationships
    public function groups()  // belongsToMany UserGroup
    public function level()   // belongsTo UserLevel

    // Scopes
    scopeActive()
    scopeOrder()
    scopePaging()
    scopeKeywords()  // Searches: first_name, last_name, email
}
```

**UserLevel**:
- Defines permission levels/roles
- Used for middleware-based access control
- Examples: Super Admin, Admin, Editor, etc.

**UserGroup**:
- Organize users into groups (many-to-many)
- Examples: Marketing Team, Development Team, etc.

#### Controllers

**UserController**:
```php
index()     // List users with search
create()    // New user form (with level/group selection)
store()     // Create user + sync groups
edit($id)   // Edit user form
update($id) // Update user + sync groups
destroy($id) // Soft delete user
```

#### Authentication

Uses Laravel's built-in auth system:
- Routes defined in `Core/Http/Routes/auth.php`
- Login/logout/password reset
- Sanctum for API authentication
- Session-based admin authentication

#### Middleware

**UserLevel** (`userLevel` middleware):
- Checks user's `user_level_id`
- Restricts access based on role
- Usage: `->middleware('userLevel:1,2')`

**Admin** (`admin` middleware):
- Ensures user is authenticated and active
- Redirects to login if not authenticated

---

### 4. Media Module (`/src/Modules/Media/`)

**Purpose**: File upload, storage, organization, and retrieval with hierarchical categories.

#### Database Schema

**media** table:
```
id, file, alt_text, description, mime_type,
media_category_id, external_id, external_url,
created_at, updated_at, deleted_at
```

**media_categories** table:
```
id, name, parent, active, position
```

#### Key Models

**Media**:
```php
class Media extends CoreModel
{
    use SoftDeletes;

    // Relationships
    public function category() // belongsTo MediaCategory

    // Virtual attributes (appended)
    protected $appends = ['link', 'extension', 'type', 'size'];

    // Cached methods (7 days high cache, 1 day low cache)
    public function getFileUrl()  // Full URL to file
    public function getFilePath() // Storage path
    public function exists()      // Check if file exists

    // Computed attributes
    getExtension()  // File extension (jpg, pdf, etc.)
    getType()       // Category: Image, File, or Video
    getSize()       // Human-readable file size
}
```

**MediaCategory**:
```php
class MediaCategory extends CoreModel
{
    // Relationships
    public function parent()   // belongsTo self (hierarchical)
    public function children() // hasMany self
    public function media()    // hasMany Media
}
```

#### Controllers

**MediaController**:
```php
index()                 // List all media files
show($id)               // Get media details
update($id)             // Update metadata (alt, description)
destroy($id)            // Soft delete file
destroyBulk()           // Delete multiple files
uploadFile()            // Handle file upload
updateParent()          // Move media to different category
getTree()               // Category tree with media counts

// Category management
categoryStore()         // Create category
categoryUpdate($id)     // Update category
categoryDestroy($id)    // Delete category
categoryPosition()      // Reorder categories
categoryUpdateParent()  // Move category in hierarchy
```

#### File Handling

**Upload Process**:
1. File posted to `MediaController@uploadFile()`
2. `MediaRepository::uploadFile()` processes upload
3. File stored using Laravel Storage (configurable disk)
4. Thumbnail generated for images (500px width)
5. Media record created with metadata
6. File URL and path cached for performance

**Storage Configuration**:
- Disk: Configured via `config('pages.image.disk')`
- Default: `public` disk
- Supports S3, local, or custom drivers

**Image Thumbnailing**:
- Automatic thumbnail generation
- 500px width (maintains aspect ratio)
- Uses Intervention/Image library
- Stored alongside original file

**Caching Strategy**:
- High cache (7 days): File existence checks, paths
- Low cache (1 day): URLs, metadata
- Automatic cache invalidation on update/delete

---

### 5. Tags Module (`/src/Modules/Tags/`)

**Purpose**: Flexible tagging system for content organization and categorization.

#### Database Schema

**tags** table:
```
id, name, type, image, content (JSON), position,
created_at, updated_at
```

#### Key Models

**Tag**:
```php
class Tag extends CoreModel
{
    use SortableTrait, IsTag;

    // Form fields
    protected $formFields = [
        ['type' => 'text', 'name' => 'name'],
        ['type' => 'select', 'name' => 'type'],
        ['type' => 'richtext', 'name' => 'content'],
        ['type' => 'image', 'name' => 'image'],
    ];
}
```

**Trait - IsTag**:
- Provides tag-specific functionality
- URI generation for tag pages
- Content rendering

#### Controllers

**TagController**:
```php
index()         // List all tags
create()        // New tag form
store()         // Create tag
edit($id)       // Edit tag form
update($id)     // Update tag
destroy($id)    // Delete tag
getAllTags()    // API endpoint: JSON list of all tags
```

#### Usage Patterns

**Type-based Tags**:
Tags support a `type` field for categorization:
- Blog tags
- Product tags
- News tags
- Custom type tags

**Content Support**:
Tags can have rich content (via `content` JSON field):
- Description
- Featured image
- Additional metadata

---

### 6. Settings Module (`/src/Modules/Settings/`)

**Purpose**: Global and model-specific key-value configuration storage.

#### Database Schema

**settings** table:
```
id, name, model, value (JSON), required, position,
created_at, updated_at
```

#### Key Models

**Setting**:
```php
class Setting extends CoreModel
{
    use SortableTrait;

    protected $casts = [
        'value' => 'object', // Flexible JSON storage
    ];

    // Attributes
    'name'     => string  // Setting key
    'model'    => string  // Target model (null for global)
    'value'    => object  // JSON data
    'required' => boolean // Must be configured?
}
```

#### Repository Pattern

**SettingRepository**:
```php
public function get($model = null)
{
    // Retrieves settings for specific model or global
}

public function store($data)
{
    // Creates new setting
}

public function update($id, $data)
{
    // Updates setting value
}
```

#### Usage Patterns

**Global Settings**:
```php
settings()->get(); // All global settings
```

**Model-Specific Settings**:
```php
settings()->get('Page'); // Settings for Page model
```

**Accessing via Helper**:
```php
$value = settings()->get()->where('name', 'site_name')->first()->value;
```

---

### 7. SiteSettings Module (`/src/Modules/SiteSettings/`)

**Purpose**: High-level site configuration and metadata management.

#### Key Features

- Global site metadata
- Site-wide configuration options
- System preferences
- Integrates with Settings module
- Admin UI at `/refined/site/settings`

---

### 8. Content Module (`/src/Modules/Content/`)

**Purpose**: Polymorphic content block storage system.

#### Database Schema

**content** table (no timestamps):
```
id, contentable_type, contentable_id,
content_class, position, field, data (JSON)
```

#### Key Models

**Content**:
```php
class Content extends Model
{
    public $timestamps = false;

    protected $table = 'content';
    protected $casts = ['data' => 'object'];

    // Polymorphic relationship
    public function contentable()
    {
        return $this->morphTo();
    }
}
```

#### Integration Pattern

**HasContentBlocks Trait**:
```php
trait HasContentBlocks
{
    public function content()
    {
        return $this->morphMany(Content::class, 'contentable');
    }

    public function saveContent($data)
    {
        // Processes form data
        // Creates/updates Content records
        // Handles repeatable fields
        // Manages file uploads
    }
}
```

#### Usage Example

```php
// In Page model
class Page extends CoreModel
{
    use HasContentBlocks;
}

// Content is automatically saved when page is saved
$page->save(); // Triggers saveContent()

// Accessing content blocks
$page->content->where('field', 'main_content')->first()->data
```

---

## CLI Commands

### Installation Commands

#### 1. `refinedCMS:install-full`

Full interactive installation wizard:

```bash
php artisan refinedCMS:install-full
```

**Steps**:
1. Database configuration check
2. Site name input
3. Site URL configuration
4. Asset publishing
5. Symlink creation
6. Database migrations
7. Default data seeding
8. Admin user creation
9. cPanel compatibility options

**Creates**:
- Database tables
- Admin user account
- Default page holders
- System settings
- Published assets

---

#### 2. `php artisan refinedCMS:install-database`

Database setup only:

```bash
php artisan refinedCMS:install-database
```

**Runs**:
- All migrations in `src/Database/Migrations/`
- Seeders: `RefinedDatabaseSeeder`
  - `PageHoldersSeeder`
  - `UserLevelSeeder`
  - `MediaSeeder`

---

#### 3. `php artisan refinedCMS:install-symlink`

Creates symlink for public assets:

```bash
php artisan refinedCMS:install-symlink
```

**Creates**:
- Symlink: `public/vendor/refined/core` → `vendor/refineddigital/cms/assets`

---

### Development Commands

#### 4. `php artisan make:module {name} [--isPage]`

Generate a new custom module:

```bash
php artisan make:module Blog
php artisan make:module Portfolio --isPage
```

**Without `--isPage`**:
```
app/RefinedCMS/{Name}/
├── Http/
│   ├── Controllers/{Name}Controller.php
│   ├── Repositories/{Name}Repository.php
│   ├── Requests/{Name}Request.php
│   └── routes.php
├── Models/{Name}.php
└── Providers/{Name}ServiceProvider.php
```

**With `--isPage`** (adds page features):
```
Additional files:
├── Config/
│   └── config.php
├── Database/
│   └── Migrations/{timestamp}_create_{name}_table.php
├── Resources/
│   └── views/
└── Traits/
    └── Is{Name}.php (URI management)
```

**Auto-registers**:
- Service provider in `bootstrap/providers.php`
- Routes in RouteAggregate
- Admin menu item in ModuleAggregate

---

#### 5. `php artisan make:content-block {name}`

Generate a new content block type:

```bash
php artisan make:content-block VideoEmbed
```

**Creates**:
- Blade template in `resources/views/content-blocks/`
- Registers in ContentAggregate

---

### Version Management (package.json scripts)

```bash
npm run version:patch  # Bump patch version (1.0.0 → 1.0.1)
npm run version:minor  # Bump minor version (1.0.0 → 1.1.0)
npm run version:major  # Bump major version (1.0.0 → 2.0.0)
```

All version commands:
1. Update version in `package.json`
2. Create git tag
3. Push tag and branch to remote

---

## Routing Structure

### Admin Routes (`/refined` prefix)

**Authentication Required**: `auth` + `admin` middleware

```
GET/POST   /refined/login
POST       /refined/logout

GET        /refined/dashboard

# Pages
GET        /refined/pages
GET        /refined/pages/create
POST       /refined/pages
GET        /refined/pages/{id}/edit
PUT        /refined/pages/{id}
DELETE     /refined/pages/{id}
POST       /refined/pages/position
POST       /refined/pages/update-parent

# Users
GET        /refined/users
GET        /refined/users/create
POST       /refined/users
GET        /refined/users/{id}/edit
PUT        /refined/users/{id}
DELETE     /refined/users/{id}

# User Groups
GET        /refined/user-groups
# ... standard CRUD routes

# Media
GET        /refined/media
POST       /refined/media/upload-file
PUT        /refined/media/{id}
DELETE     /refined/media/{id}
POST       /refined/media/destroy-bulk
POST       /refined/media/update-parent
GET        /refined/media/tree

# Media Categories
POST       /refined/media/category
PUT        /refined/media/category/{id}
DELETE     /refined/media/category/{id}
POST       /refined/media/category/position
POST       /refined/media/category/update-parent

# Tags
GET        /refined/tags
# ... standard CRUD routes
GET        /api/tags (public)

# Templates
GET        /refined/templates
# ... standard CRUD routes

# Settings
GET        /refined/settings
GET        /refined/settings/{id}/edit
PUT        /refined/settings/{id}

# Site Settings
GET        /refined/site/settings
```

---

### Public Routes

```
# API Endpoints
GET        /api/menu/{id}           # Page tree for frontend
GET        /api/setup               # Initial site data
GET        /api/pages/{uri}         # Page data by URI

# Frontend Pages
GET        /{uri}                   # Catch-all page render
GET        /sitemap.xml             # XML sitemap

# Multi-Tenancy Routes (when enabled)
GET        /{tenant}/{uri}
GET        /{tenant}/sitemap.xml
```

---

### Custom Module Routes

Custom modules add their own routes via `ModuleName/Http/routes.php`:

```php
// Example: Blog module routes
Route::group(['prefix' => config('core.app_route'), 'middleware' => ['web', 'auth', 'admin']], function() {
    Route::resource('blog', BlogController::class);
});
```

---

## Frontend Asset Pipeline

### Webpack Mix Configuration

**File**: `webpack.mix.js`

```javascript
mix
  .setPublicPath('assets')
  .copyDirectory('resources/img', 'assets/img')
  .copy('resources/svg/editor/icons.svg', 'assets/svg/editor-icons.svg')
  .js('resources/js/main.js', 'js/').vue()
  .js('resources/js/front-end/modules/FormBuilder.js', 'assets/js')
  .js('resources/js/front-end/plugins/FormValidate.js', 'assets/js')
  .sass('resources/sass/main.scss', 'css')
```

**Outputs**:
- `assets/js/main.js` - Admin UI bundle (Vue app)
- `assets/js/FormBuilder.js` - Frontend form builder
- `assets/js/FormValidate.js` - Frontend validation
- `assets/css/main.css` - Admin styles
- `assets/img/*` - Images
- `assets/svg/editor-icons.svg` - Icon sprite

---

### Vue.js Admin Interface

**Main Entry**: `resources/js/main.js`

**Components** (`resources/js/components/`):
- `Settings.vue` - Settings management
- `Link.vue` - Link input with validation
- `DatePicker.vue` - Date/time picker
- `FormRepeatable.vue` - Repeatable field groups
- `FormEmail.vue` - Email input with validation
- `Image.vue` - Image upload/selection
- `Select.vue` - Enhanced select dropdown
- `Selectize.vue` - Multi-select
- `TextEditor.vue` - Rich text editor (Trumbowyg)
- `PagesMenu.vue` - Page tree navigation
- `PagesContent.vue` - Content block manager
- `Media.vue` - Media library browser

**Directives** (`resources/js/directives/`):
- `SortableTable` - Drag-to-reorder tables
- `SortableContentItem` - Reorder content blocks
- `SortableRepeatableTable` - Reorder repeatable items
- `DraggableMedia` - Drag media items
- `DroppableMedia` - Drop zone for media
- `ConfirmDelete` - Confirmation dialog

**Mixins** (`resources/js/mixins/`):
- `PagesRepeatable` - Repeatable field logic
- `PagesImageNote` - Image note/caption handling

---

### Trumbowyg Plugins

Custom rich text editor plugins:

1. **refined-insert-image**: Media library integration
2. **refined-link**: Internal/external link picker
3. **refined-code**: Code block formatting

---

### Build Commands

```bash
# Development (unminified)
npm run dev

# Watch mode (auto-rebuild)
npm run watch

# Production (minified + versioned)
npm run prod
```

---

## Database Schema Reference

### Core Tables

#### users
```sql
id                 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
first_name         VARCHAR(191)
last_name          VARCHAR(191)
email              VARCHAR(191) UNIQUE
password           VARCHAR(191)
user_level_id      INT UNSIGNED
active             TINYINT(1) DEFAULT 1
position           INT DEFAULT 999
created_at         TIMESTAMP
updated_at         TIMESTAMP
deleted_at         TIMESTAMP NULL
```

#### pages
```sql
id                 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name               VARCHAR(191)
banner             VARCHAR(191) NULL
page_holder_id     INT UNSIGNED
parent             INT UNSIGNED DEFAULT 0
template_id        INT UNSIGNED
active             TINYINT(1) DEFAULT 1
position           INT DEFAULT 999
in_menu            TINYINT(1) DEFAULT 1
in_sitemap         TINYINT(1) DEFAULT 1
hide_sub_pages     TINYINT(1) DEFAULT 0
content            JSON NULL
settings           JSON NULL
created_at         TIMESTAMP
updated_at         TIMESTAMP
deleted_at         TIMESTAMP NULL
```

#### page_holders
```sql
id                 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name               VARCHAR(191)
active             TINYINT(1) DEFAULT 1
position           INT DEFAULT 999
form_id            INT UNSIGNED NULL
```

#### uri (polymorphic)
```sql
id                 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
uri                VARCHAR(191) UNIQUE
model_id           BIGINT UNSIGNED
model_type         VARCHAR(191)
INDEX(model_id, model_type)
```

#### media
```sql
id                 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
file               VARCHAR(191)
alt_text           TEXT NULL
description        TEXT NULL
mime_type          VARCHAR(191) NULL
media_category_id  INT UNSIGNED NULL
external_id        VARCHAR(191) NULL
external_url       VARCHAR(191) NULL
created_at         TIMESTAMP
updated_at         TIMESTAMP
deleted_at         TIMESTAMP NULL
```

#### media_categories
```sql
id                 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name               VARCHAR(191)
parent             INT UNSIGNED DEFAULT 0
active             TINYINT(1) DEFAULT 1
position           INT DEFAULT 999
```

#### content (polymorphic)
```sql
id                 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
contentable_type   VARCHAR(191)
contentable_id     BIGINT UNSIGNED
content_class      VARCHAR(191) NULL
position           INT DEFAULT 0
field              VARCHAR(191)
data               JSON
INDEX(contentable_type, contentable_id)
```

#### tags
```sql
id                 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name               VARCHAR(191)
type               VARCHAR(191) NULL
image              VARCHAR(191) NULL
content            JSON NULL
position           INT DEFAULT 999
created_at         TIMESTAMP
updated_at         TIMESTAMP
```

#### settings
```sql
id                 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name               VARCHAR(191)
model              VARCHAR(191) NULL
value              JSON
required           TINYINT(1) DEFAULT 0
position           INT DEFAULT 999
created_at         TIMESTAMP
updated_at         TIMESTAMP
```

#### templates
```sql
id                 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name               VARCHAR(191)
view               VARCHAR(191)
source             VARCHAR(191) NULL
active             TINYINT(1) DEFAULT 1
position           INT DEFAULT 999
```

#### user_levels
```sql
id                 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name               VARCHAR(191)
position           INT DEFAULT 999
created_at         TIMESTAMP
updated_at         TIMESTAMP
```

#### user_groups
```sql
id                 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name               VARCHAR(191)
active             TINYINT(1) DEFAULT 1
position           INT DEFAULT 999
created_at         TIMESTAMP
updated_at         TIMESTAMP
deleted_at         TIMESTAMP NULL
```

#### user_user_group (pivot)
```sql
user_id            BIGINT UNSIGNED
user_group_id      BIGINT UNSIGNED
PRIMARY KEY(user_id, user_group_id)
```

---

## Multi-Tenancy Support

### Configuration

**Detection**: Domain-based via `tenancy.central_domains` config

**When Enabled**:
1. Routes prefixed with `{tenant}/`
2. Middleware: `InitializeTenancyByDomain`, `PreventAccessFromCentralDomains`
3. Custom URL generator: `CustomUrlGenerator`
4. Asset URLs via `refined_asset()` helper point to central domain

### Custom URL Generator

**File**: `src/Modules/Core/Routing/CustomUrlGenerator.php`

Extends Laravel's `UrlGenerator` to handle multi-tenant URLs:
- Automatically includes tenant prefix
- Routes to central domain for assets
- Maintains correct URLs across tenant boundaries

### Helper Integration

```php
// Checks if multi-tenancy is enabled
help()->isMultiTenancy()

// Returns tenant-aware asset URL
refined_asset('vendor/refined/core/js/main.js')
// → https://central-domain.com/vendor/refined/core/js/main.js
```

---

## Extensibility & Customization

### Creating Custom Modules

**Step 1**: Generate module scaffold

```bash
php artisan make:module Products --isPage
```

**Step 2**: Define model

```php
// app/RefinedCMS/Products/Models/Product.php
namespace App\RefinedCMS\Products\Models;

use RefinedDigital\CMS\Modules\Core\Models\CoreModel;
use App\RefinedCMS\Products\Traits\IsProduct;

class Product extends CoreModel
{
    use IsProduct; // For page-like features (URI, content blocks)

    protected $fillable = ['name', 'price', 'description', 'active'];
}
```

**Step 3**: Create controller

```php
// app/RefinedCMS/Products/Http/Controllers/ProductController.php
namespace App\RefinedCMS\Products\Http\Controllers;

use RefinedDigital\CMS\Modules\Core\Http\Controllers\CoreController;
use App\RefinedCMS\Products\Http\Repositories\ProductRepository;

class ProductController extends CoreController
{
    public function __construct(ProductRepository $repo)
    {
        $this->repository = $repo;
        $this->heading = 'Products';
    }
}
```

**Step 4**: Define routes

```php
// app/RefinedCMS/Products/Http/routes.php
Route::group([
    'prefix' => config('core.app_route'),
    'middleware' => ['web', 'auth', 'admin']
], function() {
    Route::resource('products', ProductController::class);
});
```

**Step 5**: Register service provider

```php
// app/RefinedCMS/Products/Providers/ProductServiceProvider.php
namespace App\RefinedCMS\Products\Providers;

use Illuminate\Support\ServiceProvider;

class ProductServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../Http/routes.php');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        // Register admin menu item
        app(\RefinedDigital\CMS\Modules\Core\Aggregates\ModuleAggregate::class)
            ->addModule([
                'name' => 'Products',
                'route' => 'refined.products.index',
                'icon' => 'tag',
                'activeRoutes' => ['products.*']
            ]);
    }
}
```

---

### Creating Content Block Types

**Step 1**: Generate block

```bash
php artisan make:content-block VideoEmbed
```

**Step 2**: Define block template

```blade
{{-- resources/views/content-blocks/video-embed.blade.php --}}
<div class="video-embed">
    <iframe src="{{ $data->url }}" frameborder="0" allowfullscreen></iframe>
</div>
```

**Step 3**: Register block type

```php
// In a service provider
use RefinedDigital\CMS\Modules\Core\Enums\PageContentType;

ContentAggregate::addContentType([
    'key' => 'VIDEO_EMBED',
    'name' => 'Video Embed',
    'view' => 'content-blocks.video-embed',
    'type' => PageContentType::LINK,
]);
```

---

### Custom Helpers

Add custom helpers to `app/Helpers/helpers.php`:

```php
if (!function_exists('myCustomHelper')) {
    function myCustomHelper()
    {
        return new \App\Helpers\MyCustomHelper;
    }
}
```

Register in `composer.json`:

```json
"autoload": {
    "files": [
        "app/Helpers/helpers.php"
    ]
}
```

---

### Custom Middleware

Create and register custom middleware:

```php
// app/Http/Middleware/CheckSubscription.php
namespace App\Http\Middleware;

use Closure;

class CheckSubscription
{
    public function handle($request, Closure $next)
    {
        if (!auth()->user()->hasActiveSubscription()) {
            return redirect('subscribe');
        }
        return $next($request);
    }
}
```

Register in `app/Http/Kernel.php` or via service provider:

```php
$router->aliasMiddleware('subscription', CheckSubscription::class);
```

---

### Blade Directives

**Built-in**: `@svg('icon-name')`

**Custom Example**:

```php
// In a service provider
\Blade::directive('price', function($expression) {
    return "<?php echo '$' . number_format($expression, 2); ?>";
});
```

Usage:
```blade
@price(19.99) {{-- Outputs: $19.99 --}}
```

---

## Best Practices

### Model Conventions

1. **Extend CoreModel** for automatic features:
   ```php
   use RefinedDigital\CMS\Modules\Core\Models\CoreModel;
   class MyModel extends CoreModel { }
   ```

2. **Use Soft Deletes** for recoverability:
   ```php
   use Illuminate\Database\Eloquent\SoftDeletes;
   class MyModel extends CoreModel
   {
       use SoftDeletes;
   }
   ```

3. **Define Form Fields** for auto-form generation:
   ```php
   protected $formFields = [
       ['type' => 'text', 'name' => 'title', 'label' => 'Title'],
       ['type' => 'richtext', 'name' => 'content'],
   ];
   ```

---

### Repository Pattern

Always use repositories for data access:

```php
// ✓ Good
class ProductController extends CoreController
{
    public function __construct(ProductRepository $repo)
    {
        $this->repository = $repo;
    }
}

// ✗ Bad
class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all(); // Direct model access
    }
}
```

---

### Content Block Usage

For page-like models, use `HasContentBlocks` trait:

```php
class Product extends CoreModel
{
    use HasContentBlocks;
}

// Content blocks automatically saved on model save
$product->save(); // Triggers saveContent()
```

---

### Caching Strategy

1. **Media Files**: Cached for 7 days (exists check), 1 day (URLs)
2. **Page Cache**: Uses `silber/page-cache` middleware
3. **Clear Cache**: Use `ClearResponseCacheTrait` on models that affect frontend
4. **Settings**: Cache globally, clear on update

---

### Security Considerations

1. **Authentication**: Always use `auth` middleware for admin routes
2. **Authorization**: Use `userLevel` or `admin` middleware
3. **Validation**: Create FormRequest classes for all inputs
4. **SQL Injection**: Use Eloquent/Query Builder (never raw SQL)
5. **XSS**: Blade automatically escapes `{{ $var }}`
6. **CSRF**: Enabled by default via Laravel middleware

---

## Configuration Files

### config/modules.php

Registers all CMS modules:

```php
return [
    'Users' => [
        'Provider' => RefinedDigital\CMS\Modules\Users\Providers\UsersServiceProvider::class,
        'Aliases' => []
    ],
    'Pages' => [
        'Provider' => RefinedDigital\CMS\Modules\Pages\Providers\PageServiceProvider::class,
        'Aliases' => []
    ],
    // ... more modules
];
```

To disable a module, comment out or remove its entry.

---

### config/users.php

User-specific configuration:

```php
return [
    'forms' => [
        'fillable' => ['first_name', 'last_name', 'email', 'password', 'user_level_id', 'active'],
        'columns' => [
            (object) ['name' => 'Name', 'field' => 'name', 'sortable' => true],
            (object) ['name' => 'Email', 'field' => 'email', 'sortable' => true],
            (object) ['name' => 'User Level', 'field' => 'userLevel', 'type' => 'userLevel'],
            (object) ['name' => 'Active', 'field' => 'active', 'type' => 'select'],
        ]
    ]
];
```

---

### Rich Editor Config

**Published**: `config/rich-editor.php`

Configures Trumbowyg editor:
- Available buttons
- Plugin settings
- Upload paths
- Allowed tags

---

### Searchable Models Config

**Published**: `config/searchable-models.php`

Registers models for cross-model search:

```php
return [
    \App\Models\Page::class => ['name', 'content'],
    \App\Models\Product::class => ['name', 'description'],
];
```

---

## API Reference

### Helper Functions

#### help()

```php
help()->isMultiTenancy()              // Check if multi-tenancy enabled
help()->baseHref()                     // Get base HREF for current page
help()->handleLink($link)              // Process link (internal/external)
help()->formatBytes($bytes)            // Format file size (e.g., "1.5 MB")
help()->getVideoCode($url, $options)   // Get video embed code
help()->encodeEmail($email)            // Anti-spam email encoding
help()->build_url($parts)              // Build URL from parse_url array
```

#### format()

```php
format()->date($date)                  // Format date
format()->datetime($datetime)          // Format datetime
format()->price($amount)               // Format price
format()->stripTags($html)             // Strip HTML tags safely
```

#### image()

```php
image()->resize($path, $width, $height)    // Resize image
image()->thumbnail($path, $width)          // Generate thumbnail
image()->crop($path, $width, $height)      // Crop image
```

#### pages()

```php
pages()->getBreadcrumb($page)          // Get breadcrumb for page
pages()->getChildren($pageId)          // Get child pages
pages()->getPageUrl($page)             // Get full URL for page
```

#### menu()

```php
menu()->render($holderId)              // Render navigation menu
menu()->getTree($holderId)             // Get page tree
```

#### settings()

```php
settings()->get($model)                // Get settings for model
settings()->getValue($name)            // Get specific setting value
```

#### search()

```php
search()->query($term)                 // Cross-model search
search()->addModel($model, $fields)    // Register searchable model
```

---

### Repository Methods

All repositories extend `CoreRepository` with these methods:

```php
$repo->all()                           // Get all records
$repo->find($id)                       // Find by ID
$repo->store($data)                    // Create new record
$repo->update($id, $data)              // Update record
$repo->destroy($id)                    // Delete record
$repo->getForDataTable()               // Get paginated data for tables
$repo->search($term)                   // Keyword search
```

---

## Troubleshooting

### Common Issues

#### 1. Assets Not Loading

**Symptoms**: 404 errors for `/vendor/refined/core/js/main.js`

**Solution**:
```bash
php artisan refinedCMS:install-symlink
# Or manually:
php artisan storage:link
```

---

#### 2. Database Connection Error

**Symptoms**: "SQLSTATE[HY000] [1045] Access denied"

**Solution**:
1. Check `.env` database credentials
2. Verify MySQL server is running
3. Test connection: `php artisan db:show`

---

#### 3. Module Not Appearing in Admin Menu

**Symptoms**: Custom module exists but doesn't show in menu

**Solution**:
1. Verify service provider is registered in `bootstrap/providers.php`
2. Check ModuleAggregate registration in provider's `boot()` method
3. Clear cache: `php artisan cache:clear`

---

#### 4. Page URIs Not Working

**Symptoms**: 404 on frontend pages, admin works fine

**Solution**:
1. Check `.htaccess` or nginx config for catch-all rule
2. Verify `uri` table has records: `php artisan tinker` → `Uri::count()`
3. Regenerate URIs: Edit and save page in admin

---

#### 5. File Uploads Failing

**Symptoms**: "The file failed to upload"

**Solution**:
1. Check storage permissions: `chmod -R 775 storage`
2. Verify `php.ini` settings: `upload_max_filesize`, `post_max_size`
3. Check storage disk config in `.env`: `FILESYSTEM_DISK=public`

---

#### 6. Rich Text Editor Not Loading

**Symptoms**: Textarea instead of Trumbowyg editor

**Solution**:
1. Rebuild assets: `npm run dev`
2. Clear browser cache
3. Check console for JS errors
4. Verify Trumbowyg dependency: `npm list trumbowyg`

---

### Debug Mode

Enable debug mode in `.env`:

```env
APP_DEBUG=true
DEBUGBAR_ENABLED=true  # Laravel Debugbar
```

**Debugbar Features**:
- SQL queries with timing
- Route information
- View data
- Memory usage
- Timeline

---

### Logging

Logs are written to `storage/logs/laravel.log`

**Enable Query Logging**:
```php
// In AppServiceProvider
DB::listen(function($query) {
    Log::info($query->sql, $query->bindings);
});
```

---

## Performance Optimization

### 1. Caching

**Page Caching** (silber/page-cache):
```php
// Apply to routes
Route::middleware('cacheResponse')->group(function() {
    Route::get('/', 'PageController@render');
});
```

**Settings Caching**:
```php
// Cache settings globally
Cache::remember('site_settings', 3600, function() {
    return settings()->get();
});
```

---

### 2. Image Optimization

**Intervention/Image** automatically optimizes:
- Thumbnails: 500px width
- Quality: 80% (configurable)
- Format conversion (WebP support)

**Lazy Loading**:
```blade
<img src="{{ $media->link }}" loading="lazy" alt="{{ $media->alt_text }}">
```

---

### 3. Database Indexing

Key indexes already defined:
- `uri.uri` (UNIQUE)
- `uri.model_id + model_type` (COMPOSITE)
- `content.contentable_type + contentable_id` (COMPOSITE)

**Add Custom Indexes**:
```php
Schema::table('products', function (Blueprint $table) {
    $table->index('category_id');
    $table->index(['active', 'position']);
});
```

---

### 4. Eager Loading

Always eager load relationships:

```php
// ✓ Good
Page::with(['pageHolder', 'content', 'uri'])->get();

// ✗ Bad (N+1 problem)
Page::all()->each(function($page) {
    echo $page->pageHolder->name; // Separate query each iteration
});
```

---

### 5. Asset Versioning

Production builds include versioning:

```bash
npm run prod  # Creates manifest with hashed filenames
```

Blade usage:
```blade
<script src="{{ mix('js/main.js', 'vendor/refined/core') }}"></script>
```

---

## Version History

**Current Version**: 1.31.6

**Recent Changes**:
- 1.31.6: Current version
- 1.31.5: Media category fixes
- 1.31.4: Bug fixes
- 1.31.3-1.31.0: Various improvements
- 1.30.x: Cache improvements for images

For full changelog, see git log:
```bash
git log --oneline
```

---

## Testing

### Running Tests

```bash
# PHPUnit (if tests exist)
./vendor/bin/phpunit

# Laravel's test command
php artisan test
```

---

### Manual Testing Checklist

**Admin Functions**:
- [ ] Login/logout
- [ ] Create/edit/delete page
- [ ] Upload media file
- [ ] Create user
- [ ] Change user permissions
- [ ] Create tag
- [ ] Update settings

**Frontend Functions**:
- [ ] View homepage
- [ ] Navigate to child page
- [ ] Submit form
- [ ] View media files
- [ ] Check sitemap.xml
- [ ] Test 404 page

---

## Support & Resources

**Issue Tracking**: https://gitlab.com/refineddigital/cms/issues

**Documentation**: This file (CLAUDE.MD)

**Laravel Documentation**: https://laravel.com/docs

**Package Dependencies**:
- Spatie Laravel Packages: https://spatie.be/docs/laravel-activitylog
- Intervention Image: https://image.intervention.io/v3
- Silber Page Cache: https://github.com/JosephSilber/page-cache

---

## License

**MIT License**

Copyright (c) Refined Digital

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

---

## Quick Start Guide

### For Developers New to This Package

**1. Installation**:
```bash
composer require refineddigital/cms
php artisan refinedCMS:install-full
```

**2. Create Admin User**:
Follow prompts during installation or:
```bash
php artisan tinker
User::create([
    'first_name' => 'Admin',
    'last_name' => 'User',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'user_level_id' => 1,
    'active' => 1
]);
```

**3. Access Admin**:
```
URL: http://yoursite.com/refined/login
```

**4. Build Frontend Assets**:
```bash
npm install
npm run dev
```

**5. Create First Page**:
1. Login to admin
2. Navigate to Pages
3. Click "Create Page"
4. Fill in name, select template
5. Add content blocks
6. Save

**6. View Page**:
```
URL: http://yoursite.com/{page-name}
```

---

## Summary

Refined CMS is a **production-ready, enterprise-grade Laravel CMS** with:

- **8 Core Modules**: Pages, Users, Media, Tags, Settings, SiteSettings, Content, Core
- **11+ Content Block Types**: Rich text, images, files, links, repeatable fields, etc.
- **Repository Pattern**: Clean data access architecture
- **Vue.js Admin**: Modern, reactive admin interface
- **Hierarchical Organization**: Pages, media categories, user groups
- **Multi-Tenancy**: Built-in support for multiple sites
- **Extensible**: Custom modules via artisan command
- **SEO-Optimized**: Automatic URI management, sitemaps
- **Performance**: Page caching, image optimization, lazy loading
- **Security**: Role-based access, CSRF protection, validation

**Perfect for**:
- Corporate websites
- Content-heavy applications
- Multi-site platforms
- Custom web applications needing a solid CMS foundation

---

**End of Documentation**

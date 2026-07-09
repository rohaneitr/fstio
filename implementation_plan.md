# Enterprise Brand Module Implementation Plan

This plan details the files to be created and modified to implement a complete, production-grade **Enterprise Brand Module** inside `fstio` on top of Bagisto v2.4.8.

---

## 1. Bounded Context & Folder Structure

We will implement all Brand bounded context components inside the existing `app/`, `database/`, `resources/`, and `tests/` directories, respecting DDD, CQRS, and Clean Architecture principles.

```
app/
├── Application/
│   ├── DTO/
│   │   ├── CreateBrandDto.php
│   │   └── UpdateBrandDto.php
│   └── Handlers/
│       ├── CreateBrandHandler.php
│       ├── UpdateBrandHandler.php
│       └── DeleteBrandHandler.php
├── Domain/
│   ├── Models/
│   │   ├── Brand.php
│   │   └── BrandSeries.php
│   └── Repositories/
│       └── Eloquent/
│           ├── BrandRepository.php
│           └── BrandSeriesRepository.php
└── Http/
    ├── Controllers/
    │   └── Admin/
    │       └── BrandController.php
    ├── DataGrids/
    │   └── BrandDataGrid.php
    └── Requests/
        └── BrandRequest.php
```

---

## 2. Proposed Changes

### Database Schema
#### [NEW] [2026_07_09_000007_create_brands_table.php](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/database/migrations/2026_07_09_000007_create_brands_table.php)
- Creates `brands` table: `id`, `slug` (unique index), `name`, `logo_light`, `logo_dark`, `website_url`, `status` (boolean), `description` (text), `meta_title`, `meta_keywords`, `meta_description`.

#### [NEW] [2026_07_09_000008_create_brand_series_table.php](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/database/migrations/2026_07_09_000008_create_brand_series_table.php)
- Creates `brand_series` table: `id`, `brand_id` (foreign key referencing `brands(id)` on delete cascade), `slug` (unique index), `name`.

---

### Domain Layer
#### [MODIFY] [Brand.php](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/app/Domain/Models/Brand.php)
- Add `$table = 'brands'`, `$fillable`, and dynamic relationship bindings (`series`).

#### [NEW] [BrandSeries.php](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/app/Domain/Models/BrandSeries.php)
- Create Eloquent model for series referencing the `Brand` model.

#### [NEW] [BrandSeriesRepository.php](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/app/Domain/Repositories/Eloquent/BrandSeriesRepository.php)
- Create repository concrete implementing `BrandSeriesRepositoryInterface`.

---

### Application Layer (CQRS Use Cases)
#### [NEW] [CreateBrandDto.php](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/app/Application/DTO/CreateBrandDto.php)
- Add DTO wrapper for creating brands.

#### [NEW] [CreateBrandHandler.php](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/app/Application/Handlers/CreateBrandHandler.php)
- Handles creation transactionally. Saves light/dark logo assets in WebP format using `image_manager()`.

#### [NEW] [UpdateBrandDto.php](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/app/Application/DTO/UpdateBrandDto.php)
- Add DTO wrapper for updating brands.

#### [NEW] [UpdateBrandHandler.php](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/app/Application/Handlers/UpdateBrandHandler.php)
- Handles brand updates. Deletes old logos on replacement.

#### [NEW] [DeleteBrandHandler.php](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/app/Application/Handlers/DeleteBrandHandler.php)
- Handles deletions. Deletes logo directories from storage.

---

### Presentation / Controller Layer
#### [NEW] [BrandRequest.php](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/app/Http/Requests/BrandRequest.php)
- Handlers validation for CRUD inputs: slug syntax, logo formats (WebP/SVG/PNG/JPG), and dimensions.

#### [NEW] [BrandDataGrid.php](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/app/Http/DataGrids/BrandDataGrid.php)
- Subclasses Datagrid to display columns with edit/delete actions.

#### [NEW] [BrandController.php](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/app/Http/Controllers/Admin/BrandController.php)
- CRUD controller dispatching commands to buses and rendering views.

#### [NEW] [index.blade.php](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/resources/views/admin/catalog/brands/index.blade.php)
- Displays Datagrid list view.

#### [NEW] [create.blade.php](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/resources/views/admin/catalog/brands/create.blade.php)
- Form layout for brand creation.

#### [NEW] [edit.blade.php](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/resources/views/admin/catalog/brands/edit.blade.php)
- Form layout for brand edits.

#### [MODIFY] [routes/web.php](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/routes/web.php)
- Registers `/admin/catalog/brands` controller routes.

---

### Menu & ACL Configurations
#### [MODIFY] [FoundationServiceProvider.php](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/app/Foundation/Providers/FoundationServiceProvider.php)
- Merges config for `menu.admin` and `acl` dynamically on boot.

#### [NEW] [menu.php](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/config/menu.php)
- Injects sidebar navigation item under `catalog.brands`.

#### [NEW] [acl.php](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/config/acl.php)
- Injects ACL permission tree for Brand administration.

---

### Localization Layer
#### [NEW] [brand.php (English)](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/lang/en/brand.php)
- English dictionary for labels.

#### [NEW] [brand.php (Bangla)](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/lang/bn/brand.php)
- Bangla dictionary for labels.

---

### Seeder & Test Layer
#### [NEW] [BrandSeeder.php](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/database/seeders/BrandSeeder.php)
- Seed data entries.

#### [NEW] [BrandControllerTest.php](file:///c:/Users/Rohan/Desktop/fastcomputer.com.bd/tests/Feature/BrandControllerTest.php)
- Feature tests asserting CRUD access validation, logo processing, and datagrid outputs.

---

## 3. Verification Plan

### Automated Tests
- Run complete unit and feature suites:
  ```bash
  docker compose exec -T laravel.test php artisan test
  ```
- Run code standards format checks:
  ```bash
  docker compose exec -T laravel.test vendor/bin/pint --test
  ```

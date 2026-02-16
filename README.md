# PHP_Laravel12_Reliese_Model_Generator

---

##  Overview

This project demonstrates how to install and configure Reliese Model Generator in Laravel 12.

Reliese automatically generates Eloquent models from existing database tables — including:

* Table definitions
* Relationships
* Fillable attributes
* Casts
* Base model separation

This setup follows a production-ready structure.

---

##  Features

* Laravel 12 compatible setup
* Automatic model generation from database
* Base & Main model separation
* Relationship auto-detection
* Production-ready configuration
* Regenerate specific tables anytime

---

##  Project Structure

```
reliese-demo/
│
├── app/
│   └── Models/
│       ├── Base/
│       │   ├── Category.php
│       │   └── Product.php
│       │
│       ├── Category.php
│       └── Product.php
│
├── config/
│   └── models.php
│
├── database/
│   └── migrations/
│
└── .env
```

---

## 1️ System Requirements

Before starting, make sure you have:

* PHP 8.2+
* Composer
* MySQL
* Laravel 12 compatible environment
* XAMPP / Laragon / Homestead (optional but recommended)

---

## 2️ Create a New Laravel Project

Open your terminal and run:

```bash
composer create-project laravel/laravel reliese-demo
```

Start the development server (optional check):

```bash
php artisan serve
```

---

## 3️ Configure Database

Open the `.env` file and update database settings:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=reliese_demo
DB_USERNAME=root
DB_PASSWORD=
```

Now create the database in MySQL:

```sql
CREATE DATABASE reliese_demo;
```

---

## 4️ Create Example Tables (Required for Model Generation)

Reliese generates models from existing database tables.

Create migrations:

```bash
php artisan make:migration create_categories_table

php artisan make:migration create_products_table
```

### Categories Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
```

### Products Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

Run Migrations:

```bash
php artisan migrate
```

Your database now contains:

* categories
* products
* users
* jobs
* sessions
* etc.

---

## 5️ Install Reliese Model Generator

Install the package as a development dependency:

```bash
composer require reliese/laravel --dev
```

---

## 6️ Publish Reliese Configuration

Run:

```bash
php artisan vendor:publish --tag=reliese-models
```

This creates:

```
config/models.php
```

---

## 7️ Configure models.php

Open `config/models.php` and use this recommended production-ready configuration:

```php
<?php

return [

    '*' => [

        'path' => app_path('Models'),
        'namespace' => 'App\\Models',
        'parent' => Illuminate\\Database\\Eloquent\\Model::class,
        'use' => [],
        'connection' => false,
        'timestamps' => true,
        'soft_deletes' => true,
        'date_format' => 'Y-m-d H:i:s',
        'per_page' => 15,
        'base_files' => true,
        'snake_attributes' => true,
        'indent_with_space' => 0,
        'qualified_tables' => false,
        'hidden' => [
            '*secret*', '*password', '*token',
        ],
        'guarded' => [],
        'casts' => [
            '*_json' => 'json',
        ],
        'except' => [
            'migrations',
            'failed_jobs',
            'password_resets',
            'personal_access_tokens',
            'password_reset_tokens',
        ],
        'only' => [],
        'table_prefix' => '',
        'lower_table_name_first' => false,
        'model_names' => [],
        'relation_name_strategy' => 'related',
        'with_property_constants' => false,
        'with_column_list' => false,
        'pluralize' => true,
        'override_pluralize_for' => [],
        'hidden_in_base_files' => false,
        'fillable_in_base_files' => false,
        'enable_return_types' => false,
    ],
];
```

---

## 8️ Generate Models

Run the command:

```bash
php artisan code:models
```

You will see:

```
Check out your models for reliese_demo
```

---

## 9️ Verify Generated Models

Check the Models directory (Windows):

```bash
dir app\Models
```
<img width="637" height="396" alt="Screenshot 2026-02-16 120906" src="https://github.com/user-attachments/assets/9c70a4bb-dd7b-4192-9858-d2630182ed4f" />

---

##  Understanding Generated Structure

### Base Models

Located in:

```
app/Models/Base/
```

These files contain:

* Table name
* Fillable fields
* Relationships
* Casts
* Primary keys

They are automatically regenerated.

---

### Main Models

Located in:

```
app/Models/
```

Example:

```php
<?php

namespace App\Models;

use App\Models\Base\Product as BaseProduct;

class Product extends BaseProduct
{
    protected $fillable = [
        'category_id',
        'name',
        'price'
    ];
}
```

These files are safe and will NOT be overwritten.

---

##  Regenerating Specific Table

If you modify the database later:

```bash
php artisan code:models --table=products
```

Only the Base model will update.

---

##  Optional: Disable Base Folder

If you prefer a simple structure:

Change in `config/models.php`:

```php
'base_files' => false,
```

Then regenerate:

```bash
php artisan code:models
```

Now models will be generated directly inside:

```
app/Models/
```
<img width="629" height="424" alt="Screenshot 2026-02-16 120715" src="https://github.com/user-attachments/assets/a127f773-7ab2-4b37-8f09-e4f0c84b2a39" />

---

##  Best Practice Recommendation

### For Production or Large Projects

✔ Keep base_files = true
✔ Never edit Base models
✔ Add custom logic only in main model

### For Small / Demo Projects

✔ You may set base_files = false

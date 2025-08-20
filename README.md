# Project - Laravel Filament App

<details>
<summary>📚 Technologies Used</summary>

* PHP 8.4
* Laravel 12
* Filament 3.3
* [filament/spatie-laravel-media-library-plugin 3.3](https://filamentphp.com/plugins/filament-spatie-settings)
* [laravel-shift/blueprint 2.12](https://blueprint.laravelshift.com/)

</details>

<details>
<summary>🛠 Laravel Blueprint</summary>

Laravel Blueprint is used to **generate migrations and models** from the `draft.yaml` file in the root directory.

### Usage

1. **Erase generated files**

```bash
php artisan blueprint:erase
```

Deletes all migrations, model classes, and factories.

2. **Edit `draft.yaml`** as needed

3. **Re-generate files**

```bash
php artisan blueprint:build
```

> Before running new migrations, rollback previous migrations first. Blueprint does not handle it automatically.

</details>

<details>
<summary>🔗 Entity Relationships</summary>

* **Product ↔ Category:** many-to-many
* Both entities use `softDeletes`
* Deleting an entity also deletes its pivot associations

</details>

<details>
<summary>📊 Basic overview</summary>

* Actions: **Create**, **Edit**, **Delete (softDelete)**, **Restore**, **Hard Delete**
* Click on row to view entity detail page

### Validation

* `name` and `slug` must be unique (among non-softDeleted entities)
* `name` max 50 characters
* For the product entity SKU field must be unique also, non-softDelete rule applies here also.
* All fields except `description` are required
* `active` default `false`
* SoftDeleted entity cannot be restored if one not-deleted entity exists with same `name` and `slug`

### Images

* Package used: Spatie
* Each entity can have **only one image**

</details>

<details>
<summary>🚀 Project Setup</summary>

After cloning the repository, run:

```bash
composer install
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan make:filament-user - generates a filament user
```

</details>

<details>
<summary>🌍 Localization</summary>

This project uses slovak localisation.
Set `.env` file as follows:

```
APP_LOCALE=sk
APP_FALLBACK_LOCALE=sk
APP_FAKER_LOCALE=sk_SK
```

</details>

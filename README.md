# laravel-roles-migrations

Adds migrations for [`jeremykenedy/laravel-roles`](https://github.com/jeremykenedy/laravel-roles).

## Overview

This package aims to provide migration functionality for roles and
permissions (and their relationships).

1. [Installation](#installation)
2. [Usage](#usage)
    1. [Creating Migrations](#creating-migrations)
    2. [Writing Migrations](#writing-migrations)
    3. [Examples](#examples)

## Installation

Simply require this package with composer:

```bash
composer require ricardoboss/laravel-roles-migrations
```

## Usage

### Creating Migrations

This package adds a new abstract class - `RolesMigration` - which you
can use to write migrations for your roles.

To add a new migration, simply execute

```bash
php artisan make:rolesmigration MyNewRolesMigration
```

to add a new migration (which can be found in `database/migrations/xxx_xx_xx_xxxxxx_my_new_roles_migration.php`).

Open the migration. Notice how the class `extends` the new `RolesMigration` class instead
of the default `Migration` class from Laravel.

Now comes the interesting part, writing the migrations.

### Writing Migrations

A roles migration consists of four protected arrays which define what the
migration does:

* `$permissions`: contains permission definitions which shall be added
* `$roles`: contains role definitions which shall be added
* `$toAttach`: defines which permissions shall be attached to what role
* `$toDetach`: defines which permissions shall be detached from what role

An example of each migration type and their outcome can be found here:

### Examples

#### Permissions

```php
$permissions = [
    [
        'name' => "List users",
        'slug' => "users.list", // optional
        'description' => "Permission to list all users."
    ]
];
```

The `slug` value is optional.
It will be derived from the name if missing.

When the migration is executed, the above configuration will add a new permission
to your database. This exact permission will be removed in case of a rollback.

#### Roles

```php
$roles = [
    [
        'name' => "Admin",
        'level' => 10,
        'slug' => "admin", // optional
        'description' => "A user with all available permissions." // optional
    ]
];
```

The `slug` and `description` values are optional. If `slug` is missing, it will
be derived from the name. The default value for `description` is `null`.

#### ToAttach & ToDetach

```php
$toAttach = [
    'admin' => [
        'users.list',
        // more permissions...
    ]
];
```

`$toAttach` and `$toDetach` have the same structure.
They consist of nested arrays which declare what role gets or looses which permissions.

The key of the array must be the `slug` of a role whereas the value must be an array
of permission `slug`s. You can list as many permissions as you want for each role.

> The order in which permissions are attached and detached matters:
> if you have the same configuration in both `$toAttach` and `$toDetach`,
> the outcome will be that nothing changed since the permissions are first
> attached and _then_ detached.

## Contributing

Feel free to fork the repository and create a pull request.
You are encouraged to adhere to the [PSR-12](https://www.php-fig.org/psr/psr-12/)
coding style guide.

### To-Do List

- [x] Basic migrations and rollbacks
- [x] `artisan` commands
- [ ] support for updating existing roles/permissions
- [ ] write tests

If you see a missing feature you want or find a bug, please create an issue and describe it.

## License

The source code of this package is free software and distributed under the terms of the [MIT License](https://github.com/ricardoboss/laravel-roles-migrations/blob/master/LICENSE).

---

Thanks to [jeremykenedy](https://github.com/jeremykenedy) for creating the
[`laravel-roles`](https://github.com/jeremykenedy/laravel-roles) package and of
course [taylorotwell](https://github.com/taylorotwell) for creating such an amazing
framework.

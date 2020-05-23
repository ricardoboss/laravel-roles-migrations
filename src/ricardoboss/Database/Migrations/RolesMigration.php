<?php
declare(strict_types=1);

namespace ricardoboss\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

/**
 * Class RolesMigration
 *
 * @package ricardoboss\Database\Migrations
 * @author Ricardo Boss <contact@ricardoboss.de>
 */
abstract class RolesMigration extends Migration
{
    /**
     * An array containing role definitions. A role definition is an array which contains at least these keys:
     *   - name: (string) The name of the new role
     *   - description: (string) A brief description of the role
     *   - level: (int) The level of the role relative to the other roles
     *
     * Optionally, you can add the key 'slug' which is a url-safe key for the role.
     *
     * @var mixed[][]
     */
    protected $roles = [];

    /**
     * An array of permission definitions. A permission definition is an array which contains at least these keys:
     *   - name: The name of the new permission
     *   - description: A brief description of the permission
     *
     * Optionally, you can add the key 'slug' which is a url-safe key for the permission.
     *
     * @var string[][]
     */
    protected $permissions = [];

    /**
     * A key-value map that maps which permissions shall be attached to roles.
     * The key is the role slug and the value is an array with permission slugs.
     *
     * @var string[][]
     */
    protected $toAttach = [];

    /**
     * A key-value map that maps which permissions shall be detached from roles.
     * The key is the role slug and the value is an array with permission slugs.
     *
     * @var string[][]
     */
    protected $toDetach = [];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // TODO: update existing models (levels, names, descriptions)
        $this->addPermissions();
        $this->addRoles();
        $this->syncPermissions('attachPermission', $this->toAttach);
        $this->syncPermissions('detachPermission', $this->toDetach);
    }

    /**
     * Add new permissions.
     */
    public function addPermissions(): void
    {
        foreach ($this->permissions as $permission) {
            if (!array_key_exists('slug', $permission))
                $permission['slug'] = Str::slug($permission['name'], '.');

            // do not re-create existing models
            if (config('roles.models.permission')::where('slug', $permission['slug'])->exists())
                continue;

            config('roles.models.permission')::create($permission);
        }
    }

    /**
     * Add new roles.
     */
    public function addRoles(): void
    {
        foreach ($this->roles as $role) {
            if (!array_key_exists('slug', $role))
                $role['slug'] = Str::slug($role['name'], '.');

            config('roles.models.role')::create($role);
        }
    }

    /**
     * Synchronize permissions with roles.
     *
     * @param string $method The method to call on the role. Can be either 'attachPermission' or 'detachPermission'
     * @param string[][] $toSync Permissions to sync to a role. ['role' => ['permission1', 'permission2']]
     */
    public function syncPermissions(string $method, $toSync): void
    {
        foreach ($toSync as $roleSlug => $permissionSlugs) {
            /** @var Role $role */
            $role = config('roles.models.role')::where('slug', $roleSlug)->first();
            if ($role === null)
                continue;

            foreach ($permissionSlugs as $permissionSlug) {
                /** @var Permission $permission */
                $permission = config('roles.models.permission')::where('slug', $permissionSlug)->first();
                if ($permission === null)
                    continue;

                $role->{$method}($permission->{$permission->getKeyName()});
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->syncPermissions('detachPermission', $this->toAttach);
        $this->syncPermissions('attachPermission', $this->toDetach);
        $this->removeRoles();
        $this->removePermissions();
    }

    /**
     * Remove existing roles.
     */
    public function removeRoles(): void
    {
        foreach ($this->roles as $role) {
            if (!array_key_exists('slug', $role))
                $role['slug'] = Str::slug($role['name'], '.');

            config('roles.models.role')::where('slug', $role['slug'])->forceDelete();
        }
    }

    /**
     * Remove existing permissions.
     */
    public function removePermissions(): void
    {
        foreach ($this->permissions as $permission) {
            if (!array_key_exists('slug', $permission))
                $permission['slug'] = Str::slug($permission['name'], '.');

            config('roles.models.permission')::where('slug', $permission['slug'])->forceDelete();
        }
    }
}

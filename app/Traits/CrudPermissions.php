<?php

namespace App\Traits;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Use this trait inside a crud controller and the permissions will be applied.
 */
trait CrudPermissions
{
    /**
     * This will be called automatically by backpack in order to apply the current crud permissions.
     */
    protected function setupCrudPermissionDefaults()
    {
        $this->applyCrudPermissions($this->getNameFromCrudController());
    }

    /**
     * Get an array of the permissions and role for this crud controller. (used for seeds)
     *
     * @return array
     */
    public static function getCrudPermissionsAndRole()
    {
        $instance = new self;
        $name = $instance->getNameFromCrudController();

        return ['permissions' => array_map(function ($operation) use ($name) {
            return $operation.'_'.$name;
        }, $instance->currentUsedPermission()), 'role' => 'manage_'.$name];
    }

    private function currentUsedPermission()
    {
        return array_merge(
            (new self)->additionalPermission(),
            array_values(array_filter(array_map(function ($file) {
                return lcfirst(explode('Operation', basename(explode('Controllers/Operations/', $file)[1], '.php'))[0]);
            }, glob(base_path('vendor/backpack/crud/src/app/Http/Controllers/Operations/*.php'))), function ($file) {
                return array_key_exists(
                    'Backpack\\CRUD\\app\\Http\\Controllers\\Operations\\'.ucfirst($file).'Operation',
                    class_uses(new self)
                ) && ucfirst($file) != 'Fetch' && ucfirst($file) != 'InlineCreate';
            }))
        );
    }

    /**
     * Return the name of the crud controller.
     * EX: EntityCrudController will return entity
     *
     * @return string
     */
    private function getNameFromCrudController()
    {
        return mb_strtolower(
            (new self)->permissionPrefix().
            preg_replace('/\B([A-Z])/', '_$1', explode('CrudController', class_basename(self::class))[0])
        );
    }

    /**
     * Deny access of any operation the current user doesn't have access to.
     *
     * @param string $name the name of the crud controller
     *
     * @return void
     */
    private function applyCrudPermissions($name)
    {
        $instance = new self;

        foreach ($instance->currentUsedPermission() as $operation) {
            if (! backpack_user()->can($operation.'_'.$name)) {
                CRUD::denyAccess($operation);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Customizations
    |--------------------------------------------------------------------------
    */

    /**
     * Override this function in your crud controller to add some extra permissions.
     *
     * @return array
     */
    protected function additionalPermission()
    {
        return [];
    }

    /**
     * Override this function in your crud controller to add some a prefix to the permissions.
     *
     * @return string
     */
    private function permissionPrefix()
    {
        return '';
    }
}
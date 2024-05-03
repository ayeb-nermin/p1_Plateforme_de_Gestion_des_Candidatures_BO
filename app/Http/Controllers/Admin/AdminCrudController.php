<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\PermissionManager\app\Http\Controllers\UserCrudController as BackpackUserCrudController;
use Backpack\PermissionManager\app\Models\Role;

/**
 * Class UserCrudController
 *
 * @property CrudPanel $crud
 */
class AdminCrudController extends BackpackUserCrudController
{
    public function setup()
    {
        parent::setup();

        // Do not list the SUPER ADMINS to manage them
        $this->crud->addClause(
            'whereNotIn',
            'id',
            Role::where('name', 'super.admin')
                ->first()
                ->users()
                ->pluck('id')
        );
    }

    public function setupUpdateOperation()
    {
        parent::setupUpdateOperation();

        if (CRUD::getCurrentEntry()->hasRole('super.admin')) {
            $this->crud->denyAccess('update');
        }
    }
}
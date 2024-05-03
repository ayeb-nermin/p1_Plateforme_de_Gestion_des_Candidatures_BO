<?php

namespace App\Http\Controllers\Admin;

use Backpack\PermissionManager\app\Http\Controllers\RoleCrudController as BackpackRoleCrudController;
use App\Http\Requests\RoleUpdateCrudRequest;
use App\Http\Requests\RoleStoreCrudRequest;
use Illuminate\Support\Facades\Cache;
use App\Models\RoleTranslation;
use App\Traits\CrudPermissions;
use App\Traits\Field;

/**
 * Class UserCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class RoleCrudController extends BackpackRoleCrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as traitStore;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
        update as traitUpdate;
    }
    use CrudPermissions;
    use Field;

    protected $languages;

    public function setup()
    {
        parent::setup();
        $this->crud->addClause('where', 'name', '!=', 'super.admin');
    }

    public function setupListOperation()
    {
        $this->crud->addColumn([
            'name' => 'translation',
            'label' => trans('form.role.title'),
            'type' => 'relationship',
            'attribute' => 'title',
        ]);
        /**
         * Show a column for the name of the role.
         */
        $this->crud->addColumn([
            'name'  => 'name',
            'label' => trans('backpack::permissionmanager.name'),
            'type'  => 'text',
        ]);

        /**
         * Show a column with the number of users that have that particular role.
         *
         * Note: To account for the fact that there can be thousands or millions
         * of users for a role, we did not use the `relationship_count` column,
         * but instead opted to append a fake `user_count` column to
         * the result, using Laravel's `withCount()` method.
         * That way, no users are loaded.
         */
        $this->crud->query->withCount('users');
        $this->crud->addColumn([
            'label'     => trans('form.commun.admins'),
            'type'      => 'text',
            'name'      => 'users_count',
            'wrapper'   => [
                'href' => function ($crud, $column, $entry, $related_key) {
                    return backpack_url('user?role='.$entry->getKey());
                },
            ],
            'suffix'    => ' '.trans('form.commun.admins'),
        ]);

        /**
         * In case multiple guards are used, show a column for the guard.
         */
        if (config('backpack.permissionmanager.multiple_guards')) {
            $this->crud->addColumn([
                'name'  => 'guard_name',
                'label' => trans('backpack::permissionmanager.guard_type'),
                'type'  => 'text',
            ]);
        }

        /**
         * Show the exact permissions that role has.
         */
        $this->crud->addColumn([
            // n-n relationship (with pivot table)
            'label'     => ucfirst(trans('backpack::permissionmanager.permission_plural')),
            'type'      => 'select_multiple',
            'name'      => 'permissions', // the method that defines the relationship in your Model
            'entity'    => 'permissions', // the method that defines the relationship in your Model
            'attribute' => 'translation.title', // foreign key attribute that is shown to user
            'model'     => $this->permission_model, // foreign key model
            'pivot'     => true, // on create&update, do you need to add/delete pivot table entries?
        ]);
    }

    public function setupCreateOperation()
    {
        $this->addFields();
        $this->crud->setValidation(RoleStoreCrudRequest::class);

        //otherwise, changes won't have effect
        Cache::forget('spatie.permission.cache');
    }

    public function setupUpdateOperation()
    {
        $this->addFields();
        $this->crud->setValidation(RoleUpdateCrudRequest::class);

        //otherwise, changes won't have effect
        Cache::forget('spatie.permission.cache');
    }

    private function addFields()
    {
        $translations = $this->getTranslations($this->crud->getOperation(), $this->crud->getModel(), $this->crud->getCurrentEntry());

        $languages = languages();
        foreach ($languages as $key => $value) {
            $this->crud->addFields([
                [
                    'name' => $key . '[title]',
                    'label' => trans('form.role.name'),
                    'type' => 'text',
                    'default' => ($translations) ? ($translations->translations[$key]->title ?? null) : '',
                    'tab' => $value,
                ],
            ]);
        }
        $this->crud->addField([
            'name'  => 'name',
            'label' => trans('backpack::permissionmanager.name'),
            'type'  => 'text',
        ]);

        if (config('backpack.permissionmanager.multiple_guards')) {
            $this->crud->addField([
                'name'    => 'guard_name',
                'label'   => trans('backpack::permissionmanager.guard_type'),
                'type'    => 'select_from_array',
                'options' => $this->getGuardTypes(),
            ]);
        }

        $this->crud->addField([
            'label'     => ucfirst(trans('backpack::permissionmanager.permission_plural')),
            'type'      => 'checklist',
            'name'      => 'permissions',
            'entity'    => 'permissions',
            'attribute' => 'translation.title',
            'model'     => $this->permission_model,
            'pivot'     => true,
        ]);
    }

    public function store()
    {
        $redirect = $this->traitStore();
        $id       = $this->crud->entry->id;
        if (intval($id)) {
            $this->crud->getCurrentEntry()->update(['admin_id'=>backpack_user()->id]);

            $languages = languages();
            foreach ($languages as $key => $value) {
                $data               = $this->crud->getRequest()->$key;
                $data['role_id'] = $id;
                $data['locale']     = $key;
                RoleTranslation::create($data);
            }
        }

        return $redirect;
    }

    public function update()
    {
        $redirect = $this->traitUpdate();
        $id       = $this->crud->entry->id;
        if (intval($id)) {
            $this->crud->getCurrentEntry()->update(['admin_id'=>backpack_user()->id]);
            $languages = languages();
            foreach ($languages as $key => $value) {
                $data               = $this->crud->getRequest()->$key;
                if ($translation = RoleTranslation::where(['locale' => $key, 'role_id' => $id])->first()) {
                    $translation->update($data);
                } else {
                    $data['role_id']   = $id;
                    $data['locale']     = $key;
                    RoleTranslation::create($data);
                }
            }
        }

        return $redirect;
    }
}

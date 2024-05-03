<?php

namespace App\Http\Controllers\Admin;

use Backpack\PermissionManager\app\Http\Controllers\PermissionCrudController as BackpackPermissionCrudController;
use App\Http\Requests\PermissionStoreCrudRequest;
use App\Http\Requests\PermissionUpdateCrudRequest;
use App\Models\PermissionTranslation;
use Illuminate\Support\Facades\Cache;
use App\Traits\Field;
use App\Traits\CrudPermissions;

class PermissionCrudController extends BackpackPermissionCrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as traitStore;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
        update as traitUpdate;
    }
    use CrudPermissions;
    use Field;

    public function setup()
    {
        parent::setup();
    }

    public function setupListOperation()
    {
        $this->crud->addColumn([
            'name' => 'translation',
            'label' => trans('form.permission.name'),
            'type' => 'relationship',
            'attribute' => 'title',
        ]);

        $this->crud->addColumn([
            'name'  => 'name',
            'label' => trans('backpack::permissionmanager.name'),
            'type'  => 'text',
        ]);

        if (config('backpack.permissionmanager.multiple_guards')) {
            $this->crud->addColumn([
                'name'  => 'guard_name',
                'label' => trans('backpack::permissionmanager.guard_type'),
                'type'  => 'text',
            ]);
        }
    }

    public function setupCreateOperation()
    {
        $this->addFields();
        $this->crud->setValidation(PermissionStoreCrudRequest::class);

        //otherwise, changes won't have effect
        Cache::forget('spatie.permission.cache');
    }

    public function setupUpdateOperation()
    {
        $this->addFields();
        $this->crud->setValidation(PermissionUpdateCrudRequest::class);

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
                    'label' => trans('form.permission.name'),
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
    }

    public function store()
    {
        $redirect = $this->traitStore();
        $id       = $this->crud->entry->id;
        if (intval($id)) {
            $this->crud->getCurrentEntry()->update(['admin_id' => backpack_user()->id]);
            $languages = languages();
            foreach ($languages as $key => $value) {
                $data               = $this->crud->getRequest()->$key;
                $data['permission_id'] = $id;
                $data['locale']     = $key;
                PermissionTranslation::create($data);
            }
        }

        return $redirect;
    }

    public function update()
    {
        $redirect = $this->traitUpdate();
        $id       = $this->crud->entry->id;
        if (intval($id)) {
            $this->crud->getCurrentEntry()->update(['admin_id' => backpack_user()->id]);
            $languages = languages();
            foreach ($languages as $key => $value) {
                $data               = $this->crud->getRequest()->$key;
                if ($translation = PermissionTranslation::where(['locale' => $key, 'permission_id' => $id])->first()) {
                    $translation->update($data);
                } else {
                    $data['permission_id']   = $id;
                    $data['locale']     = $key;
                    PermissionTranslation::create($data);
                }
            }
        }

        return $redirect;
    }
}

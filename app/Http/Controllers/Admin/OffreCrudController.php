<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\OffreRequest;
use App\Models\Offre;
use App\Traits\CrudPermissions;
use App\Traits\Field;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ReclamationCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class OffreCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as traitStore;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
        update as traitUpdate;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use Field;
    use CrudPermissions;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Offre::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/offre');
        CRUD::setEntityNameStrings('Offre', 'Offres');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->addColumn([
            'name' => 'title',
            'label' => 'Title',
        ]);

        $this->crud->addColumn([
            'name' => 'description',
            'label' => 'Description',
        ]);

        $this->crud->addColumn([
            'name' => 'company_name',
            'label' => 'Company Name',
        ]);
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(OffreRequest::class);

        $this->crud->addField([
            'name' => 'title',
            'label' => 'Title',
            'type' => 'text',
        ]);

        $this->crud->addField([
            'name' => 'description',
            'label' => 'Description',
            'type' => 'tinymce',
        ]);

        $this->crud->addField([
            'name' => 'company_name',
            'label' => 'Company Name',
            'type' => 'text',
        ]);
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
    protected function store()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $redirect = $this->traitStore();
        $id = $this->crud->entry->id;
        return $redirect;
    }
    protected function update()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $redirect = $this->traitUpdate();
        $id = $this->crud->entry->id;
        return $redirect;
    }
    protected function setupShowOperation()
    {
        $this->crud->set('show.setFromDb', false);
        $this->crud->addColumn([
            'name' => 'title',
            'label' => 'Title',
        ]);

        $this->crud->addColumn([
            'name' => 'description',
            'label' => 'Description',
        ]);

        $this->crud->addColumn([
            'name' => 'company_name',
            'label' => 'Company Name',
        ]);
        $this->crud->addColumn([
            'name' => 'created_at',
            'label' => 'Date de création',
        ]);
    }
}

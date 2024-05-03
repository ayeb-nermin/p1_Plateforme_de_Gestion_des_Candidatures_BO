<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use App\Http\Requests\EmailRequest;
use App\Traits\CrudPermissions;

/**
 * Class EmailCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class EmailCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use CrudPermissions;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Email::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/email');
        CRUD::setEntityNameStrings(__('form.email.singular'), __('form.email.plural'));
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('name')->label(trans('form.email.name'));
        // CRUD::column('description')->label(trans('form.email.description'));
    }

    protected function setupShowOperation()
    {
        $this->crud->set('show.setFromDb', false);

        CRUD::column('name')
            ->label(trans('form.email.name'));
        CRUD::column('description')
            ->label(trans('form.email.description'));
        CRUD::column('headers')
            ->type('table')
            ->columns([
                'header_name' => trans('form.email.headers_name'),
                'header_value' => trans('form.email.headers_value'),
            ])->label(trans('form.email.headers_name'));
        CRUD::column('template')
            ->type('custom_html')
            ->value(CRUD::getCurrentEntry()->template ?? '')
            ->label(trans('form.email.template'));
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(EmailRequest::class);

        CRUD::field('name')->type('text')->label(trans('form.email.name'));

        CRUD::field('description')->type('textarea')->label(trans('form.email.description'));

        CRUD::field('template')->type('summernote')->options([])->label(trans('form.email.template'));

        CRUD::field('headers')->type('repeatable')->new_item_label(trans('form.email.headers.add_button'))->fields([
            [
                'name' => 'header_name',
                'label' => trans('form.email.headers_name'),
                'type' => 'select2_from_array',
                'options' => [
                    'subject' => trans('form.email.headers.subject'),
                    'to' => trans('form.email.headers.to'),
                    'from' => trans('form.email.headers.from'),
                    'from_name' => trans('form.email.headers.from_name'),
                    'reply_to' => trans('form.email.headers.reply_to'),
                    'cc' => trans('form.email.headers.cc'),
                    'bcc' => trans('form.email.headers.bcc'),
                ],
                'allows_null' => false,
                'default' => 'one',
                'wrapper' => ['class' => 'form-group col-md-6'],
            ],
            [
                'name' => 'header_value',
                'label' => trans('form.email.headers_value'),
                'type' => 'text',
                'wrapper' => ['class' => 'form-group col-md-6'],
            ],
        ])->label(trans('form.email.header'));
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
}

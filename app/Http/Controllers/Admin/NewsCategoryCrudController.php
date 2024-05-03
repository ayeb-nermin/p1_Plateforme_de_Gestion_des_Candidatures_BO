<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use App\Http\Requests\NewsCategoryRequest;
use App\Models\NewsCategoryTranslation;
use App\Traits\CrudPermissions;
use App\Traits\Field;

/**
 * Class NewsCategoryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class NewsCategoryCrudController extends CrudController
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
        CRUD::setModel(\App\Models\NewsCategory::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/news-category');
        CRUD::setEntityNameStrings(__('form.news_category.singular'), __('form.news_category.plural'));
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // specific action
        $this->enableButton();

        $this->crud->addColumn([
            'name' => 'translation',
            'label' => __('form.news_category.name'), // Table column heading
            'type' => 'relationship',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('translation', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%');
                });
            }
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
        $this->crud->setValidation(NewsCategoryRequest::class);

        $translations = $this->getTranslations($this->crud->getOperation(), $this->crud->getModel(), $this->crud->getCurrentEntry());
        $languages = languages();
        foreach ($languages as $key => $value) {
            $this->crud->field($key . '[name]')
                ->label(__('form.news_category.name'))
                ->type('text')
                ->default(($translations) ? ($translations->translations[$key]->name ?? null) : '')
                ->wrapperAttributes(['class' => 'form-group col-md-12'])
                ->tab($value);

            $this->crud->field($key . '[description]')
                ->label(__('form.news_category.description'))
                ->type('tinymce')
                ->default(($translations) ? ($translations->translations[$key]->description ?? null) : '')
                ->wrapperAttributes(['class' => 'form-group col-md-12'])
                ->tab($value);
        }

        $this->crud->field('is_active')
            ->label(__('form.commun.is_active'))
            ->type('toggle');

        $this->crud->field('order')
            ->label(__('form.commun.order'))
            ->type('number');
    }
    /**
     * Store a newly created resource in the database.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $redirect = $this->traitStore();

        $this->insertTranslation(NewsCategoryTranslation::class, 'news_category_id');

        return $redirect;
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
    /**
     * Update the specified resource in the database.
     *
     * @return \Backpack\CRUD\app\Http\Controllers\Operations\Response
     */
    public function update()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $redirect = $this->traitUpdate();

        $this->updateTranslation(NewsCategoryTranslation::class, 'news_category_id');

        return $redirect;
    }

    protected function setupShowOperation()
    {
        $this->crud->set('show.setFromDb', false);

        $this->crud->addColumn([
            'label' => trans('form.news_category.name'),
            'name' => 'translation',
            'type' => 'relationship',
        ]);

        $this->crud->addColumn([
            'label' => trans('form.news_category.description'),
            'name' => 'description',
            'type' => 'relationship',
            'entity'    => 'translation',
            'attribute' => 'description',
        ]);

        $this->crud->addColumn([
            'label' => trans('form.commun.order'),
            'name' => 'order',
            'type' => 'integer',
        ]);

        $this->crud->addColumn([
            'label' => trans('form.commun.is_active'),
            'name' => 'is_active',
            'type' => 'boolean',
        ]);
    }
}

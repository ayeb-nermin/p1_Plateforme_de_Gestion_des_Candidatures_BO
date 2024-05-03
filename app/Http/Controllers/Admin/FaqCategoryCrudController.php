<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use App\Http\Requests\FaqCategoryRequest;
use App\Models\FaqCategoryTranslations;
use App\Traits\CrudPermissions;
use App\Traits\Field;

class FaqCategoryCrudController extends CrudController
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
    use CrudPermissions;
    use Field;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\FaqCategory::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/faq-category');
        CRUD::setEntityNameStrings(
            __('form.faq_category.singular'),
            __('form.faq_category.plural')
        );
    }

    protected function setupListOperation()
    {
        $this->enableButton();

        $this->crud->addColumn([
            'name' => 'translation',
            'label' => __('form.faq_category.name'), // Table column heading
            'type' => 'relationship',
            'attribute' => 'name',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('translation', function ($q) use (
                    $searchTerm
                ) {
                    $q->where('name', 'like', '%' . $searchTerm . '%');
                });
            },
        ]);
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(FaqCategoryRequest::class);

        $translations = $this->getTranslations(
            $this->crud->getOperation(),
            $this->crud->getModel(),
            $this->crud->getCurrentEntry()
        );

        $languages = languages();
        foreach ($languages as $key => $value) {
            $this->crud
                ->field($key . '[name]')
                ->label(__('form.faq_category.name'))
                ->type('text')
                ->default(
                    $translations
                        ? $translations->translations[$key]->name ?? null
                        : ''
                )
                ->wrapperAttributes(['class' => 'form-group col-md-12'])
                ->tab($value)
                ->slug_class(
                    $this->crud->getOperation() != 'update'
                        ? 'slug_' . $key
                        : ''
                );
            $this->crud
                ->field($key . '[slug]')
                ->label(__('form.faq_category.slug'))
                ->type('text')
                ->display(false)
                ->slug(true)
                ->default(
                    $translations
                        ? $translations->translations[$key]->slug ?? null
                        : ''
                )
                ->tab(__($value))
                ->attributes([
                    'class' => 'slug_' . $key . ' form-control',
                ]);
            $this->crud
                ->field($key . '[description]')
                ->label(__('form.faq_category.description'))
                ->type('tinymce')
                ->default(
                    $translations
                        ? $translations->translations[$key]->description ?? null
                        : ''
                )
                ->wrapperAttributes(['class' => 'form-group col-md-12'])
                ->tab($value);
        }

        $this->crud
            ->field('is_active')
            ->label(__('form.commun.is_active'))
            ->type('toggle');
    }

    public function store()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $redirect = $this->traitStore();

        $this->insertTranslation(FaqCategoryTranslations::class, 'faq_category_id');

        return $redirect;
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function update()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $redirect = $this->traitUpdate();

        $this->updateTranslation(FaqCategoryTranslations::class, 'faq_category_id');

        return $redirect;
    }

    protected function setupShowOperation()
    {
        $this->crud->addColumn([
            'label' => trans('form.faq_category.name'),
            'name' => 'translation',
            'type' => 'relationship',
        ]);

        $this->crud->addColumn([
            'label' => trans('form.commun.is_active'),
            'name' => 'is_active',
            'type' => 'boolean',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use App\Http\Requests\WidgetRequest;
use App\Models\WidgetTranslation;
use App\Traits\CrudPermissions;
use Illuminate\Http\Request;
use App\Traits\WidgetTrait;
use App\Models\Widget;
use App\Traits\Field;

/**
 * Class WidgetCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class WidgetCrudController extends CrudController
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
    use WidgetTrait;
    use Field;

    protected $modelName;
    protected $modelNameSpace;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Widget::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/widget');
        CRUD::setEntityNameStrings(__('form.widget.singular'), __('form.widget.plural'));

        if ($this->crud->getOperation() == 'update') {
            if (CRUD::getCurrentEntry()->select_type == 'free_select') {
                $moduleReference = CRUD::getCurrentEntry()->module_reference;
                $this->modelName = (config()->has('cms.modules.' . $moduleReference) && in_array(config('cms.modules.' . $moduleReference . '.reference'), get_modules_that_has_elements())) ? config('cms.modules.' . $moduleReference . '.model_name') : '';
                if ($this->modelName) {
                    $this->modelNameSpace = "\App\Models\\" . $this->modelName;
                }
            }
        }
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
            'label' => __('form.widget.title'),
            'type' => 'relationship',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('translation', function ($q) use ($searchTerm) {
                    $q->where('title', 'like', '%' . $searchTerm . '%');
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
        $this->crud->setValidation(WidgetRequest::class);

        $translations = $this->getTranslations($this->crud->getOperation(), $this->crud->getModel(), $this->crud->getCurrentEntry());

        $languages = languages();

        foreach ($languages as $key => $value) {
            $this->crud->field($key . '[title]')
                ->label(__('form.widget.title'))
                ->type('text')
                ->default(($translations) ? ($translations->translations[$key]->title ?? null) : '')
                ->tab($value);

            $this->crud->field($key . '[description]')
                ->label(__('form.widget.description'))
                ->type('textarea')
                ->default(($translations) ? ($translations->translations[$key]->description ?? null) : '')
                ->tab($value);

            $this->crud->field($key . '[button_title]')
                ->label(__('form.widget.button_title'))
                ->type('text')
                ->default(($translations) ? ($translations->translations[$key]->button_title ?? null) : '')
                ->tab($value);
        }

        $this->crud->field('is_active')
            ->label(__('form.commun.is_active'))
            ->type('toggle')
            ->tab(__('form.commun.general_information'));

        $this->crud->field('reference')
            ->type('hidden')
            ->value(time())
            ->tab(__('form.commun.general_information'));

        $this->crud->field('home_reference')
            ->type('hidden')
            ->value(null)
            ->tab(__('form.commun.general_information'));

        $this->crud->field('module_reference')
            ->label(__('form.widget.module_reference'))
            ->type('select2_from_array_widget_columns')
            ->options($this->getModules())
            ->allows_null(false)
            ->tab(__('form.commun.general_information'));

        $this->crud->field('order_column')
            ->label(__('form.widget.order_column'))
            ->type('select2_from_array')
            ->options([])
            ->allows_null(false)
            ->tab(__('form.commun.general_information'));

        $this->crud->field('order_column_type')
            ->label(__('form.widget.order_column_type'))
            ->type('select2_from_array')
            ->options($this->getOrderColumnType())
            ->allows_null(false)
            ->tab(__('form.commun.general_information'));

        $this->crud->field('select_type')
            ->label(__('form.widget.select_type'))
            ->type('select2_from_array_select_type')
            ->options($this->getSelectType())
            ->allows_null(false)
            ->tab(__('form.commun.general_information'));

        $this->crud->field('number_for_latest')
            ->label(__('form.widget.number_for_latest'))
            ->type('number')
            ->tab(__('form.commun.general_information'));

        $this->crud->field('order')
            ->label(__('form.commun.order'))
            ->type('number')
            ->tab(__('form.commun.general_information'));

        $this->crud->addFields([
            [
                'name' => 'type',
                'label' => __('form.banner.content_type'),
                'type' => 'menu_type',
                'except' => 3,
                'tab' => __('form.commun.general_information')
            ]
        ]);

        if ($this->crud->getOperation() == 'update' && $this->modelName) {
            $this->crud->field('items')
                ->label(__('form.widget.items'))
                ->type('select_and_order')
                ->options($this->getOptionsByModel($this->modelName, $this->modelNameSpace))
                ->select_all(true)
                ->label_selected(__('form.widget.selected'))
                ->value($this->crud->entry->elements($this->modelName)->get()->pluck('id')->toArray())
                ->tab(__('form.tabs.template'));
        }
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

        $this->insertTranslation(WidgetTranslation::class, 'widget_id');

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

        $this->updateTranslation(WidgetTranslation::class, 'widget_id');

        $this->syncWidgetAndMenuElements($this->modelName, $this->modelNameSpace);

        return $redirect;
    }

    protected function setupShowOperation()
    {
        $widget = Widget::find(request()->route('id'));
        $this->crud->set('show.setFromDb', false);

        $this->crud->addColumn([
            'label' => trans('form.widget.title'),
            'name' => 'translation',
            'type' => 'relationship',
            'attribute' => 'title',
        ]);

        $this->crud->addColumn([
            'label' => trans('form.widget.description'),
            'name' => 'description',
            'type' => 'relationship',
            'entity'    => 'translation',
            'attribute' => 'description',
        ]);

        $this->crud->addColumn([
            'label' => trans('form.widget.button_title'),
            'name' => 'button_title',
            'type' => 'relationship',
            'entity'    => 'translation',
            'attribute' => 'button_title',
        ]);


        $this->crud->column('reference')->label(__('form.widget.reference'));

        $this->crud->column('home_reference')->label(__('form.widget.home_reference'));

        $this->crud->column('module_reference')
            ->label(__('form.widget.module_reference'))
            ->type('select_from_array')
            ->options($this->getModules());

        $this->crud->column('order_column')
            ->label(__('form.widget.order_column'))
            ->type('select_from_array')
            ->options($this->getOrderColumn($widget->module_reference));

        $this->crud->column('order_column_type')
            ->label(__('form.widget.order_column_type'))
            ->type('select_from_array')
            ->options($this->getOrderColumnType());

        $this->crud->column('select_type')
            ->label(__('form.widget.select_type'))
            ->type('select_from_array')
            ->options($this->getSelectType());

        if ($widget->select_type == 'latest') {
            $this->crud->column('number_for_latest')
                ->label(__('form.widget.number_for_latest'))
                ->type('number');
        }

        $this->crud->column('order')
            ->label(__('form.commun.order'))
            ->type('number');

        $this->crud->addColumn([
            'label' => trans('form.commun.is_active'),
            'name' => 'is_active',
            'type' => 'boolean',
        ]);
    }

    public function getWidgetColumnsByModuleReference(Request $request)
    {
        if ($request->reference) {
            return $this->getOrderColumn($request->reference);
        }

        return [];
    }
}

<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use App\Http\Requests\MenuRequest;
use App\Models\MenuTranslation;
use App\Traits\Field;
use App\Models\Menu;

/**
 * Class MenuCrudController
 *
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MenuCrudController extends CrudController
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
    use \Backpack\CRUD\app\Http\Controllers\Operations\ReorderOperation;
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
        $this->crud->setModel(Menu::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/menu');
        $this->crud->setEntityNameStrings(__('form.menu.singular'), __('form.menu.plural'));
        $this->crud->set('reorder.max_level', 4);
        $this->crud->set('reorder.label', 'translation.title');
        $this->crud->orderBy('lft');

        if ($this->crud->getOperation() == 'update') {
            $moduleReference = CRUD::getCurrentEntry()->module_reference;
            $this->modelName = (config()->has('cms.modules.' . $moduleReference) && in_array(config('cms.modules.'.$moduleReference . '.reference'), get_modules_that_has_elements())) ? config('cms.modules.'.$moduleReference.'.model_name') : '';
            if ($this->modelName) {
                $this->modelNameSpace = "\App\Models\\" . ucfirst($this->modelName);
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
        $this->crud->addColumn([
            'name' => 'id',
            'label' => '#ID',
        ]);
        $this->crud->addColumn([
            'name' => 'translation',
            'label' => __('form.menu.title'), // Table column heading
            'type' => 'relationship',
            'attribute' => 'title',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('translation', function ($q) use ($searchTerm) {
                    $q->where('title', 'like', '%' . $searchTerm . '%');
                });
            }
        ]);
        $this->crud->addColumn([
            'name' => 'parent.translation',
            'label' => __('form.menu.parent'), // Table column heading
            'type' => 'relationship',
            'attribute' => 'title',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('translation', function ($q) use ($searchTerm) {
                    $q->where('title', 'like', '%' . $searchTerm . '%');
                });
            }
        ]);

        $this->enableButton();
        $this->crud->addButtonFromModelFunction('line', __('form.menu.type'), 'getMenuType', 'end');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        $this->crud->setValidation(MenuRequest::class);


        $translations = $this->getTranslations($this->crud->getOperation(), $this->crud->getModel(), $this->crud->getCurrentEntry());
        $languages = languages();
        foreach ($languages as $key => $value) {
            $this->crud->field($key . '[title]')
                ->label(__('form.menu.title'))
                ->type('text')
                ->default(($translations) ? ($translations->translations[$key]->title ?? null) : '')
                ->tab(__($value))
                ->slug_class(($this->crud->getOperation() != 'update') ? 'slug_' . $key : '');

            $this->crud->field($key . '[slug]')
                ->label(__('form.menu.slug'))
                ->type('text')
                ->display(false)
                ->slug(true)
                ->default(($translations) ? ($translations->translations[$key]->slug ?? null) : '')
                ->tab(__($value))
                ->attributes([
                    'class' => 'slug_' . $key . ' form-control',
                ]);

            $this->crud->field($key . '[description]')
                ->label(__('form.menu.description'))
                ->type('tinymce')
                ->default(($translations) ? ($translations->translations[$key]->description ?? null) : '')
                ->tab(__($value))
                ->options($this->tinyMceOption());

            $this->crud->field($key . '[content]')
                ->label(__('form.menu.content'))
                ->type('tinymce')
                ->default(($translations) ? ($translations->translations[$key]->content ?? null) : '')
                ->tab(__($value))
                ->options($this->tinyMceOption());

            $this->crud->field($key . '[meta_title]')
                ->label(__('form.commun.meta_title'))
                ->type('text')
                ->default(($translations) ? ($translations->translations[$key]->meta_title ?? null) : '')
                ->tab(__($value));

            $this->crud->field($key . '[meta_description]')
                ->label(__('form.commun.meta_description'))
                ->type('textarea')
                ->default(($translations) ? ($translations->translations[$key]->meta_description ?? null) : '')
                ->tab(__($value));
        }

        $this->crud->field('is_active')
            ->label(__('form.commun.is_active'))
            ->type('toggle')
            ->tab(__('form.tabs.general_information'));

        if ($this->crud->getOperation() == 'update') {
            $options = Menu::where('is_active', 1)->whereNotIn('id', array_merge($this->crud->entry->children->pluck('id')->toArray(), [$this->crud->entry->id]))->with('translation')->get()->pluck('translation.title', 'id');

            $this->crud->field('parent_id')
                ->label(__('form.menu.parent'))
                ->type('select2_from_array')
                ->entity('parent')
                ->attribute('name')
                ->options($options)
                ->model(Menu::class)
                ->wrapperAttributes(['class' => 'form-group col-md-6'])
                ->tab(__('form.tabs.general_information'));
        } else {
            $options = Menu::where('is_active', 1)->with('translation')->get()->pluck('translation.title', 'id');

            $this->crud->field('parent_id')
                ->label(__('form.menu.parent'))
                ->type('select2_from_array')
                ->entity('parent')
                ->attribute('name')
                ->options($options)
                ->model(Menu::class)
                ->wrapperAttributes(['class' => 'form-group col-md-6'])
                ->tab(__('form.tabs.general_information'));
        }

        $this->crud->field('menu_zone_id')
            ->label(__('form.menu.zone'))
            ->type('select_from_array')
            ->options($this->getZoneMenuName())
            ->wrapperAttributes(['class' => 'form-group col-md-6'])
            ->tab(__('form.tabs.general_information'));

        $this->crud->field('type')
            ->label(__('form.menu.content_type'))
            ->type('menu_type')
            ->tab(__('form.tabs.general_information'));

        if ($this->crud->getOperation() == 'update' && $this->modelName) {
            $this->crud->field('items')
                ->label(__('form.menu.items'))
                ->type('select_and_order')
                ->options($this->getOptions())
                ->select_all(true)
                ->label_selected(__('form.menu.selected'))
                ->value($this->crud->entry->elements($this->modelName)->get()->pluck('id')->toArray())
                ->tab(__('form.tabs.template'));
        }
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

    public function store()
    {
        $redirect = $this->traitStore();
        $id = $this->crud->entry->id;
        if (intval($id)) {
            $this->updateMenu($id);
            $languages = languages();
            foreach ($languages as $key => $value) {
                $data = $this->crud->getRequest()->$key;
                $data['menu_id'] = $id;
                $data['locale'] = $key;
                MenuTranslation::create($data);
            }
        }

        return $redirect;
    }

    public function update()
    {
        $redirect = $this->traitUpdate();
        $id = $this->crud->entry->id;
        if (intval($id)) {
            $this->updateMenu($id);
            $languages = languages();
            foreach ($languages as $key => $value) {
                $data = $this->crud->getRequest()->$key;
                if ($translation = MenuTranslation::where(['locale' => $key, 'menu_id' => $id])->first()) {
                    $translation->update($data);
                } else {
                    $data['menu_id'] = $id;
                    $data['locale'] = $key;
                    MenuTranslation::create($data);
                }
            }
        }

        if ($this->modelName) {
            $items = [];
            if ($this->crud->getRequest()->items) {
                foreach ($this->crud->getRequest()->items as $order => $item) {
                    $items[$item] = [
                        'model' => $this->modelNameSpace,
                        'order' => $order,
                    ];
                }
            }
            $this->crud->entry->elements($this->modelName)->sync($items);
        }

        return $redirect;
    }

    public function setupShowOperation()
    {
        $this->crud->set('show.setFromDb', false);

        $this->crud->addColumn([
            'name' => 'id',
            'label' => '#ID',
        ]);

        $this->crud->addColumn([
            'name' => 'title',
            'label' => __('form.menu.title'),
            'type' => 'relationship',
            'entity' => 'translation',
            'attribute' => 'title',
        ]);

        $this->crud->addColumn([
            'name' => 'description',
            'label' => __('form.menu.description'),
            'type' => 'relationship',
            'entity' => 'translation',
            'attribute' => 'description',
        ]);

        $this->crud->addColumn([
            'name' => 'content',
            'label' => __('form.menu.content'),
            'type' => 'relationship',
            'entity' => 'translation',
            'attribute' => 'content',
        ]);

        $this->crud->addColumn([
            'name' => 'meta_title',
            'label' => __('form.menu.meta_title'),
            'type' => 'relationship',
            'entity' => 'translation',
            'attribute' => 'meta_title',
        ]);

        $this->crud->addColumn([
            'name' => 'meta_description',
            'label' => __('form.menu.meta_description'),
            'type' => 'relationship',
            'entity' => 'translation',
            'attribute' => 'meta_description',
        ]);

        $this->crud->addColumn([
            'name' => 'parent.translation',
            'label' => __('form.menu.parent'),
            'type' => 'relationship',
            'attribute' => 'title',
        ]);

        $this->crud->addColumn([
            'name' => 'menu_zone_id',
            'label' => trans('form.menu.zone'),
            'type' => 'select_from_array',
            'options' => $this->getZoneMenuName(),
        ]);

        $this->crud->addColumn([
            'name' => 'is_active',
            'label' => trans('form.commun.is_active'),
            'type' => 'boolean',
        ]);
    }

    public function updateMenu($id)
    {
        // TODO check why menu_id, link not saved
        $menu = Menu::find($id);
        $data = [];
        switch ($menu->type) {
            case 1:
                $data['internal_link'] = $this->crud->getRequest()->internal_link;
                $data['external_link'] = null;
                $data['module_reference'] = null;
                break;
            case 2:
                $data['external_link'] = $this->crud->getRequest()->external_link;
                $data['internal_link'] = null;
                $data['module_reference'] = null;
                break;
            case 3:
                $data['module_reference'] = $this->crud->getRequest()->module_reference;
                $data['internal_link'] = null;
                $data['external_link'] = null;
                break;
        }
        $data['target'] = $this->crud->getRequest()->target;
        $menu->update($data);
    }

    private function getOptions()
    {
        if ($this->modelName) {
            $modelNameSpace = $this->modelNameSpace . 'Translation';
            return $modelNameSpace::whereHas(strtolower($this->modelName), function ($q) {
                $q->where('is_active', 1);
            })->get()->pluck('title', strtolower($this->modelName) . '_id');
        }

        return [];
    }
}

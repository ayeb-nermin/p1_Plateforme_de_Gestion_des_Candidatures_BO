<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use App\Http\Requests\BannerRequest;
use App\Models\BannerTranslation;
use App\Models\MenuTranslation;
use App\Models\Banner;
use App\Traits\Field;

/**
 * Class BannerCrudController
 *
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class BannerCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as originalStore;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
        update as originalUpdate;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use Field;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(Banner::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/banner');
        CRUD::setEntityNameStrings(
            __('form.banner.singular'),
            __('form.banner.plural')
        );
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::addColumn([
            'name' => 'id',
            'label' => '#ID',
        ]);

        CRUD::addColumn([
            'name' => 'translation',
            'label' => __('form.banner.title'),
            'type' => 'relationship',
            'attribute' => 'admin_title',
        ]);

        CRUD::addColumn([
            'label' => __('form.banner.button_title'),
            'type' => 'relationship',
            'entity' => 'translation',
            'model' => MenuTranslation::class,
            'attribute' => 'button_title',
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
        CRUD::setValidation(BannerRequest::class);

        $translations = $this->getTranslations(
            CRUD::getOperation(),
            CRUD::getModel(),
            CRUD::getCurrentEntry()
        );

        $languages = languages();
        foreach ($languages as $key => $value) {
            $this->crud
                ->field($key . '[admin_title]')
                ->label(__('form.banner.admin_title'))
                ->type('text')
                ->default(
                    $translations
                        ? $translations->translations[$key]->admin_title ?? null
                        : ''
                )
                ->tab(__('form.tabs.' . $key));

            $this->crud
                ->field($key . '[wording]')
                ->label(__('form.banner.wording'))
                ->type('text')
                ->default(
                    $translations
                        ? $translations->translations[$key]->wording ?? null
                        : ''
                )
                ->tab(__('form.tabs.' . $key));

            $this->crud
                ->field($key . '[title]')
                ->label(__('form.banner.title'))
                ->type('text')
                ->default(
                    $translations
                        ? $translations->translations[$key]->title ?? null
                        : ''
                )
                ->tab(__('form.tabs.' . $key));

            $this->crud
                ->field($key . '[button_title]')
                ->label(__('form.banner.button_title'))
                ->type('text')
                ->default(
                    $translations
                        ? $translations->translations[$key]->button_title ??
                        null
                        : ''
                )
                ->tab(__('form.tabs.' . $key));

            $this->crud
                ->field($key . '[description]')
                ->label(__('form.banner.description'))
                ->type('tinymce')
                ->options($this->tinyMceOption())
                ->default(
                    $translations
                        ? $translations->translations[$key]->description ?? null
                        : ''
                )
                ->tab(__('form.tabs.' . $key));
        }

        $this->crud->addFields([
            [
                'name' => 'type',
                'label' => __('form.banner.content_type'),
                'type' => 'menu_type',
                'tab' => __('form.tabs.general_information'),
                'except' => 3, // type code content == 3 //todo
            ],
            [
                'name' => 'image',
                'label' => __('form.banner.image'),
                'type' => 'browse',
                'tab' => __('form.tabs.general_information'),
            ],
            [
                'name' => 'image2',
                'label' => __('form.banner.image2'),
                'type' => 'browse',
                'tab' => __('form.tabs.general_information'),
            ],
            [
                'name' => 'video_type',
                'label' => __('form.banner.video_type'),
                'type' => 'select_from_array_select_video',
                'options' => $this->getVideoType(),
                'wrapper' => ['class' => 'form-group col-md-6'],
                'tab' => __('form.tabs.general_information'),
            ],
            [
                'name' => 'video',
                'label' => __('form.banner.video'),
                'type' => 'browse',
                'tab' => __('form.tabs.general_information'),
            ],
            [
                'name' => 'video_link',
                'label' => __('form.banner.video_link'),
                'type' => 'url',
                'tab' => __('form.tabs.general_information'),
            ],
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

    public function store()
    {
        $redirect = $this->originalStore();

        $id = $this->crud->entry->id;
        if (intval($id)) {
            $this->updateBanner($id);
            $this->insertTranslation(BannerTranslation::class, 'banner_id');
        }

        return $redirect;
    }

    public function update()
    {
        $redirect = $this->originalUpdate();
        $id = $this->crud->entry->id;
        if (intval($id)) {
            $this->updateBanner($id);
            $this->updateTranslation(BannerTranslation::class, 'banner_id');
        }

        return $redirect;
    }

    public function setupShowOperation()
    {
        return $this->setupListOperation();
    }

    public function updateBanner($id)
    {
        // TODO check why menu_id, link not saved
        $menu = Banner::find($id);

        $data = [];

        switch ($menu->type) {
            case 1:
                if ($this->crud->getRequest()->internal_link == -1) {
                    $data[
                    'internal_link_text'
                    ] = $this->crud->getRequest()->internal_link_text;
                } else {
                    $data['internal_link_text'] = null;
                }
                $data[
                'internal_link'
                ] = $this->crud->getRequest()->internal_link;
                $data['external_link'] = null;
                $data['target'] = $this->crud->getRequest()->target;
                break;
            case 2:
                $data[
                'external_link'
                ] = $this->crud->getRequest()->external_link;
                $data['internal_link'] = null;
                $data['target'] = $this->crud->getRequest()->target;
                break;
        }

        $menu->update($data);
    }
}

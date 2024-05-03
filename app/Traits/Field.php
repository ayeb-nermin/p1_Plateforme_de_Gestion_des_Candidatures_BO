<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait Field
{
    /**
     * @return array
     */
    public function getStatusesList()
    {
        return [
            0 => __('form.commun.status.deactivate'),
            1 => __('form.commun.status.activate'),
        ];
    }

    /**
     * get translations model
     *
     * @param $operation
     * @param $model
     * @param $id
     * @return null
     */
    public function getTranslations($operation, $model, $currentModel)
    {
        $dataModel = null;
        if ($operation == 'update' && $currentModel) {
            $dataModel = $model::where('id', $currentModel->id)->with('translations')->first();
            if (optional($dataModel)->translations) {
                // dd($dataModel->translations->toArray());
                $dataModel->setRelation('translations', $dataModel->translations->keyBy('locale'));
            } else {
                $dataModel = null;
            }
        }

        return ($dataModel !== null && $dataModel->translations->isNotEmpty()) ? $dataModel : null;
    }

    public function enableButton()
    {
        return $this->crud->addButtonFromView('line', __('form.commun.status.name'), 'btn_enable', 'beginning');
    }

    /**
     * activate or deactivate
     *
     * @param null $id
     * @param null $state
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function enable($id = null, $status = null)
    {
        if (request()->ajax()) {
            $model = $this->crud->getModel();
            if (!$item = $model::find($id)) {
                return response()->json([
                    'status' => false,
                    'title' => __('form.commun.title_error_operation_popup'),
                    'message' => __('form.commun.text_error_operation_popup_2'),
                ]);
            }

            $item->update([
                "is_active" => $status,
            ]);

            return response()->json([
                'status' => true,
                'title' => __('form.commun.title_success_operation_popup'),
                'message' => __('form.commun.text_success_operation_popup'),
            ]);
        }

        return redirect()->to(backpack_url());
    }

    /**
     * @param $query
     * @return mixed
     */
    public function selectOption($query, $status = true)
    {
        $table = $query->getModel()->getTable();
        $foreignKey = Str::singular($table);
        $tableTranslation = $foreignKey . '_translations';
        $where = [
            'locale' => locale(),
            'is_active' => 1,
        ];
        if (!$status) {
            $where = [
                'locale' => locale(),
            ];
        }

        return $query->select('name', $table . '.id as id')
            ->join($tableTranslation, $table . '.id', '=', $tableTranslation . '.' . $foreignKey . '_id')
            ->where($where)->orderBy($table . '.id', 'ASC')->get();
    }

    /**
     * @return array
     */
    public function tinyMceOption()
    {
        return [
            'plugins' => 'image,link,media,anchor,code,emoticons,table,searchreplace,preview',
            'valid_elements' => '*[*]',
            'toolbar' => 'image|link|media|anchor|code|emoticons|table|searchreplace|preview|bold italic underline strikethrough',
            'content_css ' => public_path('assets/css/style.css'),
            'relative_urls' => false,
            'remove_script_host' => false,
        ];
    }

    public function getZoneMenuName()
    {
        $zones = [];
        foreach (config('menu_zones.zones') as $reference => $zone) {
            $zones[config('menu_zones.zones.' . $reference)] = __('form.menu_zones.' . $reference);
        }

        return $zones;
    }

    /**
     * @return array
     */
    public function getVideoType()
    {
        return [
            1 => __('form.banner.url'),
            2 => __('form.banner.file'),
        ];
    }

    public function syncElements($relation_function_name)
    {
        $entry = $this->crud->entry;
        $entry_items = $this->crud->getRequest()->{$relation_function_name};

        if (isset($entry_items) && !empty($entry_items)) {
            $entry->{$relation_function_name}()->sync($entry_items);
        }
    }

    public function insertTranslation($model, $foreignKey)
    {
        $id = $this->crud->entry->id;

        $languages = languages();
        foreach ($languages as $key => $value) {
            $data = $this->crud->getRequest()->$key;
            $data[$foreignKey] = $id;
            $data['locale'] = $key;

            $model::create($data);
        }
    }

    public function updateTranslation($model, $foreignKey)
    {
        $id = $this->crud->entry->id;

        $languages = languages();
        foreach ($languages as $key => $value) {
            if ($translation = $model::where(['locale' => $key, $foreignKey => $id])->first()) {
                $translation->update(request($key));
            } else {
                $data = $this->crud->getRequest()->$key;
                $data[$foreignKey] = $id;
                $data['locale'] = $key;

                $model::create($data);
            }
        }
    }

    public function getOptionsByModel($modelName, $modelNameSpace)
    {
        if ($modelName) {
            $modelNameSpace = $modelNameSpace . 'Translation';
            return $modelNameSpace::whereHas(strtolower($modelName), function ($q) {
                $q->where('is_active', 1);
            })->get()->pluck('title', strtolower($modelName) . '_id');
        }

        return [];
    }

    public function syncMenuElements()
    {
        $items = [];
        $moduleName = explode('admin/', $this->crud->getRoute())[1];
        $modelNameSpace = "\App\Models\\" . ucfirst($moduleName);

        if ($this->crud->getRequest()->items) {
            foreach ($this->crud->getRequest()->items as $order => $item) {
                $items[$item] = [
                    'model' => $modelNameSpace,
                    'order' => $order,
                ];
            }
        }

        $this->crud->entry->menus()->wherePivot('model', $modelNameSpace)->sync($items);
    }

    public function syncWidgetAndMenuElements($modelName, $modelNameSpace)
    {
        if ($modelName) {
            $items = [];
            if ($this->crud->getRequest()->items) {
                foreach ($this->crud->getRequest()->items as $order => $item) {
                    $items[$item] = [
                        'model' => $modelNameSpace,
                        'order' => $order,
                    ];
                }
            }

            $this->crud->entry->elements($modelName)->sync($items);
        }
    }
}

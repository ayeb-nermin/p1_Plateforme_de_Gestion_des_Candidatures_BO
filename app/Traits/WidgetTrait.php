<?php

namespace App\Traits;

trait WidgetTrait
{
    public function getHomes()
    {
        return [
            \App\Models\Widget::HOME_PAGE => __('module.home_page'),
        ];
    }

    public function getModules()
    {
        $references = [];

        foreach (config('cms.modules') as $module) {
            if ($module['used_in_widget'] == true && $module['is_active'] == true) {
                $references[$module['reference']] = __('module.' . $module['reference'].'.module_name');
            }
        }

        return $references;
    }

    public function getOrderColumnType()
    {
        return [
            'asc' => __('module.ascending'),
            'desc' => __('module.descending'),
        ];
    }

    public function getSelectType()
    {
        return [
            'latest' => __('module.latest'),
            'free_select' => __('module.free_select'),
        ];
    }

    public function getOrderColumn($reference) {
        if (config()->has('cms.modules.' . $reference)) {
            $data = json_decode(config('cms.modules.' . $reference . '.widget_orderable_columns'), true);
            $returnData = [];

            foreach ($data as $key => $value) {
                $returnData[$key] = __('module.' . $reference . '.' . $key);
            }

            return $returnData;
        }

        return [];
    }
}

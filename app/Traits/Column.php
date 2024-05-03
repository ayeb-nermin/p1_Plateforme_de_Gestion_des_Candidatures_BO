<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait Column
{
    public function getNameArrayTypeRelationship($model, $column = 'name') {
        $result = [];
        if(is_array($this->$column)) {
            foreach($this->$column as $id) {
                if($data = $model::where(['status'=>1, 'id'=>$id])->with('translation')->first()) {
                    @array_push($result, $data->translation->name);
                }
            }
        } elseif($data = $model::where(['status'=>1, 'id'=>$this->$column])->with('translation')->first()) {
            array_push($result, $data->translation->name);;
        }

        return (count($result)?implode(', ', $result):'');
    }
}

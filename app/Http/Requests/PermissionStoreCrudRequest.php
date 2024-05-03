<?php

namespace App\Http\Requests;

use App\Models\PermissionTranslation;
use Illuminate\Foundation\Http\FormRequest;

class PermissionStoreCrudRequest extends FormRequest
{
    
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules     = [];
        $languages = languages();
        foreach($languages as $key => $name) {
            $rules[$key.'.title'] = 'required|min:2|max:255';
        }
        
        $rules['name'] = 'required|string|max:255|unique:'.config('permission.table_names.permissions', 'permissions').',name';
        
        return $rules;
    }
    
    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];
        $languages = languages();
        foreach($languages as $key => $name) {
            $attributes[$key.'.title'] = trans('form.permission.name').' '.trans('form.commun.in_langue').' '.trans('backpack::bh.tabs.'.$key);
        }
        
        return $attributes;
    }
    
    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            //
        ];
    }
}
<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;

class NewsCategoryRequest extends FormRequest
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
        $rules = [
            'is_active' => 'required',
            // 'order' => 'required',
        ];

        $languages = languages();
        foreach($languages as $key => $name) {
            $rules[$key.'.name'] = 'required|min:2|max:255';
        }

        return $rules;
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [
            'is_active' => __('form.commun.is_active'),
        ];

        $language = default_language();
        $attributes[$language.'.name'] = trans('form.news_category.name').' '.trans('form.commun.in_langue').' '.trans('form.tabs.'.$language);

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

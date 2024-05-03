<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WidgetRequest extends FormRequest
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
            'module_reference' => 'required',
            'order_column' => 'required',
            'order_column_type' => 'required',
            'select_type' => 'required',
            'number_for_latest' => 'required_if:select_type,latest',
            'order' => 'required',
        ];

        $language = default_language();
        $rules[$language.'.title'] = 'required';

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
            'module_reference' => __('form.widget.module_reference'),
            'order_column' => __('form.widget.order_column'),
            'order_column_type' => __('form.widget.order_column_type'),
            'select_type' => __('form.widget.select_type'),
            'number_for_latest' => __('form.widget.number_for_latest'),
            'order' => __('form.commun.order'),
        ];

        $language = default_language();
        $attributes[$language.'.title'] = trans('form.widget.title').' '.trans('form.commun.in_langue').' '.trans('form.tabs.'.$language);

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

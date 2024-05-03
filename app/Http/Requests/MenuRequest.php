<?php

namespace App\Http\Requests;

use App\Models\MenuTranslation;
use Illuminate\Foundation\Http\FormRequest;

class MenuRequest extends FormRequest
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
        $id = request()->id;
        $language = default_language();
        $rules[$language.'.title'] = 'required|min:2|max:255';
        $rules[$language.'.slug']  = 'required|min:2|max:255|unique:'.(new MenuTranslation())->getTable().',slug'.($id ? ','.$id : '').',menu_id';



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
        $language = default_language();
        $attributes[$language.'.title'] = trans('form.menu.title').' '.trans('form.commun.in_langue').' '.trans('form.tabs.'.$language);
        $attributes[$language.'.slug']  = trans('form.menu.slug').' '.trans('form.commun.in_langue').' '.trans('form.tabs.'.$language);

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

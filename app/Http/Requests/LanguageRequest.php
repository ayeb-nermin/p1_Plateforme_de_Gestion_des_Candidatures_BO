<?php

namespace App\Http\Requests;

use Backpack\LangFileManager\app\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

class LanguageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow creates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|min:3|max:255',
            'abbr' => 'required|max:2|max:2|unique:'.(new Language())->getTable().(request()->has('id') ? ',abbr,'.request()->id.',id' : ''),
        ];
    }
}

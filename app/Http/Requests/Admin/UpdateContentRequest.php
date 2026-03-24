<?php



namespace App\Http\Requests\Admin;



use Illuminate\Foundation\Http\FormRequest;



class UpdateContentRequest extends FormRequest

{

    public function authorize()

    {

        return true;

    }



   public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'title_translation' => 'required|string|max:255',

            'description' => 'required|string',
            'description_translation' => 'required|string',
        ];
    }


    public function messages()
    {
        return [
            'title.required' => 'The title field is required.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title may not be greater than 255 characters.',

            'title_translation.required' => 'The German title field is required.',
            'title_translation.string' => 'The German title must be a string.',
            'title_translation.max' => 'The German title may not be greater than 255 characters.',

            'description.required' => 'The description field is required.',
            'description.string' => 'The description must be a string.',

            'description_translation.required' => 'The German description field is required.',
            'description_translation.string' => 'The German description must be a string.',
        ];
    }

}


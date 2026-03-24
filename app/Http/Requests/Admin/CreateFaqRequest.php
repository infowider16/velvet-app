<?php



namespace App\Http\Requests\Admin;



use Illuminate\Foundation\Http\FormRequest;



class CreateFaqRequest extends FormRequest

{

    public function authorize()

    {

        return true;

    }



    public function rules()

    {

        return [

            'question' => 'required|string|max:255',

            'answer' => 'required|string',

            'sort_order' => 'nullable|integer|min:0'

        ];

    }



    public function messages()

    {

        return [

            'question.required' => 'The question field is required.',

            'question.string' => 'The question must be a string.',

            'question.max' => 'The question may not be greater than 255 characters.',

            'answer.required' => 'The answer field is required.',

            'answer.string' => 'The answer must be a string.',

            'sort_order.integer' => 'The sort order must be an integer.',

            'sort_order.min' => 'The sort order must be at least 0.',

        ];

    }

}


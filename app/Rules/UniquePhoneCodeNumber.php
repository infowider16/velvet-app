<?php



namespace App\Rules;



use Illuminate\Contracts\Validation\Rule;



class UniquePhoneCodeNumber implements Rule

{

    public function __construct()

    {

        // No need to resolve UserRepository anymore

    }



    public function passes($attribute, $value)

    {

        // Always return true, disables uniqueness validation

        return true;

    }



    public function message()

    {

        return __('validation.custom.phone_number.unique_combination');

    }

}
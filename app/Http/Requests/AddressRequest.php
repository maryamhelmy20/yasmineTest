<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
   public function rules()
    {
        return [
            'address'   => 'required|max:255',
            'phone'     => 'required|numeric',
            'country_id'=> 'required',
            'state_id'  => 'required',
            'city_id'   => 'required',
        ];
    }




    /**
     * Get the validation messages of rules that apply to the request.
     *
     * @return array
     */
 public function messages()
    {
        return [
            'address.required'   => 'Address is required',
            'phone.required'     => 'Phone is required',
            'country_id.required'=> 'Country is required',
            'state_id.required'  => 'State is required',
            'city_id.required'   => 'City name is required',
        ];
    }
}

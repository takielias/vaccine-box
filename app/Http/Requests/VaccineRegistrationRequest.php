<?php

namespace App\Http\Requests;

use App\Rules\MinimumAgeRule;
use App\Rules\ValidatePhoneNumberRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Takielias\Lab\Facades\Lab;

class VaccineRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'unique:users,email'],
            'nid' => ['required', 'string', 'min:10', 'unique:users,nid'],
            'birth_date' => ['required', 'date', new MinimumAgeRule()],
            'phone_number' => ['required', new ValidatePhoneNumberRule()],
            'vaccination_center_id' => ['required', Rule::exists('vaccination_centers', 'id')]
        ];
    }


    protected function failedValidation($validator)
    {
        // Throw the HttpResponseException with the custom response
        throw new HttpResponseException(Lab::setStatus(422)
            ->enableScrollToTop()
            ->setValidationError($validator)->toJsonResponse());
    }
}

<?php

namespace App\Http\Requests;

use App\Rules\MinimumAgeRule;
use App\Rules\ValidateNIDRule;
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
            'nid' => ['unique:users,nid', new ValidateNIDRule()],
            'birth_date' => ['required', 'date', new MinimumAgeRule()],
            'phone_number' => ['required', new ValidatePhoneNumberRule()],
            'vaccination_center_id' => ['required', Rule::exists('vaccination_centers', 'id')]
        ];
    }

    public function messages(): array
    {
        return [
            'birth_date.required' => 'Patient Date of Birth is required.',
            'name.required' => 'Patient Name is required.',
            'phone_number.required' => 'Patient Phone number is required.',
            'email.required' => 'Patient Email address is required.',
            'vaccination_center_id.required' => 'You must specify a vaccination center.',
            'nid.unique' => 'Patient NID already exists.',
        ];
    }


    protected function failedValidation($validator)
    {
        // Throw the HttpResponseException with the custom response
        throw new HttpResponseException(Lab::setStatus(422)
            ->enableScrollToTop()
            ->setValidationError($validator)
            ->toJsonResponse());
    }
}

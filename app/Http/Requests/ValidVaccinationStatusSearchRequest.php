<?php

namespace App\Http\Requests;

use App\Rules\ValidateNIDRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Takielias\Lab\Facades\Lab;

class ValidVaccinationStatusSearchRequest extends FormRequest
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
            'nid' => [new ValidateNIDRule(), Rule::exists('users', 'nid')],
        ];
    }

    public function messages(): array
    {
        $registrationLink = route('vaccine-registration');

        return [
            'nid.exists' => "The provided NID is not registered in our system. Please <a class='btn btn-sm btn-ghost-primary' href='{$registrationLink}'>Register Here</a>",
        ];
    }

    protected function failedValidation($validator)
    {
        // Throw the HttpResponseException with the custom response
        throw new HttpResponseException(Lab::setStatus(422)
            ->enableScrollToTop()
            ->disableFadeOut()
            ->setValidationError($validator)->toJsonResponse());
    }
}

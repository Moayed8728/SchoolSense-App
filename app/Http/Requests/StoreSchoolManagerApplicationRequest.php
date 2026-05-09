<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class StoreSchoolManagerApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fullName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'schoolName' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'size:2'],
            'city' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'websiteUrl' => ['required', 'url', 'max:255'],
            'contactEmail' => ['nullable', 'email', 'max:255'],
            'contactPhone' => ['nullable', 'string', 'max:255'],
            'contactPageUrl' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string'],
            'feesMin' => ['nullable', 'integer', 'min:0'],
            'feesMax' => ['nullable', 'integer', 'min:0', 'gte:feesMin'],
            'currency' => ['required', 'string', 'size:3'],
            'feePeriod' => ['required', 'in:yearly,semester'],
            'curriculumIds' => ['nullable', 'array'],
            'curriculumIds.*' => ['uuid', 'exists:curricula,id'],
            'activityIds' => ['nullable', 'array'],
            'activityIds.*' => ['uuid', 'exists:activities,id'],
            'languageIds' => ['nullable', 'array'],
            'languageIds.*' => ['uuid', 'exists:languages,id'],
            'proofText' => ['required', 'string', 'max:4000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'country' => strtoupper((string) $this->input('country')),
            'currency' => strtoupper((string) $this->input('currency')),
        ]);
    }

    public function messages(): array
    {
        return [
            'feesMax.gte' => 'Fees max must be greater than or equal to fees min.',
            'email.unique' => 'An account with this email already exists.',
            'proofText.required' => 'Add proof that you represent this school.',
        ];
    }

    public function attributes(): array
    {
        return [
            'fullName' => 'full name',
            'schoolName' => 'school name',
            'websiteUrl' => 'official school website',
            'contactEmail' => 'contact email',
            'contactPhone' => 'contact phone',
            'contactPageUrl' => 'contact page URL',
            'feesMin' => 'fees min',
            'feesMax' => 'fees max',
            'feePeriod' => 'fee period',
            'curriculumIds' => 'curricula',
            'curriculumIds.*' => 'curriculum',
            'activityIds' => 'activities',
            'activityIds.*' => 'activity',
            'languageIds' => 'languages',
            'languageIds.*' => 'language',
            'proofText' => 'proof text',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOwnedSchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['nullable', 'string'],
            'contactEmail' => ['nullable', 'email', 'max:255'],
            'contactPhone' => ['nullable', 'string', 'max:255'],
            'websiteUrl' => ['nullable', 'url', 'max:255'],
            'contactPageUrl' => ['nullable', 'url', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'size:2'],
            'city' => ['nullable', 'string', 'max:255'],
            'feesMin' => ['nullable', 'integer', 'min:0'],
            'feesMax' => ['nullable', 'integer', 'min:0', 'gte:feesMin'],
            'currency' => ['nullable', 'string', 'size:3'],
            'feePeriod' => ['nullable', 'in:yearly,semester'],
            'curriculumIds' => ['nullable', 'array'],
            'curriculumIds.*' => ['uuid', 'exists:curricula,id'],
            'activityIds' => ['nullable', 'array'],
            'activityIds.*' => ['uuid', 'exists:activities,id'],
            'languageIds' => ['nullable', 'array'],
            'languageIds.*' => ['uuid', 'exists:languages,id'],
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
        ];
    }

    public function attributes(): array
    {
        return [
            'websiteUrl' => 'website URL',
            'contactPageUrl' => 'contact page URL',
            'contactEmail' => 'contact email',
            'contactPhone' => 'contact phone',
            'feesMin' => 'fees min',
            'feesMax' => 'fees max',
            'feePeriod' => 'fee period',
            'curriculumIds' => 'curricula',
            'curriculumIds.*' => 'curriculum',
            'activityIds' => 'activities',
            'activityIds.*' => 'activity',
            'languageIds' => 'languages',
            'languageIds.*' => 'language',
        ];
    }
}

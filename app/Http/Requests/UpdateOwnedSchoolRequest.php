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
}

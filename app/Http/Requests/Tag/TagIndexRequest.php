<?php

namespace App\Http\Requests\Tag;

use App\Services\BooleanService;
use Illuminate\Foundation\Http\FormRequest;

class TagIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'nested' => 'nullable',
        ];
    }

    /**
     * @return bool|null
     */
    public function getNested(): ?bool
    {
        return BooleanService::boolValue($this->input('nested'));
    }
}

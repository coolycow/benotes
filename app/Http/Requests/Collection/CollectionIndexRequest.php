<?php

namespace App\Http\Requests\Collection;

use App\Services\BooleanService;
use Illuminate\Foundation\Http\FormRequest;

class CollectionIndexRequest extends FormRequest
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
            'withShared' => 'nullable',
        ];
    }

    /**
     * @return bool
     */
    public function getNested(): bool
    {
        return BooleanService::boolValue($this->input('nested'));
    }

    /**
     * @return bool
     */
    public function getWithShared(): bool
    {
        return BooleanService::boolValue($this->input('withShared'));
    }
}

<?php

namespace App\Http\Requests\Share;

use Illuminate\Foundation\Http\FormRequest;

class ShareIndexRequest extends FormRequest
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
            'collection_id' => 'integer|nullable'
        ];
    }

    /**
     * @return int|null
     */
    public function getCollectionId(): ?int
    {
        return $this->input('collection_id');
    }
}

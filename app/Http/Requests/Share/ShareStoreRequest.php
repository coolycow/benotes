<?php

namespace App\Http\Requests\Share;

use Illuminate\Foundation\Http\FormRequest;

class ShareStoreRequest extends FormRequest
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
            'token' => 'required|string',
            'collection_id' => 'required|integer',
            'is_active' => 'required|boolean'
        ];
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->input('token');
    }

    /**
     * @return int
     */
    public function getCollectionId(): int
    {
        return $this->input('collection_id');
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->input('is_active');
    }
}

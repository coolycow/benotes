<?php

namespace App\Http\Requests\Collection;

use Illuminate\Foundation\Http\FormRequest;

class CollectionStoreRequest extends FormRequest
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
            'name' => 'required|string',
            'parent_id' => 'nullable|integer',
            'icon_id' => 'nullable|integer'
        ];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->input('name');
    }

    /**
     * @return int|null
     */
    public function getParentId(): ?int
    {
        return $this->input('parent_id');
    }

    /**
     * @return int|null
     */
    public function getIconId(): ?int
    {
        return $this->input('icon_id');
    }
}

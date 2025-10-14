<?php

namespace App\Http\Requests\Collection;

use Illuminate\Foundation\Http\FormRequest;

class CollectionUpdateRequest extends FormRequest
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
            'name' => 'nullable|string',
            'icon_id' => 'nullable|integer',
            'parent_id' => 'nullable|integer',
            'is_root' => 'nullable|boolean',
        ];
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->input('name');
    }

    /**
     * @return int|null
     */
    public function getIconId(): ?int
    {
        return $this->input('icon_id');
    }

    /**
     * @return int|null
     */
    public function getParentId(): ?int
    {
        return $this->input('parent_id');
    }

    /**
     * @return bool|null
     */
    public function getIsRoot(): ?bool
    {
        return $this->input('is_root');
    }
}

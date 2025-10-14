<?php

namespace App\Http\Requests\Post;

use App\Services\BooleanService;
use Illuminate\Foundation\Http\FormRequest;

class PostIndexRequest extends FormRequest
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
            'collection_id'    => 'integer|nullable',
            'is_uncategorized' => 'nullable',
            // should support: 0, 1, true, false, "true", "false" because
            // it should/could be used in a query string
            'tag_id'           => 'integer|nullable',
            'withTags'         => 'nullable',
            'filter'           => 'string|nullable',
            'is_archived'      => 'nullable',
            // same as is_uncategorized
            'after_id'         => 'integer|nullable',
            'offset'           => 'integer|nullable',
            'limit'            => 'integer|nullable',
        ];
    }

    /**
     * @return int|null
     */
    public function getCollectionId(): ?int
    {
        return $this->input('collection_id');
    }

    /**
     * @return bool
     */
    public function getIsUncategorized(): bool
    {
        return BooleanService::boolValue($this->input('is_uncategorized'));
    }

    /**
     * @return int|null
     */
    public function getTagId(): ?int
    {
        return $this->input('tag_id');
    }

    /**
     * @return bool
     */
    public function getWithTags(): bool
    {
        return BooleanService::boolValue($this->input('withTags'));
    }

    /**
     * @return string|null
     */
    public function getFilter(): ?string
    {
        return $this->input('filter');
    }

    /**
     * @return bool
     */
    public function getIsArchived(): bool
    {
        return BooleanService::boolValue($this->input('is_archived'));
    }

    /**
     * @return int|null
     */
    public function getAfterId(): ?int
    {
        return $this->input('after_id');
    }

    /**
     * @return int|null
     */
    public function getOffset(): ?int
    {
        return $this->input('offset');
    }

    /**
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->input('limit');
    }
}

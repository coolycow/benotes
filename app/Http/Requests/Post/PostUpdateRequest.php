<?php

namespace App\Http\Requests\Post;

use App\Services\BooleanService;
use Illuminate\Foundation\Http\FormRequest;

class PostUpdateRequest extends FormRequest
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
            'title'            => 'string|nullable',
            'content'          => 'string|nullable',
            'collection_id'    => 'integer|nullable',
            'is_uncategorized' => 'boolean|nullable',
            'tags'             => 'array|nullable',
            'tags.*'           => 'integer|required_with:tags',
            'order'            => 'integer|nullable',
            'is_archived'      => 'boolean|nullable'
        ];
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->input('title');
    }

    /**
     * @return string|null
     */
    public function getPostContent(): ?string
    {
        return $this->input('content');
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
     * @return array
     */
    public function getTags(): array
    {
        return $this->input('tags') ?? [];
    }

    /**
     * @return int|null
     */
    public function getOrder(): ?int
    {
        return $this->input('order');
    }

    /**
     * @return bool
     */
    public function getIsArchived(): bool
    {
        return BooleanService::boolValue($this->input('is_archived'));
    }
}

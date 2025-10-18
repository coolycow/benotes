<?php

namespace App\Http\Requests\Post;

use App\Rules\NotEmptyContentRule;
use Illuminate\Foundation\Http\FormRequest;

class PostStoreRequest extends FormRequest
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
            'title' => 'string|nullable',
            'content' => [
                'required',
                'string',
                new NotEmptyContentRule
            ],
            'collection_id' => 'integer|nullable',
            'description' => 'string|nullable',
            'tags' => 'array|nullable',
            'tags.*' => 'integer',
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
     * @return string
     */
    public function getPostContent(): string
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
     * @return string|null
     */
    public function getDescription(): ?string{
        return $this->input('description');
    }

    /**
     * @return array|null
     */
    public function getTags(): ?array
    {
        return $this->input('tags');
    }
}

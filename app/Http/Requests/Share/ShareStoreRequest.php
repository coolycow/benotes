<?php

namespace App\Http\Requests\Share;

use App\Enums\SharePermissionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

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
            'collection_id' => 'required|integer',
            'guests' => 'array|nullable',
            'guests.*.guest_id' => 'integer|exists:users,id',
            'guests.*.permission' => new Enum(SharePermissionEnum::class),
        ];
    }

    /**
     * @return int
     */
    public function getCollectionId(): int
    {
        return $this->input('collection_id');
    }

    /**
     * @return array
     */
    public function getGuests(): array
    {
        return $this->input('guests', []);
    }
}

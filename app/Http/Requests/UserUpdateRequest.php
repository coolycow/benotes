<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
            'name' => 'string',
            'email' => 'email',
            'password_old' => 'string',
            'password_new' => 'string|required_with:password_old'
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
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->input('email');
    }

    /**
     * @return string|null
     */
    public function getPasswordOld(): ?string
    {
        return $this->input('password_old');
    }

    /**
     * @return string|null
     */
    public function getPasswordNew(): ?string
    {
        return $this->input('password_new');
    }
}

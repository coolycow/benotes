<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NotEmptyContentRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        // Удаляем все HTML-теги
        $stripped = strip_tags($value);

        // Удаляем все whitespace символы (пробелы, табуляции, переносы строк)
        $trimmed = trim($stripped);

        // Проверяем, остался ли хоть какой-то текст
        return !empty($trimmed);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute cannot be empty or contain only tags and whitespace.';
    }
}

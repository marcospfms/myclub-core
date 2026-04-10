<?php

namespace App\Http\Requests\FriendlyMatch;

use Illuminate\Foundation\Http\FormRequest;

class PostponeFriendlyMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'scheduled_at' => ['required', 'date', 'after:now'],
            'location' => ['nullable', 'string', 'max:255'],
        ];
    }
}

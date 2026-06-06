<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    /**
     * Ensure the authenticated user is allowed to chat with the receiver.
     */
    public function authorize(): bool
    {
        $receiver = User::find($this->input('receiver_id'));

        if ($receiver === null) {
            return false;
        }

        return $this->user()?->can('chat-with', $receiver) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'receiver_id' => ['required', 'integer', 'exists:users,id'],
            'content' => ['required', 'string', 'max:5000'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MessageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'recipients' => ['nullable', 'array', 'min:1'],
            'recipients.*' => ['nullable', 'string'],
            'content' => ['required', 'string', 'max:1600'],
            'group' => ['nullable','string'],
            // 'group_id' => ['nullable', 'exists:contact_groups,id'],
            // 'template_id' => ['nullable', 'exists:message_templates,id'],
            'schedule_date' => ['nullable', 'date', 'after:now'],
            'include_salutation' => ['boolean'],
            'include_signature' => ['boolean'],
            'campaign' => ['required', 'array'],
            'campaign.name' => ['required', 'string', 'max:255'],
            'campaign.description' => ['nullable', 'string']
        ];
    }
}


<?php

namespace App\WebhookModule\Requests\Webhooks;

use App\Http\Requests\BaseFormRequest;

class UpdateRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'webhook_trigger_value_id' => ['exists:ExternalServiceWebhookTriggerValues,id'],
            'webhook_url'              => ['string', 'url', 'max:255'],
        ];

        return $rules;
    }
}

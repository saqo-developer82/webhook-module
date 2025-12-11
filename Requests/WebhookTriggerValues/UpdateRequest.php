<?php

namespace App\WebhookModule\Requests\WebhookTriggerValues;

use App\Http\Requests\BaseFormRequest;
use App\WebhookModule\Constants\WebhookConstants;
use Illuminate\Validation\Rule;

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
            'global_trigger_id'              => ['exists:ExternalServiceWebhookGlobalTriggers,id'],
            'resource_action_value'          => ['string', Rule::in(WebhookConstants::RESOURCE_ACTIONS)],
            'resource_field_values'          => ['array'],
            'resource_field_action_value'    => ['string', Rule::in(WebhookConstants::RESOURCE_FIELD_ACTIONS)],
            'resource_subfield_values'       => ['array'],
            'resource_subfield_action_value' => ['string', Rule::in(WebhookConstants::RESOURCE_SUBFIELD_ACTIONS)],
            'webhook_url'                    => ['string', 'url', 'max:255'],
        ];

        if (request()->get('resource_field_action_value') == WebhookConstants::FIELD_ACTION_NONE) {
            $rules['resource_subfield_values'][] = 'required';
            $rules['resource_subfield_action_value'][] = 'required';
        }

        return $rules;
    }
}

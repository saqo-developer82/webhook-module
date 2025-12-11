<?php

namespace App\WebhookModule\Jobs;

use App\Jobs\Job;
use App\WebhookModule\Constants\WebhookConstants;
use App\Models\Invoice;
use App\WebhookModule\Models\WebhookGlobalTrigger;
use App\WebhookModule\Services\WebhookSendService;
use App\Models\Job as JobModel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WebhookTriggersJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $resource_fields_values;
    protected $resource_subfields_values;
    protected $webhook_trigger_ids;
    protected $object;
    protected $action;
    protected $objectParams;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        $webhook_trigger_ids,
        $object,
        $action,
        $objectParams = null,
        $resource_fields_values = null,
        $resource_subfields_values = null
    )
    {
        $this->webhook_trigger_ids       = $webhook_trigger_ids;
        $this->object                    = $object;
        $this->resource_fields_values    = $resource_fields_values;
        $this->resource_subfields_values = $resource_subfields_values;
        $this->action                    = $action;
        $this->objectParams              = $objectParams;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $webhookTriggers = WebhookGlobalTrigger::whereIn('id', $this->webhook_trigger_ids)
                ->with([
                    'webhookTriggerValues' => function($q) {
                        $q->where('company_id', $this->object->company_id);
                        $q->where('resource_action_value', $this->action);
                    }
                ])
            ->get();

            foreach ($webhookTriggers as $webhookTrigger) {
                $send_data = [];

                $webhookTriggerValues = $webhookTrigger->webhookTriggerValues;
                foreach ($webhookTriggerValues as $webhookTriggerValue) {
                    $webhook = $webhookTriggerValue->webhook;

                    $triggerResourceUppercase = strtoupper($webhookTriggerValue->resource_value);
                    $triggerResourceActionUppercase = strtoupper($webhookTriggerValue->resource_action_value);

                    if (empty($this->resource_fields_values)) {
                        if (!empty($webhookTrigger->resource_field) || !empty($webhookTrigger->resource_subfield)) {
                            continue;
                        }

                        $send_data['trigger'] = $webhookTrigger->name;
                        $send_data['message'] = $triggerResourceActionUppercase . ' ON ' . $triggerResourceUppercase;
                        $send_data['data'] = $this->getObjectData();
                        $send_data['company'] = 'Company_' . $this->object->company->title . '_' . $this->object->company_id;

                        $service = new WebhookSendService($webhook, $send_data);
                        $service->send();

                        continue;
                    }

                    if (!empty($webhookTrigger->resource_field) && empty($webhookTrigger->resource_subfield)) {
                        if (empty($webhookTriggerValue->resource_field_values)
                            || !in_array($webhookTriggerValue->resource_field_action_value, [WebhookConstants::FIELD_ACTION_IN, WebhookConstants::FIELD_ACTION_OUT])
                        ) {
                            continue;
                        }

                        if (!array_key_exists($webhookTrigger->resource_field, $this->resource_fields_values)) {
                            continue;
                        }

                        if (!in_array(WebhookConstants::RESOURCE_ACTION_ALL_RECORDS, $webhookTriggerValue->resource_field_values)
                            && !in_array($this->resource_fields_values[$webhookTrigger->resource_field]['old'], $webhookTriggerValue->resource_field_values)
                            && !in_array($this->resource_fields_values[$webhookTrigger->resource_field]['new'], $webhookTriggerValue->resource_field_values)
                        ) {
                            continue;
                        }

                        if (in_array(WebhookConstants::RESOURCE_ACTION_ALL_RECORDS, $webhookTriggerValue->resource_field_values)) {
                            $send_data['trigger'] = $webhookTrigger->name;
                            $send_data['message'] = $triggerResourceActionUppercase . ' ON ' . $triggerResourceUppercase . ' FOR ' . $webhookTrigger->resource_field;
                            $send_data['data'] = [
                                'old_value' => $this->resource_fields_values[$webhookTrigger->resource_field]['old'],
                                'new_value' => $this->resource_fields_values[$webhookTrigger->resource_field]['new'],
                                'object'    => $this->getObjectData()
                            ];
                        }

                        if (in_array($this->resource_fields_values[$webhookTrigger->resource_field]['old'], $webhookTriggerValue->resource_field_values)
                            && $webhookTriggerValue->resource_field_action_value == WebhookConstants::FIELD_ACTION_OUT
                        ) {
                            $send_data['trigger'] = $webhookTrigger->name;
                            $send_data['message'] = strtoupper($webhookTriggerValue->resource_action_value) . ' ON ' . strtoupper($webhookTriggerValue->resource_value) . ' FOR ' . $webhookTrigger->resource_field;
                            $send_data['data'] = [
                                'old_value' => $this->resource_fields_values[$webhookTrigger->resource_field]['old'],
                                'new_value' => $this->resource_fields_values[$webhookTrigger->resource_field]['new'],
                                'object'    => $this->getObjectData()
                            ];
                        }

                        if (in_array($this->resource_fields_values[$webhookTrigger->resource_field]['new'], $webhookTriggerValue->resource_field_values)
                            && $webhookTriggerValue->resource_field_action_value == WebhookConstants::FIELD_ACTION_IN
                        ) {
                            $send_data['trigger'] = $webhookTrigger->name;
                            $send_data['message'] = $triggerResourceActionUppercase . ' ON ' . $triggerResourceUppercase . ' FOR ' . $webhookTrigger->resource_field;
                            $send_data['data'] = [
                                'old_value' => $this->resource_fields_values[$webhookTrigger->resource_field]['old'],
                                'new_value' => $this->resource_fields_values[$webhookTrigger->resource_field]['new'],
                                'object'    => $this->getObjectData()
                            ];
                        }

                        if (!empty($send_data)) {
                            $send_data['company'] = 'Company_' . $this->object->company->title . '_' . $this->object->company_id;
                            $service = new WebhookSendService($webhook, $send_data);
                            $service->send();
                        }

                        continue;
                    }

                    if ($webhookTriggerValue->resource_field_action_value != WebhookConstants::FIELD_ACTION_NONE) {
                        continue;
                    }

                    // This section already implyies we have resource and subresource data set
                    $resourceFieldKeys = array_keys($this->resource_fields_values);
                    if (empty($resourceFieldKeys[0])) {
                        continue;
                    }

                    $resourceFieldKey = $resourceFieldKeys[0];
                    $resourceFieldValue = $this->resource_fields_values[$resourceFieldKey];

                    if ($resourceFieldKey != $webhookTrigger->resource_field) {
                        continue;
                    }


                    if (!in_array(WebhookConstants::RESOURCE_ACTION_ALL_RECORDS, $webhookTriggerValue->resource_field_values)
                        && !in_array($resourceFieldValue, $webhookTriggerValue->resource_field_values)
                    ) {
                        continue;
                    }

                    if (empty($webhookTriggerValue->resource_subfield_values)
                        || !in_array($webhookTriggerValue->resource_subfield_action_value, [WebhookConstants::FIELD_ACTION_IN, WebhookConstants::FIELD_ACTION_OUT])
                    ) {
                        continue;
                    }

                    if (!empty($this->resource_subfields_values) && !array_key_exists($webhookTrigger->resource_subfield, $this->resource_subfields_values)) {
                        continue;
                    }

                    if (!in_array(WebhookConstants::RESOURCE_ACTION_ALL_RECORDS, $webhookTriggerValue->resource_subfield_values)
                        && !in_array($this->resource_subfields_values[$webhookTrigger->resource_subfield]['old'], $webhookTriggerValue->resource_subfield_values)
                        && !in_array($this->resource_subfields_values[$webhookTrigger->resource_subfield]['new'], $webhookTriggerValue->resource_subfield_values)
                    ) {
                        continue;
                    }

                    if (in_array(WebhookConstants::RESOURCE_ACTION_ALL_RECORDS, $webhookTriggerValue->resource_subfield_values)) {
                        $send_data['trigger'] = $webhookTrigger->name;
                        $send_data['message'] = $triggerResourceActionUppercase . ' ON ' . $triggerResourceUppercase . ' FOR ' . $webhookTrigger->resource_subfield;
                        $send_data['data'] = [
                            'old_value' => $this->resource_subfields_values[$webhookTrigger->resource_subfield]['old'],
                            'new_value' => $this->resource_subfields_values[$webhookTrigger->resource_subfield]['new'],
                            'object'    => $this->getObjectData()
                        ];
                    }

                    if (in_array($this->resource_subfields_values[$webhookTrigger->resource_subfield]['old'], $webhookTriggerValue->resource_subfield_values)
                        && $webhookTriggerValue->resource_subfield_action_value == WebhookConstants::FIELD_ACTION_OUT
                    ) {
                        $send_data['trigger'] = $webhookTrigger->name;
                        $send_data['message'] = $triggerResourceActionUppercase . ' ON ' . $triggerResourceUppercase . ' FOR ' . $webhookTrigger->resource_subfield;
                        $send_data['data'] = [
                            'old_value' => $this->resource_subfields_values[$webhookTrigger->resource_subfield]['old'],
                            'new_value' => $this->resource_subfields_values[$webhookTrigger->resource_subfield]['new'],
                            'object'    => $this->getObjectData()
                        ];
                    }

                    if (in_array($this->resource_subfields_values[$webhookTrigger->resource_subfield]['new'], $webhookTriggerValue->resource_subfield_values)
                        && $webhookTriggerValue->resource_subfield_action_value == WebhookConstants::FIELD_ACTION_IN
                    ) {
                        $send_data['trigger'] = $webhookTrigger->name;
                        $send_data['message'] = $triggerResourceActionUppercase . ' ON ' . $triggerResourceUppercase . ' FOR ' . $webhookTrigger->resource_subfield;
                        $send_data['data'] = [
                            'old_value' => $this->resource_subfields_values[$webhookTrigger->resource_subfield]['old'],
                            'new_value' => $this->resource_subfields_values[$webhookTrigger->resource_subfield]['new'],
                            'object'    => $this->getObjectData()
                        ];
                    }

                    if (!empty($send_data)) {
                        $send_data['company'] = 'Company_' . $this->object->company->title . '_' . $this->object->company_id;
                        $service = new WebhookSendService($webhook, $send_data);
                        $service->send();
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error("Webhook trigger job error: " . $e->getMessage());
        }
    }

    /**
     *  get data for webhook send
     */
    private function getObjectData()
    {
        $acceptableObject = (
            $this->object instanceof JobModel ||
            $this->object instanceof Invoice
        );

        if ($acceptableObject && !empty($this->objectParams)) {
            return $this->object->only($this->objectParams);
        }
    }
}

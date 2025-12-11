<?php

namespace App\WebhookModule\Services;

use App\WebhookModule\Jobs\WebhookTriggersJob;
use App\WebhookModule\Models\WebhookGlobalTrigger;

class WebhookTriggerService
{
    protected $resource_subfields_values;
    protected $resource_fields_values;
    protected $name;
    protected $resource;
    protected $action;
    protected $object;
    protected $objectParams;

    public function __construct(
        $name,
        $object,
        $resource,
        $action,
        $objectParams = null,
        $resource_fields_values = null,
        $resource_subfields_values = null
    )
    {
        $this->resource_subfields_values = $resource_subfields_values;
        $this->resource_fields_values    = $resource_fields_values;
        $this->resource                  = $resource;
        $this->action                    = $action;
        $this->object                    = $object;
        $this->name                      = $name;
        $this->objectParams              = $objectParams;
    }

    public function run()
    {
        $webhookTriggersQuery = WebhookGlobalTrigger::where('resource', $this->resource)
            ->where('name', $this->name)
            ->whereJsonContains('resource_actions', $this->action)
            ->whereHas('webhookTriggerValues', function($q) {
                $q->where('company_id', $this->object->company_id);
                $q->where('resource_action_value', $this->action);
            });

        // For resource create or delete resource_fields_values should be null
        if (!empty($this->resource_fields_values)) {
            $webhookTriggersQuery->where(function ($q1) {
                $fields = array_keys($this->resource_fields_values);
                $q1->whereIn('resource_field', $fields);
            });
        } else {
            $webhookTriggersQuery->whereNull('resource_field');
        }

        // For resource create or delete resource_fields_values should be null
        if (!empty($this->resource_subfields_values)) {
            $webhookTriggersQuery->where(function ($q1) {
                $subFields = array_keys($this->resource_subfields_values);
                $q1->whereIn('resource_subfield', $subFields);
            });
        } else {
            $webhookTriggersQuery->whereNull('resource_subfield');
        }

        $webhookTriggerIds = $webhookTriggersQuery->pluck('id')->toArray();

        if (!empty($webhookTriggerIds)) {
            dispatch((new WebhookTriggersJob(
                $webhookTriggerIds,
                $this->object,
                $this->action,
                $this->objectParams,
                $this->resource_fields_values,
                $this->resource_subfields_values
            ))->onConnection(config('queue.default')));
        }
    }
}

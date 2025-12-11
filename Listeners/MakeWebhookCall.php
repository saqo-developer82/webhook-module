<?php

namespace App\WebhookModule\Listeners;

use App\WebhookModule\Constants\WebhookConstants;
use App\WebhookModule\Events\WebhookEvent as ExternalWebhookEvent;
use App\WebhookModule\Services\WebhookTriggerService;
use App\WebhookModule\Models\WebhookGlobalTrigger;

class MakeWebhookCall
{
    private $resource_updated_values;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ExternalWebhookEvent $event): void
    {
        try {
            if (!isset($event->object->webhook_resource)) {
                return;
            }

            // Exclude specific attributes
            $excluded_attributes = ['updated_at', 'created_at', 'deleted_at'];
            $object_changed_attributes = array_diff_key($event->object->getDirty(), array_flip($excluded_attributes));
            if (empty($object_changed_attributes)) {
                return;
            }

            $resource_updated_values = [];

            foreach ($object_changed_attributes as $attribute => $new_value) {
                $old_value = $event->object->getOriginal($attribute);
                $resource_updated_values[$attribute] = ['old' => $old_value, 'new' => $new_value];
            }

            if (empty($resource_updated_values)) {
                return;
            }

            $this->resource_updated_values = $resource_updated_values;

            $webhookGlobalTriggerNames = WebhookGlobalTrigger::where('resource' ,$event->object->webhook_resource)
                ->whereJsonContains('resource_actions', $event->action)
                ->whereIn('name', WebhookConstants::RESOURCE_JOB_EVENT_NAMES)
                ->pluck('name')
                ->toArray();

            foreach ($webhookGlobalTriggerNames as $webhookGlobalTriggerName) {
                $this->runGlobalWebhookTrigger($event, $webhookGlobalTriggerName);
            }
        } catch (\Exception $e) {
            \Log::error("Webhook event call error: " . $e->getMessage());
        }
    }

    private function runGlobalWebhookTrigger($event, $triggerName)
    {
        $webhookTriggerService = null;

        if ($event->object->webhook_resource == WebhookConstants::RESOURCE_JOB) {
            switch ($triggerName) {
                case WebhookConstants::RESOURCE_JOB_WORKFLOW_CUSTOM_STATUS_EVENT_NAME:
                    $objectParams = [
                        'id',
                        'job_type',
                        'customer_id',
                        'status',
                        'status_id',
                        'status_workflow_id',
                        'in_progress_status_log',
                        'updated_at'
                    ];
                    $workflowId = $event->object->status_workflow_id;
                    $resourceFieldValues = [
                        'status_workflow_id' => $workflowId
                    ];

                    $webhookTriggerService = new WebhookTriggerService(
                        $triggerName,
                        $event->object,
                        $event->object->webhook_resource,
                        $event->action,
                        $objectParams,
                        $resourceFieldValues,
                        $this->resource_updated_values
                    );
                    break;
                case WebhookConstants::RESOURCE_JOB_CUSTOM_STATUS_EVENT_NAME:
                case WebhookConstants::RESOURCE_JOB_START_TIME_UPDATE_EVENT_NAME:
                case WebhookConstants::RESOURCE_JOB_END_TIME_UPDATE_EVENT_NAME:
                case WebhookConstants::RESOURCE_JOB_CREATED_EVENT_NAME:
                    if (in_array($triggerName, [
                            WebhookConstants::RESOURCE_JOB_CUSTOM_STATUS_EVENT_NAME,
                            WebhookConstants::RESOURCE_JOB_CREATED_EVENT_NAME,
                        ])) {
                        $objectParams = [
                            'id',
                            'job_type',
                            'customer_id',
                            'status',
                            'status_id',
                            'status_workflow_id',
                            'in_progress_status_log',
                            'updated_at'
                        ];
                    } else {
                        $objectParams = [
                            'id',
                            'job_type',
                            'updated_at'
                        ];
                    }

                    $webhookTriggerService = new WebhookTriggerService(
                        $triggerName,
                        $event->object,
                        $event->object->webhook_resource,
                        $event->action,
                        $objectParams,
                        $this->resource_updated_values
                    );
                    break;
            }
        }

        if ($webhookTriggerService) {
            $webhookTriggerService->run();
        } else {
            \Log::error("Webhook trigger with name {$triggerName} not found");
        }
    }
}

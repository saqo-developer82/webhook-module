<?php

namespace App\WebhookModule\Commands;

use App\WebhookModule\Models\WebhookGlobalTrigger;
use App\WebhookModule\Constants\WebhookConstants;
use App\Modules\Sales\Models\Invoice;
use App\Modules\Schedule\Models\Job;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateNewWebhookGlobalTrigger extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name      = 'create:webhook-trigger';
    protected $signature = 'create:webhook-trigger';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will create new webhook global trigger.';

    /**
     * If the subfield is required for the trigger.
     * @var bool
     */
    protected bool $subfieldRequired = false;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $webhook_trigger_data = [];

        $this->setTriggerName($webhook_trigger_data);
        $this->setTriggerResource($webhook_trigger_data);
        $this->setTriggerResourceActions($webhook_trigger_data);
        $this->setTriggerResourceFieldAndActions($webhook_trigger_data);
        $this->setTriggerResourceSubFieldAndActions($webhook_trigger_data);

        try {
            $webhook_trigger_data = (new WebhookGlobalTrigger())->AttributeHandler($webhook_trigger_data);
            $this->info("Webhook global trigger created successfully.");
            $this->info(print_r($webhook_trigger_data->toArray(), true));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        exit();
    }

    /**
     * Set the global webhook trigger name
     * @param array $webhook_trigger_data
     * @return void
     */
    protected function setTriggerName(&$webhook_trigger_data)
    {
        $existingNames = WebhookGlobalTrigger::pluck('name')->toArray();
        $availableNames = array_values(array_diff(WebhookConstants::RESOURCE_ALL_EVENT_NAMES, $existingNames));

        $name = $this->choice(
            'Select the name of the webhook trigger*',
            array_flip($availableNames),
            null,
            $maxAttempts = 3, // allow 3 attempts
            $allowMultipleSelections = false
        );

        $webhook_trigger_data['name'] = $name;

        $this->subfieldRequired = in_array($name, WebhookConstants::SUBFIELD_REQUIRED_TRIGGERS);
    }

    /**
     * To what resource the trigger is applied. Can be jobs, invoices, etc.
     * @param array $webhook_trigger_data
     * @return void
     */
    protected function setTriggerResource(&$webhook_trigger_data)
    {
        // Ask for webhook trigger resource
        $resource = $this->choice(
            "Enter the resource for the webhook trigger ({$webhook_trigger_data['name']}) *",
            WebhookConstants::RESOURCES_CHOICES,
            null,
            $maxAttempts = 3, // allow 3 attempts
            $allowMultipleSelections = false
        );

        $webhook_trigger_data['resource'] = $resource;
    }

    /**
     * On which action trigger the webhooks: Should be json object containing "create", "update", "delete"
     * @param array $webhook_trigger_data
     * @return void
     */
    protected function setTriggerResourceActions(&$webhook_trigger_data)
    {
        // Ask for webhook trigger resource actions
        $resource_actions = $this->choice(
            "Enter the resource actions for the webhook trigger ({$webhook_trigger_data['name']}) (comma separated, if multiple)*",
            WebhookConstants::RESOURCE_ACTIONS_CHOICES,
            null,
            $maxAttempts = 3, // allow 3 attempts
            $allowMultipleSelections = true
        );

        if (in_array('all', $resource_actions)) {
            $resource_actions = ['create', 'update', 'delete'];
        }

        $webhook_trigger_data['resource_actions'] = $resource_actions;
    }

    /**
     * @param array $webhook_trigger_data
     * @return void
     */
    protected function setTriggerResourceFieldAndActions(&$webhook_trigger_data)
    {
        // Ask for webhook trigger resource field.
        // For example when resource=job this can be custom workflow or custom status
        $resource_field = $this->choice(
            "Enter the resource field for the webhook trigger ({$webhook_trigger_data['name']})",
            $this->getFieldsOptions($webhook_trigger_data),
            null,
            $maxAttempts = 3, // allow 3 attempts
            $allowMultipleSelections = false
        );

        if ($resource_field !== "SKIP") {
            $webhook_trigger_data['resource_field'] = $resource_field;

            $this->setTriggerResourceFieldActions($webhook_trigger_data);
        }
    }

    /**
     * @param array $webhook_trigger_data
     * @return void
     */
    protected function setTriggerResourceFieldActions(&$webhook_trigger_data)
    {
        // Ask for webhook trigger resource field actions.
        // On which actions to trigger the webhook. Should be json object containing these available values: "none", "in", "out".
        $resource_field_actions = $this->choice(
            "Enter the resource field actions for the webhook trigger ({$webhook_trigger_data['name']}) (comma separated, if multiple)",
            WebhookConstants::RESOURCE_FIELD_ACTIONS_CHOICES,
            null,
            $maxAttempts = 3, // allow 3 attempts
            $allowMultipleSelections = true
        );

        if (!in_array('skip', $resource_field_actions)) {
            if (in_array('all', $resource_field_actions)) {
                $resource_field_actions = ['none', 'in', 'out'];
            }

            $webhook_trigger_data['resource_field_actions'] = $resource_field_actions;
        }
    }

    /**
     * @param array $webhook_trigger_data
     * @return void
     */
    protected function setTriggerResourceSubFieldAndActions(&$webhook_trigger_data)
    {
        if ($this->subfieldRequired) {
            // Ask for webhook trigger resource subfield.
            // The subfield of selected field. For example for jobs:  resource_field=custom_workflow, resource_subfield=custom_status
            $resource_subfield = $this->choice(
                "Enter the resource subfield for the webhook trigger ({$webhook_trigger_data['name']})",
                $this->getFieldsOptions($webhook_trigger_data, false),
                null,
                $maxAttempts = 3, // allow 3 attempts
                $allowMultipleSelections = false
            );

            $webhook_trigger_data['resource_subfield'] = $resource_subfield;
            $this->setTriggerResourceSubFieldActions($webhook_trigger_data);
        }
    }

    /**
     * @param array $webhook_trigger_data
     * @return void
     */
    protected function setTriggerResourceSubFieldActions(&$webhook_trigger_data)
    {
        if ($this->subfieldRequired) {
            // Ask for webhook trigger resource subfield actions.
            //The subfield actions to trigger the webhook. Should be json object containing these available values: "in", "out".
            $resource_subfield_actions = $this->choice(
                "Enter the resource subfield actions for the webhook trigger ({$webhook_trigger_data['name']}) (comma separated, if multiple)",
                WebhookConstants::RESOURCE_SUBFIELD_ACTIONS_CHOICES,
                null,
                $maxAttempts = 3, // allow 3 attempts
                $allowMultipleSelections = true
            );

            if (in_array('all', $resource_subfield_actions)) {
                $resource_subfield_actions = ['in', 'out'];
            }

            $webhook_trigger_data['resource_subfield_actions'] = $resource_subfield_actions;
        }
    }

    /**
     * Generates a list of options based on the specified resource within the
     * webhook trigger data. The method retrieves the appropriate database table
     * based on the resource type and fetches its column names to construct the
     * options list.
     *
     * @param array $webhook_trigger_data Contains information about the trigger,
     *                                    including the resource type.
     * @param bool $withSkip If true, the skip option will be added to the list.
     *
     * @return array Associative array of column names as keys and their respective
     *               positions as values, with an additional empty option.
     * @throws void Terminates execution if the resource type is invalid.
     */
    protected function getFieldsOptions($webhook_trigger_data, $withSkip = true)
    {
        $availableResources = [
            WebhookConstants::RESOURCE_JOB => (new Job())->getTable(),
            WebhookConstants::RESOURCE_INVOICE => (new Invoice())->getTable(),
            WebhookConstants::RESOURCE_ESTIMATE => (new Invoice())->getTable(),
        ];

        $table = $availableResources[$webhook_trigger_data['resource']] ?? null;

        if (!$table) {
            $this->error('Invalid resource');
            exit();
        }

        $columns = DB::getSchemaBuilder()->getColumnListing($table);

        if ($withSkip) {
            return ['SKIP' => -1] + array_flip($columns); // add empty for skiping
        }

        return array_flip($columns);
    }
}

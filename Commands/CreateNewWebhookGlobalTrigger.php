<?php

namespace App\WebhookModule\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\WebhookModule\Builders\WebhookTriggerBuilder;
use App\WebhookModule\Constants\WebhookConstants;
use App\WebhookModule\Models\BaseWebhookGlobalTrigger;
use App\Models\BaseJob;
use App\Models\BaseInvoice;


/**
 * Command for creating new webhook global triggers
 *
 * This command guides the user through the process of creating webhook triggers
 * with a fluent interface that allows for easy configuration of various parameters.
 */
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
     * The webhook trigger builder instance.
     *
     * @var WebhookTriggerBuilder
     */
    protected $webhookBuilder;

    /**
     * Create a new command instance with dependencies.
     *
     * @param WebhookTriggerBuilder $webhookBuilder Builder for webhook triggers
     * @return void
     */
    public function __construct(WebhookTriggerBuilder $webhookBuilder) {
        parent::__construct();
        $this->webhookBuilder = $webhookBuilder;
    }

    /**
     * Execute the console command.
     *
     * Guides through the steps of building a webhook trigger and saves it.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Start building the webhook trigger with required properties
            $this->webhookBuilder->reset();

            // Collect information step by step
            $this->setTriggerName();
            $this->setTriggerResource();
            $this->setTriggerResourceActions();
            $this->setTriggerResourceFieldAndActions();
            $this->setTriggerResourceSubFieldAndActions();

            $webhookTriggerData = (new BaseWebhookGlobalTrigger())->AttributeHandler($this->webhookBuilder->build());
            $this->info("Webhook global trigger created successfully.");
            $this->info(print_r($webhookTriggerData->toArray(), true));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        exit();
    }

    /**
     * Set the global webhook trigger name
     *
     * Prompts the user to select a name for the webhook trigger from predefined options.
     *
     * @return void
     */
    protected function setTriggerName(): void
    {
        $name = $this->choice(
            'Select the name of the webhook trigger*',
            array_flip(WebhookConstants::RESOURCE_ALL_EVENT_NAMES),
            null,
            $maxAttempts = 3,
            $allowMultipleSelections = false
        );

        $this->webhookBuilder->withName($name);
    }

    /**
     * Set the resource for the webhook trigger
     *
     * Determines which resource type the trigger will be applied to (jobs, invoices, etc.).
     *
     * @return void
     */
    protected function setTriggerResource(): void
    {
        $resource = $this->choice(
            'Enter the resource for the webhook trigger*',
            [
                'jobs'     => 1,
                'invoices' => 2
            ],
            null,
            $maxAttempts = 3,
            $allowMultipleSelections = false
        );

        $this->webhookBuilder->withResource($resource);
    }

    /**
     * Set the resource actions for the webhook trigger
     *
     * Determines which actions (create, update, delete) will trigger the webhook.
     *
     * @return void
     */
    protected function setTriggerResourceActions(): void
    {
        $resourceActions = $this->choice(
            'Enter the resource actions for the webhook trigger (comma separated, if multiple)*',
            [
                'all'    => 0,
                'create' => 1,
                'update' => 2,
                'delete' => 3
            ],
            null,
            $maxAttempts = 3,
            $allowMultipleSelections = true
        );

        if (in_array('all', $resourceActions)) {
            $resourceActions = ['create', 'update', 'delete'];
        }

        $this->webhookBuilder->withResourceActions($resourceActions);
    }

    /**
     * Set the resource field and its actions for the webhook trigger
     *
     * Prompts for specific fields to monitor and the actions to track on those fields.
     *
     * @return void
     */
    protected function setTriggerResourceFieldAndActions(): void
    {
        // Ask for webhook trigger resource field
        $resourceField = $this->choice(
            'Enter the resource field for the webhook trigger',
            $this->getFieldsOptions($this->webhookBuilder->getResource()),
            null,
            $maxAttempts = 3,
            $allowMultipleSelections = false
        );

        if ($resourceField !== "") {
            $this->webhookBuilder->withResourceField($resourceField);
            $this->setTriggerResourceFieldActions();
        }
    }

    /**
     * Set the actions for the selected resource field
     *
     * Determines which specific field actions to track (none, in, out).
     *
     * @return void
     */
    protected function setTriggerResourceFieldActions(): void
    {
        $resourceFieldActions = $this->choice(
            'Enter the resource field actions for the webhook trigger (comma separated, if multiple)',
            [
                'skip' => 0,
                'all'  => 1,
                'none' => 2,
                'in'   => 3,
                'out'  => 4
            ],
            null,
            $maxAttempts = 3,
            $allowMultipleSelections = true
        );

        if (!in_array('skip', $resourceFieldActions)) {
            if (in_array('all', $resourceFieldActions)) {
                $resourceFieldActions = ['none', 'in', 'out'];
            }

            $this->webhookBuilder->withResourceFieldActions($resourceFieldActions);
        }
    }

    /**
     * Set the resource subfield and its actions for the webhook trigger
     *
     * Prompts for specific subfields to monitor and the actions to track on those subfields.
     *
     * @return void
     */
    protected function setTriggerResourceSubFieldAndActions(): void
    {
        $resourceSubfield = $this->choice(
            'Enter the resource subfield for the webhook trigger',
            $this->getFieldsOptions($this->webhookBuilder->getResource()),
            null,
            $maxAttempts = 3,
            $allowMultipleSelections = false
        );

        if ($resourceSubfield !== "") {
            $this->webhookBuilder->withResourceSubfield($resourceSubfield);
            $this->setTriggerResourceSubFieldActions();
        }
    }

    /**
     * Set the actions for the selected resource subfield
     *
     * Determines which specific subfield actions to track (in, out).
     *
     * @return void
     */
    protected function setTriggerResourceSubFieldActions(): void
    {
        $resourceSubfieldActions = $this->choice(
            'Enter the resource subfield actions for the webhook trigger (comma separated, if multiple)',
            [
                'skip' => 0,
                'all'  => 1,
                'in'   => 2,
                'out'  => 3
            ],
            null,
            $maxAttempts = 3,
            $allowMultipleSelections = true
        );

        if (!in_array('skip', $resourceSubfieldActions)) {
            if (in_array('all', $resourceSubfieldActions)) {
                $resourceSubfieldActions = ['in', 'out'];
            }

            $this->webhookBuilder->withResourceSubfieldActions($resourceSubfieldActions);
        }
    }

    /**
     * Generates a list of field options based on the specified resource
     *
     * Retrieves database columns for the resource to present as options.
     *
     * @param string $resource The resource type (jobs, invoices)
     * @return array Associative array of column names and their positions
     * @throws \Exception If the resource type is invalid
     */
    protected function getFieldsOptions(string $resource): array
    {
        switch ($resource) {
            case 'jobs':
                $table = (new BaseJob())->getTable();
                break;
            case 'invoices':
                $table = (new BaseInvoice())->getTable();
                break;
            default:
                throw new \Exception('Invalid resource');
        }

        $columns = DB::getSchemaBuilder()->getColumnListing($table);

        // Add empty option for skipping
        return ['' => -1] + array_flip($columns);
    }
}

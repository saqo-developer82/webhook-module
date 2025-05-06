<?php

namespace App\WebhookModule\Builders;

/**
 * Builder class for WebhookTrigger objects
 *
 * Implements the Builder pattern to create webhook trigger objects with a fluent interface.
 */
class WebhookTriggerBuilder
{
    /**
     * The webhook trigger data being built
     *
     * @var array
     */
    protected $webhookTriggerData = [];

    /**
     * Reset the builder to start fresh
     *
     * @return self
     */
    public function reset(): self
    {
        $this->webhookTriggerData = [];
        return $this;
    }

    /**
     * Set the name for the webhook trigger
     *
     * @param string $name The name of the webhook trigger
     * @return self
     */
    public function withName(string $name): self
    {
        $this->webhookTriggerData['name'] = $name;
        return $this;
    }

    /**
     * Set the resource for the webhook trigger
     *
     * @param string $resource The resource type (jobs, invoices, etc.)
     * @return self
     */
    public function withResource(string $resource): self
    {
        $this->webhookTriggerData['resource'] = $resource;
        return $this;
    }

    /**
     * Get the current resource type
     *
     * @return string|null The current resource type
     */
    public function getResource(): ?string
    {
        return $this->webhookTriggerData['resource'] ?? null;
    }

    /**
     * Set the resource actions for the webhook trigger
     *
     * @param array $actions Array of actions (create, update, delete)
     * @return self
     */
    public function withResourceActions(array $actions): self
    {
        $this->webhookTriggerData['resource_actions'] = $actions;
        return $this;
    }

    /**
     * Set the resource field for the webhook trigger
     *
     * @param string $field The field name
     * @return self
     */
    public function withResourceField(string $field): self
    {
        $this->webhookTriggerData['resource_field'] = $field;
        return $this;
    }

    /**
     * Set the resource field actions for the webhook trigger
     *
     * @param array $actions Array of field actions (none, in, out)
     * @return self
     */
    public function withResourceFieldActions(array $actions): self
    {
        $this->webhookTriggerData['resource_field_actions'] = $actions;
        return $this;
    }

    /**
     * Set the resource subfield for the webhook trigger
     *
     * @param string $subfield The subfield name
     * @return self
     */
    public function withResourceSubfield(string $subfield): self
    {
        $this->webhookTriggerData['resource_subfield'] = $subfield;
        return $this;
    }

    /**
     * Set the resource subfield actions for the webhook trigger
     *
     * @param array $actions Array of subfield actions (in, out)
     * @return self
     */
    public function withResourceSubfieldActions(array $actions): self
    {
        $this->webhookTriggerData['resource_subfield_actions'] = $actions;
        return $this;
    }

    /**
     * Get the built webhook trigger data
     *
     * @return array The webhook trigger data
     */
    public function build(): array
    {
        return $this->webhookTriggerData;
    }
}

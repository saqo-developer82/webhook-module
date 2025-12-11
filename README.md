# Webhook Module

A Laravel module for managing and executing webhooks in the application. This module provides a flexible system for creating webhook triggers, managing webhook configurations, and automatically sending webhook notifications when specific events occur in the system.

## Overview

This module enables external systems to receive real-time notifications about events happening in application, such as job creation, status updates, invoice changes, and estimate modifications. It supports complex trigger configurations with field-level and subfield-level filtering, allowing for granular control over when webhooks are fired.

## Features

- **Global Webhook Triggers**: Define reusable webhook trigger templates that can be used across multiple companies
- **Company-Specific Webhooks**: Configure webhooks per company with custom trigger values
- **Event-Based Triggers**: Support for create, update, and delete actions on resources
- **Field-Level Filtering**: Trigger webhooks based on specific field changes (e.g., status changes)
- **Subfield-Level Filtering**: Support for nested field changes (e.g., workflow status changes)
- **Automatic Secret Key Generation**: Each webhook automatically generates a secure secret key (64-character hex string) for HMAC authentication
- **Retry Mechanism**: Automatic retry logic (up to 3 attempts) for failed webhook deliveries
- **Request Logging**: All webhook requests and responses are logged for auditing
- **Queue-Based Processing**: Webhook delivery is handled asynchronously via Laravel queues

## Architecture

### Core Components

#### Models

- **`Webhook`**: Represents a webhook configuration with URL, secret key, and trigger value association
  - Table: `Webhooks`
  - Auto-generates `secret_key` on save if not provided
  - Relationships: `company`, `user`, `webhookTriggerValue`, `webhookRequests`

- **`WebhookGlobalTrigger`**: Defines global trigger templates that specify when webhooks should fire
  - Table: `WebhookGlobalTriggers`
  - Stores trigger name, resource type, actions, and field/subfield configurations
  - Relationships: `webhookTriggerValues`

- **`WebhookTriggerValue`**: Company-specific trigger values that link global triggers to webhooks
  - Table: `WebhookTriggerValues`
  - Stores company-specific filter values and action configurations
  - Relationships: `company`, `globalTrigger`, `webhook`

- **`WebhookRequest`**: Logs all webhook delivery attempts and responses
  - Table: `WebhookRequests`
  - Tracks request data, response status, and success/failure

#### Controllers

- **`WebhooksController`**: CRUD operations for webhook management
  - Endpoints: Enum, Find, Create, Update, Delete
  - Requires JWT authentication

- **`WebhookGlobalTriggersController`**: Read-only access to global trigger definitions
  - Endpoints: Enum, Find
  - Requires JWT authentication

- **`WebhookTriggerValuesController`**: CRUD operations for trigger value configurations
  - Endpoints: Enum, Find, Create, Update, Delete
  - Automatically creates/updates associated webhook when trigger value is created/updated
  - Requires JWT authentication

#### Services

- **`WebhookTriggerService`**: Determines which webhooks should be triggered based on events
  - Queries global triggers and trigger values based on resource, action, and field changes
  - Dispatches `WebhookTriggersJob` for matched webhooks

- **`WebhookSendService`**: Handles the actual HTTP request to webhook URLs
  - Encrypts payload with HMAC-SHA256 using webhook's secret key
  - Implements retry logic (up to 3 attempts)
  - Logs all requests to `WebhookRequest` model

#### Jobs

- **`WebhookTriggersJob`**: Queue job that processes webhook triggers and sends notifications
  - Processes multiple webhook triggers asynchronously
  - Filters webhooks based on field/subfield values and actions
  - Constructs payload with trigger info, message, data, and company identifier

#### Events & Listeners

- **`WebhookEvent`**: Event fired when a resource change occurs
  - Contains: `object` (the model instance) and `action` (create/update/delete)

- **`MakeWebhookCall`**: Listener that processes webhook events
  - Detects changed attributes (excluding timestamps)
  - Matches events to global triggers
  - Instantiates `WebhookTriggerService` for matched triggers

#### Commands

- **`CreateNewWebhookGlobalTrigger`**: Artisan command for creating new global triggers
  - Command: `php artisan create:webhook-trigger`
  - Interactive command that guides through trigger creation
  - Validates against existing triggers and available event names

## Supported Resources

The module currently supports webhooks for the following resources:

- **Jobs**: Job creation, status updates, workflow changes, start/end time updates
- **Invoices**: Invoice creation, workflow status updates, custom status updates
- **Estimates**: Estimate creation, workflow status updates, custom status updates

## API Endpoints

All endpoints require JWT authentication.

### Webhooks

- `GET /webhook` - List all webhooks (with EnumScope filtering)
- `POST /webhook` - Create a new webhook
  - Required: `webhook_trigger_value_id`, `webhook_url`
- `GET /webhook/{id}` - Get a specific webhook (with FindScope filtering)
- `PUT /webhook/{id}` - Update a webhook
- `DELETE /webhook/{id}` - Delete a webhook (soft delete)

### Webhook Global Triggers

- `GET /webhook-global-trigger` - List all global triggers
- `GET /webhook-global-trigger/{id}` - Get a specific global trigger

### Webhook Trigger Values

- `GET /webhook-trigger-value` - List all trigger values (with EnumScope filtering)
- `POST /webhook-trigger-value` - Create a new trigger value and associated webhook
  - Required: `global_trigger_id`, `resource_action_value`, `resource_field_action_value`, `webhook_url`
  - Conditionally required: `resource_subfield_values`, `resource_subfield_action_value` (if field action is "none")
- `GET /webhook-trigger-value/{id}` - Get a specific trigger value (with FindScope filtering)
- `PUT /webhook-trigger-value/{id}` - Update a trigger value and optionally update associated webhook
- `DELETE /webhook-trigger-value/{id}` - Delete a trigger value (soft delete)

## Database Schema

### Webhooks
- `id` - Primary key
- `company_id` - Foreign key to company
- `user_id` - Foreign key to user who created the webhook
- `webhook_trigger_value_id` - Foreign key to trigger value
- `webhook_url` - The URL to send webhook requests to
- `secret_key` - HMAC secret key (auto-generated, 64-character hex string)
- `created_at`, `updated_at`, `deleted_at` - Timestamps

### WebhookGlobalTriggers
- `id` - Primary key
- `name` - Trigger name (e.g., "Job Created", "Job Status Update")
- `resource` - Resource type (jobs, invoices, estimates)
- `resource_actions` - JSON array of actions (create, update, delete)
- `resource_field` - Optional field name to monitor
- `resource_field_actions` - JSON array of field actions (none, in, out)
- `resource_subfield` - Optional subfield name to monitor
- `resource_subfield_actions` - JSON array of subfield actions (in, out)
- `created_at`, `updated_at`, `deleted_at` - Timestamps

### WebhookTriggerValues
- `id` - Primary key
- `company_id` - Foreign key to company
- `global_trigger_id` - Foreign key to global trigger
- `resource_value` - Resource value filter (defaults to global trigger's resource)
- `resource_action_value` - Action value filter (create, update, delete)
- `resource_field_values` - JSON array of field values to match
- `resource_field_action_value` - Field action filter (none, in, out)
- `resource_subfield_values` - JSON array of subfield values to match
- `resource_subfield_action_value` - Subfield action filter (in, out)
- `created_at`, `updated_at`, `deleted_at` - Timestamps

### WebhookRequests
- `id` - Primary key
- `company_id` - Foreign key to company
- `webhook_id` - Foreign key to webhook
- `webhook_url` - The URL that was called
- `request_data` - JSON data that was sent
- `response_status_code` - HTTP status code from response
- `response_data` - JSON response data
- `is_successful` - Boolean indicating success
- `created_at`, `updated_at`, `deleted_at` - Timestamps

## Usage

### Creating a Webhook

1. **Create a Global Trigger** (if it doesn't exist):
   ```bash
   php artisan create:webhook-trigger
   ```
   This interactive command will guide you through creating a new global trigger.

2. **Create a Trigger Value with Webhook**:
   ```json
   POST /webhook-trigger-value
   {
     "global_trigger_id": 1,
     "resource_action_value": "update",
     "resource_field_action_value": "in",
     "resource_field_values": ["status_id_1", "status_id_2"],
     "webhook_url": "https://example.com/webhook"
   }
   ```
   This will automatically create both the trigger value and the associated webhook.

3. **Or Create Webhook Separately**:
   ```json
   POST /webhook
   {
     "webhook_trigger_value_id": 1,
     "webhook_url": "https://example.com/webhook"
   }
   ```

### Webhook Payload Format

When a webhook is triggered, it sends a POST request with the following structure:

```json
{
  "trigger": "Job Status Update",
  "message": "UPDATE ON JOBS FOR status_workflow_id",
  "data": {
    "old_value": "old_status_id",
    "new_value": "new_status_id",
    "object": {
      "id": 123,
      "job_type": "service",
      "customer_id": 456,
      "status": "In Progress",
      ...
    }
  },
  "company": "Company_CompanyName_123"
}
```

### Security

Each webhook request includes an `X-Encrypted-Data` header containing an HMAC-SHA256 signature of the request body, signed with the webhook's secret key. This allows the receiving system to verify the authenticity of the webhook.

The signature is calculated as:
```
base64(hmac_sha256(json_encode(data), secret_key))
```

**Verification Example (PHP)**:
```php
$receivedSignature = $_SERVER['HTTP_X_ENCRYPTED_DATA'];
$payload = file_get_contents('php://input');
$calculatedSignature = base64_encode(hash_hmac('sha256', $payload, $secretKey, true));

if (hash_equals($receivedSignature, $calculatedSignature)) {
    // Webhook is authentic
}
```

## Configuration

### Queue Configuration

Webhook delivery is processed asynchronously via Laravel queues. Ensure your queue worker is running:

```bash
php artisan queue:work
```

The module uses the default queue connection specified in your Laravel configuration.

### Retry Logic

The `WebhookSendService` automatically retries failed webhook deliveries up to 3 times with a 1-second delay between attempts. After 3 failed attempts, the request is logged as unsuccessful.

## Event Flow

1. A resource (Job, Invoice, Estimate) is created, updated, or deleted
2. A `WebhookEvent` is fired with the object and action
3. The `MakeWebhookCall` listener processes the event
4. Changed attributes are detected (excluding timestamps)
5. `WebhookTriggerService` queries for matching global triggers and trigger values
6. If matches are found, `WebhookTriggersJob` is dispatched to the queue
7. The job processes each matching webhook and uses `WebhookSendService` to send HTTP requests
8. All requests and responses are logged in `WebhookRequest`

## Constants

The `WebhookConstants` class defines:

- **Resource Actions**: `create`, `update`, `delete`
- **Field Actions**: `skip`, `all`, `none`, `in`, `out`
- **Resources**: `jobs`, `invoices`, `estimates`
- **Event Names**: Predefined event names for various triggers:
  - Job events: Workflow Custom Status Update, Custom Status Update, Start Time Update, End Time Update, Created
  - Invoice events: Created, Workflow Custom Status Update, Custom Status Update
  - Estimate events: Created, Workflow Custom Status Update, Custom Status Update

## Development

### Adding New Triggers

1. Add the event name constant to `WebhookConstants`
2. Add the event name to the appropriate event name array (`RESOURCE_JOB_EVENT_NAMES` or `RESOURCE_ALL_EVENT_NAMES`)
3. Update the `MakeWebhookCall` listener to handle the new trigger in `runGlobalWebhookTrigger()`
4. Create the global trigger using the artisan command: `php artisan create:webhook-trigger`

### Testing

When testing webhooks locally, you can use tools like:
- [webhook.site](https://webhook.site) - Temporary webhook URL for testing
- [ngrok](https://ngrok.com) - Expose local server to the internet
- [RequestBin](https://requestbin.com) - HTTP request inspector

### Namespace

All classes use the `App\WebhookModule` namespace:
- Models: `App\WebhookModule\Models`
- Controllers: `App\WebhookModule\Http\Controllers`
- Services: `App\WebhookModule\Services`
- Jobs: `App\WebhookModule\Jobs`
- Events: `App\WebhookModule\Events`
- Listeners: `App\WebhookModule\Listeners`
- Commands: `App\WebhookModule\Commands`
- Constants: `App\WebhookModule\Constants`

## License

This module is part of the application.

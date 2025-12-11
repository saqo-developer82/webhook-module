<?php

namespace App\WebhookModule\Constants;

class WebhookConstants
{
    const RESOURCE_ACTION_ALL = 'all';
    const RESOURCE_ACTION_CREATE = 'create';
    const RESOURCE_ACTION_UPDATE = 'update';
    const RESOURCE_ACTION_DELETE = 'delete';

    const RESOURCE_ACTIONS = [
        self::RESOURCE_ACTION_CREATE,
        self::RESOURCE_ACTION_UPDATE,
        self::RESOURCE_ACTION_DELETE,
    ];

    const RESOURCE_ACTIONS_CHOICES = [
        self::RESOURCE_ACTION_ALL => 0,
        self::RESOURCE_ACTION_CREATE => 1,
        self::RESOURCE_ACTION_UPDATE => 2,
        self::RESOURCE_ACTION_DELETE => 3,
    ];

    const FIELD_ACTION_SKIP = 'skip';
    const FIELD_ACTION_ALL = 'all';
    const FIELD_ACTION_NONE = 'none';
    const FIELD_ACTION_IN = 'in';
    const FIELD_ACTION_OUT = 'out';

    const RESOURCE_FIELD_ACTIONS = [
        self::FIELD_ACTION_NONE,
        self::FIELD_ACTION_IN,
        self::FIELD_ACTION_OUT,
    ];

    const RESOURCE_FIELD_ACTIONS_CHOICES = [
        self::FIELD_ACTION_SKIP => 0,
        self::FIELD_ACTION_ALL => 1,
        self::FIELD_ACTION_NONE => 2,
        self::FIELD_ACTION_IN => 3,
        self::FIELD_ACTION_OUT => 4,
    ];

    const RESOURCE_SUBFIELD_ACTIONS = [
        self::FIELD_ACTION_IN,
        self::FIELD_ACTION_OUT,
    ];

    const RESOURCE_SUBFIELD_ACTIONS_CHOICES = [
        self::FIELD_ACTION_ALL => 1,
        self::FIELD_ACTION_IN => 2,
        self::FIELD_ACTION_OUT => 3,
    ];

    const RESOURCE_JOB = 'jobs';
    const RESOURCE_INVOICE = 'invoices';
    const RESOURCE_ESTIMATE = 'estimates';

    const RESOURCES = [
        self::RESOURCE_JOB,
        self::RESOURCE_INVOICE,
        self::RESOURCE_ESTIMATE,
    ];

    const RESOURCES_CHOICES = [
        self::RESOURCE_JOB     => 1,
        self::RESOURCE_INVOICE => 2,
        self::RESOURCE_ESTIMATE => 3,
    ];

    const RESOURCE_ACTION_ALL_RECORDS = '7DATAALLRECORDS7'; // This needs to be unique so it can't occur in data records

    const GLOBAL_WEBHOOK_CUSTOM_STATUS_UPDATE = 'Job Status Update';
    const RESOURCE_JOB_WORKFLOW_CUSTOM_STATUS_EVENT_NAME = 'Job Workflow Custom Status Update';
    const RESOURCE_JOB_CUSTOM_STATUS_EVENT_NAME = 'Job Custom Status Update';
    const RESOURCE_JOB_START_TIME_UPDATE_EVENT_NAME = 'Job Start Time Update';
    const RESOURCE_JOB_END_TIME_UPDATE_EVENT_NAME = 'Job End Time Update';
    const RESOURCE_JOB_CREATED_EVENT_NAME = 'Job Created';

    const INVOICE_CREATED = 'Invoice Created';

    const INVOICE_WORKFLOW_CUSTOM_STATUS_UPDATED = 'Invoice Workflow Custom Status Update';
    const INVOICE_CUSTOM_STATUS_UPDATED = 'Invoice Custom Status Update';

    const ESTIMATE_CREATED = 'Estimate Created';

    const ESTIMATE_WORKFLOW_CUSTOM_STATUS_UPDATED = 'Estimate Workflow Custom Status Update';
    const ESTIMATE_CUSTOM_STATUS_UPDATED = 'Estimate Custom Status Update';

    const RESOURCE_JOB_EVENT_NAMES = [
        self::RESOURCE_JOB_WORKFLOW_CUSTOM_STATUS_EVENT_NAME,
        self::RESOURCE_JOB_CUSTOM_STATUS_EVENT_NAME,
        self::RESOURCE_JOB_START_TIME_UPDATE_EVENT_NAME,
        self::RESOURCE_JOB_END_TIME_UPDATE_EVENT_NAME,
        self::RESOURCE_JOB_CREATED_EVENT_NAME,
    ];

    const RESOURCE_ALL_EVENT_NAMES = [
        self::RESOURCE_JOB_WORKFLOW_CUSTOM_STATUS_EVENT_NAME,
        self::RESOURCE_JOB_CUSTOM_STATUS_EVENT_NAME,
        self::RESOURCE_JOB_START_TIME_UPDATE_EVENT_NAME,
        self::RESOURCE_JOB_END_TIME_UPDATE_EVENT_NAME,
        self::RESOURCE_JOB_CREATED_EVENT_NAME,
        self::INVOICE_CREATED,
        self::INVOICE_WORKFLOW_CUSTOM_STATUS_UPDATED,
        self::INVOICE_CUSTOM_STATUS_UPDATED,
        self::ESTIMATE_CREATED,
        self::ESTIMATE_WORKFLOW_CUSTOM_STATUS_UPDATED,
        self::ESTIMATE_CUSTOM_STATUS_UPDATED,
    ];

    const SUBFIELD_REQUIRED_TRIGGERS = [
        self::INVOICE_WORKFLOW_CUSTOM_STATUS_UPDATED,
        self::ESTIMATE_WORKFLOW_CUSTOM_STATUS_UPDATED,
        self::RESOURCE_JOB_WORKFLOW_CUSTOM_STATUS_EVENT_NAME,
    ];
}

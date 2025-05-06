<?php

namespace App\WebhookModule\Constants;

class WebhookConstants
{
    const RESOURCE_ACTION_CREATE = 'create';
    const RESOURCE_ACTION_UPDATE = 'update';
    const RESOURCE_ACTION_DELETE = 'delete';

    const RESOURCE_ACTIONS = [
        self::RESOURCE_ACTION_CREATE,
        self::RESOURCE_ACTION_UPDATE,
        self::RESOURCE_ACTION_DELETE,
    ];

    const FIELD_ACTION_NONE = 'none';
    const FIELD_ACTION_IN = 'in';
    const FIELD_ACTION_OUT = 'out';

    const RESOURCE_FIELD_ACTIONS = [
        self::FIELD_ACTION_NONE,
        self::FIELD_ACTION_IN,
        self::FIELD_ACTION_OUT,
    ];

    const RESOURCE_SUBFIELD_ACTIONS = [
        self::FIELD_ACTION_IN,
        self::FIELD_ACTION_OUT,
    ];

    const RESOURCE_JOB = 'jobs';

    const RESOURCE_ACTION_ALL_RECORDS = '7DATAALLRECORDS7'; // This needs to be unique so it can't occur in data records

    const GLOBAL_WEBHOOK_CUSTOM_STATUS_UPDATE = 'Job Status Update';
    const RESOURCE_JOB_WORKFLOW_CUSTOM_STATUS_EVENT_NAME = 'Job Workflow Custom Status Update';
    const RESOURCE_JOB_CUSTOM_STATUS_EVENT_NAME = 'Job Custom Status Update';
    const RESOURCE_JOB_START_TIME_UPDATE_EVENT_NAME = 'Job Start Time Update';
    const RESOURCE_JOB_END_TIME_UPDATE_EVENT_NAME = 'Job End Time Update';
    const RESOURCE_JOB_CREATED_EVENT_NAME = 'Job Created';

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
    ];
}

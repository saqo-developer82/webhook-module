<?php

namespace App\WebhookModule\Events;

use Illuminate\Queue\SerializesModels;

class WebhookEvent
{

    use SerializesModels;

    public $object;
    public $action;

    public function __construct($object, $action)
    {
        $this->object   = $object;
        $this->action   = $action;
    }
}

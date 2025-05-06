<?php

namespace App\WebhookModule;

use App\WebhookModule\Commands\CreateNewWebhookGlobalTrigger;

class ServiceProvider extends \App\BaseServiceProvider
{
    public function boot()
    {
        parent::boot();

        $this->commands([
            CreateNewWebhookGlobalTrigger::class
        ]);

        $this->app->booted(function () {
        });
    }
}

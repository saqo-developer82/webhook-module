<?php

namespace App\WebhookModule\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Traits\Scopes;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/* Internal Models */



class WebhookTriggerValue extends Model
{

    use SoftDeletes;
    use Scopes;

    /* --------------------------
                    Database
    -------------------------- */
    protected $connection = 'aurora_postgres';
    protected $table = 'WebhookTriggerValues';

    /* --------------------------
            Attribute Assignment
    -------------------------- */
    protected $fillable = [
        'company_id',
        'global_trigger_id',
        'resource_value',
        'resource_action_value',
        'resource_field_values',
        'resource_field_action_value',
        'resource_subfield_values',
        'resource_subfield_action_value'
    ];

    protected $with = [
        'webhook'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at'                => 'date:Y-m-d H:i:s',
            'updated_at'                => 'date:Y-m-d H:i:s',
            'deleted_at'                => 'date:Y-m-d H:i:s',
            'resource_field_values'     => 'array',
            'resource_subfield_values'  => 'array',
        ];
    }

    /*****
     *  RELATIONS
     */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function globalTrigger(): BelongsTo
    {
        return $this->belongsTo(WebhookGlobalTrigger::class, 'global_trigger_id');
    }

    public function webhook(): HasOne
    {
        return $this->hasOne(Webhook::class, 'webhook_trigger_value_id');
    }

    public function AttributeHandler(array $data, bool $save = true): void
    {
        if (Auth::user()) {
            $company_id = Auth::user()->company_id;
        }

        if (isset($data['company_id'])) {
            $company_id = $data['company_id'];
        }

        if (isset($company_id)) {
            $this->company()->associate($company_id);
        }

        if (isset($data['global_trigger_id'])) {
            $this->global_trigger_id = $data['global_trigger_id'];

            $this->globalTrigger()->associate($this->global_trigger_id);
        }

        if (isset($data['resource_value'])) {
            $resource_value = trim($data['resource_value']);

            if (!empty($resource_value)) {
                $this->resource_value = $resource_value;
            }
        } else {
            $this->resource_value = $this->globalTrigger->resource; // by deafult set the resource name of global trigger
        }

        if (isset($data['resource_action_value'])) {
            $resource_action_value = trim($data['resource_action_value']);

            if (!empty($resource_action_value)) {
                $this->resource_action_value = $resource_action_value;
            }
        }

        if (isset($data['resource_field_action_value'])) {
            $resource_field_action_value= trim($data['resource_field_action_value']);

            if (!empty($resource_field_action_value)) {
                $this->resource_field_action_value = $resource_field_action_value;
            }
        }

        if (!empty($data['resource_field_values'])) {
            $this->resource_field_values = $data['resource_field_values'];
        }

        if (isset($data['resource_subfield_values'])) {
            $this->resource_subfield_values = $data['resource_subfield_values'];
        }

        if (isset($data['resource_subfield_action_value'])) {
            $this->resource_subfield_action_value = $data['resource_subfield_action_value'];
        }

        if ($save) {
            $this->save();
        }
    }
}

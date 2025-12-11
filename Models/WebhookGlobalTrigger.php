<?php

namespace App\WebhookModule\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Scopes;

class WebhookGlobalTrigger extends Model
{
    use SoftDeletes;
    use Scopes;

    /* --------------------------
                    Database
    -------------------------- */
    protected $connection = 'aurora_postgres';
    protected $table = 'WebhookGlobalTriggers';

    /* --------------------------
            Attribute Assignment
    -------------------------- */
    protected $fillable = [
        'name',
        'resource',
        'resource_actions',
        'resource_field',
        'resource_field_actions',
        'resource_subfield',
        'resource_subfield_actions'
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
            'resource_actions'          => 'array',
            'resource_field_actions'    => 'array',
            'resource_subfield_actions' => 'array',
        ];
    }

    /*****
     *  RELATIONS
     */

    public function webhookTriggerValues(): HasMany
    {
        return $this->hasMany(WebhookTriggerValue::class, 'global_trigger_id');
    }

    /**
     * @param array $data
     * @param boolean $save
     * @return $this
     */
    public function AttributeHandler($data, $save = true)
    {
        if (isset($data['name'])) {
            $name = trim($data['name']);

            if (!empty($name)) {
                $this->name = $name;
            }
        }

        if (isset($data['resource'])) {
            $resource = trim($data['resource']);

            if (!empty($resource)) {
                $this->resource = $resource;
            }
        }

        if (!empty($data['resource_actions'])) {
            $this->resource_actions = $data['resource_actions'];
        }

        if (isset($data['resource_field'])) {
            $this->resource_field = $data['resource_field'];
        }

        if (isset($data['resource_field_actions'])) {
            $this->resource_field_actions = $data['resource_field_actions'];
        }

        if (isset($data['resource_subfield'])) {
            $this->resource_subfield = $data['resource_subfield'];
        }

        if (isset($data['resource_subfield_actions'])) {
            $this->resource_subfield_actions = $data['resource_subfield_actions'];
        }

        if ($save) {
            $this->save();
        }

        return $this;
    }
}

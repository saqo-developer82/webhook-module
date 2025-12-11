<?php

namespace App\WebhookModule\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Traits\Scopes;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/* Internal Models */

class Webhook extends Model
{

    use SoftDeletes;
    use Scopes;

    /* --------------------------
                    Database
    -------------------------- */
    protected $connection = 'aurora_postgres';
    protected $table = 'Webhooks';

    /* --------------------------
            Attribute Assignment
    -------------------------- */
    protected $fillable = [
        'company_id',
        'user_id',
        'webhook_trigger_value_id',
        'webhook_url',
        'secret_key'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'date:Y-m-d H:i:s',
            'updated_at' => 'date:Y-m-d H:i:s',
            'deleted_at' => 'date:Y-m-d H:i:s',
        ];
    }

    protected static function booted()
    {
        self::saving(function($model) {
            if (empty($model->secret_key)) {
                $model->secret_key = bin2hex(random_bytes(32));
            }
        });
    }

    /*****
     *  RELATIONS
     */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function webhookTriggerValue(): BelongsTo
    {
        return $this->belongsTo(WebhookTriggerValue::class, 'webhook_trigger_value_id');
    }

    public function webhookRequests(): HasMany
    {
        return $this->hasMany(WebhookRequest::class, 'webhook_id');
    }

    public function AttributeHandler(array $data, bool $save = true): void
    {
        if (Auth::user()) {
            $company_id = Auth::user()->company_id;
            $user_id = Auth::user()->id;
        }

        if (isset($data['company_id'])) {
            $company_id = $data['company_id'];
        }

        if (isset($data['user_id'])) {
            $user_id = $data['user_id'];
        }

        if (isset($company_id)) {
            $this->company()->associate($company_id);
        }

        if (isset($user_id)) {
            $this->user()->associate($user_id);
        }

        if (isset($data['webhook_trigger_value_id'])) {
            $this->webhook_trigger_value_id = $data['webhook_trigger_value_id'];

            $this->webhookTriggerValue()->associate($this->webhook_trigger_value_id);
        }

        if (isset($data['webhook_url'])) {
            $webhook_url = trim($data['webhook_url']);

            if (!empty($webhook_url)) {
                $this->webhook_url = $webhook_url;
            }
        }

        if ($save) {
            $this->save();
        }
    }
}

<?php

namespace App\WebhookModule\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Utility;
use App\Traits\Scopes;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/* Internal Models */

class WebhookRequest extends Model
{

    use SoftDeletes;
    use Scopes;

    /* --------------------------
                    Database
    -------------------------- */
    protected $connection = 'aurora_postgres';
    protected $table = 'WebhookRequests';

    /* --------------------------
            Attribute Assignment
    -------------------------- */
    protected $fillable = [
        'company_id',
        'webhook_id',
        'is_successful',
        'webhook_url',
        'response_status_code',
        'request_data',
        'response_data'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at'    => 'date:Y-m-d H:i:s',
            'updated_at'    => 'date:Y-m-d H:i:s',
            'deleted_at'    => 'date:Y-m-d H:i:s',
            'request_data'  => 'array',
            'response_data' => 'array',
            'is_successful' => 'boolean',
        ];
    }

    /*****
     *  RELATIONS
     */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class, 'webhook_id');
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

        if (isset($data['webhook_id'])) {
            $this->webhook_id = $data['webhook_id'];

            $this->webhook()->associate($this->webhook_id);
        }

        if (isset($data['is_successful'])) {
            $this->is_successful = Utility::format('boolean', $data['is_successful']);
        }

        if (isset($data['webhook_url'])) {
            $webhook_url = trim($data['webhook_url']);

            if (!empty($webhook_url)) {
                $this->webhook_url = $webhook_url;
            }
        }

        if (isset($data['response_status_code'])) {
            $response_status_code = trim($data['response_status_code']);

            if (!empty($response_status_code)) {
                $this->response_status_code = $response_status_code;
            }
        }

        if (!empty($data['request_data'])) {
            $this->request_data = $data['request_data'];
        }

        if (isset($data['response_data'])) {
            $this->response_data = $data['response_data'];
        }

        if ($save) {
            $this->save();
        }
    }
}

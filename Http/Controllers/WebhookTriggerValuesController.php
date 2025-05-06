<?php

namespace App\WebhookModule\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\WebhookModule\Models\BaseWebhook;
use App\WebhookModule\Models\BaseWebhookTriggerValue;
use App\WebhookModule\Requests\WebhookTriggerValues\CreateRequest as TriggerValueCreateRequest;
use App\WebhookModule\Requests\WebhookTriggerValues\UpdateRequest as TriggerValueUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class WebhookTriggerValuesController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function Enum(Request $request): JsonResponse
    {
        $trigger_values = (new BaseWebhookTriggerValue())
            ->EnumScope($request)
            ->get();

        return Response::json([
            'error' => false,
            'result_count' => $trigger_values->count(),
            'response' => $trigger_values
        ], 200);
    }

    /**
     * @param $id
     */
    public function Find(Request $request, $id): JsonResponse
    {
        $trigger_value = BaseWebhookTriggerValue::Id($id)
            ->FindScope($request)
            ->firstOrFail();

        return Response::json([
            'error' => false,
            'response' => $trigger_value
        ], 200);
    }

    public function Create(TriggerValueCreateRequest $request): JsonResponse
    {
        $trigger_value = new BaseWebhookTriggerValue();
        $trigger_value->AttributeHandler($request->all());

        if ($trigger_value->id) {
            (new BaseWebhook())->AttributeHandler([
                'webhook_trigger_value_id' => $trigger_value->id,
                'webhook_url' => $request->get('webhook_url')
            ]);
        }

        $response = BaseWebhookTriggerValue::Id($trigger_value->id)->first();

        return Response::json([
            'error' => false,
            'response' => $response
        ], 201);
    }

    /**
     * @param $id
     */
    public function Update(TriggerValueUpdateRequest $request, $id): JsonResponse
    {
        $trigger_value = (new BaseWebhookTriggerValue())
            ->Id($id)
            ->firstOrFail();

        $trigger_value->AttributeHandler($request->all());

        if ($request->filled('webhook_url')) {
            (new BaseWebhook())->AttributeHandler([
                'webhook_trigger_value_id' => $trigger_value->id,
                'webhook_url' => $request->get('webhook_url')
            ]);
        }

        $response = BaseWebhookTriggerValue::Id($trigger_value->id)->first();

        return Response::json([
            'error' => false,
            'response' => $response
        ], 200);
    }

    /**
     * @param $id
     */
    public function Delete(Request $request, $id): JsonResponse
    {
        $trigger_value = (new BaseWebhookTriggerValue())
            ->CompanyScope()
            ->Id($id)
            ->firstOrFail();

        $trigger_value->delete();

        /**
         * Response
         */
        $trigger_value = BaseWebhookTriggerValue::
        withTrashed()
            ->Id($trigger_value->id)
            ->first();

        return Response::json([
            'error' => false,
            'response' => $trigger_value,
        ], 200);
    }

}

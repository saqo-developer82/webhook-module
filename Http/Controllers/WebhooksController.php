<?php

namespace App\WebhookModule\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\WebhookModule\Models\BaseWebhook;
use App\WebhookModule\Requests\Webhooks\CreateRequest as WebhookCreateRequest;
use App\WebhookModule\Requests\Webhooks\UpdateRequest as WebhookUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class WebhooksController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function Enum(Request $request): JsonResponse
    {
        $webhooks = (new BaseWebhook())
            ->EnumScope($request)
            ->get();

        return Response::json([
            'error' => false,
            'result_count' => $webhooks->count(),
            'response' => $webhooks
        ], 200);
    }

    /**
     * @param $id
     */
    public function Find(Request $request, $id): JsonResponse
    {
        $webhook = BaseWebhook::Id($id)
            ->FindScope($request)
            ->firstOrFail();

        return Response::json([
            'error' => false,
            'response' => $webhook
        ], 200);
    }

    public function Create(WebhookCreateRequest $request): JsonResponse
    {
        $webhook = new BaseWebhook();
        $webhook->AttributeHandler($request->all());

        return Response::json([
            'error' => false,
            'response' => $webhook
        ], 201);
    }

    /**
     * @param $id
     */
    public function Update(WebhookUpdateRequest $request, $id): JsonResponse
    {
        $webhook = (new BaseWebhook())
            ->Id($id)
            ->firstOrFail();

        $webhook->AttributeHandler($request->all());

        return Response::json([
            'error' => false,
            'response' => $webhook
        ], 200);
    }

    /**
     * @param $id
     */
    public function Delete(Request $request, $id): JsonResponse
    {
        $webhook = (new BaseWebhook())
            ->CompanyScope()
            ->Id($id)
            ->firstOrFail();

        $webhook->delete();

        /**
         * Response
         */
        $webhook = BaseWebhook::
        withTrashed()
            ->Id($webhook->id)
            ->first();

        return Response::json([
            'error' => false,
            'response' => $webhook,
        ], 200);
    }

}

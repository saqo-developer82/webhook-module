<?php

namespace App\WebhookModule\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\WebhookModule\Models\WebhookGlobalTrigger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class WebhookGlobalTriggersController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function Enum(): JsonResponse
    {
        $triggers = (new WebhookGlobalTrigger())
            ->get();

        return Response::json([
            'error' => false,
            'result_count' => $triggers->count(),
            'response' => $triggers
        ], 200);
    }

    /**
     * @param $id
     */
    public function Find($id): JsonResponse
    {
        $trigger = WebhookGlobalTrigger::Id($id)
            ->firstOrFail();

        return Response::json([
            'error' => false,
            'response' => $trigger
        ], 200);
    }
}

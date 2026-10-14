<?php

namespace Farapayamak\Laravel\Http\Controllers;

use Farapayamak\Laravel\Events\FarapayamakMessageReceived;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;

class FarapayamakWebhookController extends Controller
{
    /**
     * Handle incoming SMS notification callback from Farapayamak.
     */
    public function handle(Request $request): JsonResponse
    {
        $from = (string) ($request->input('from') ?? $request->input('sender') ?? '');
        $to   = (string) ($request->input('to') ?? $request->input('receptor') ?? '');
        $msg  = (string) ($request->input('msg') ?? $request->input('message') ?? $request->input('text') ?? '');
        $id   = (string) ($request->input('msgid') ?? $request->input('id') ?? '');

        Event::dispatch(new FarapayamakMessageReceived(
            from: $from,
            to: $to,
            message: $msg,
            msgId: $id ?: null,
            raw: $request->all()
        ));

        return response()->json(['status' => 'OK']);
    }
}

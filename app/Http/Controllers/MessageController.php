<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Models\Conversation;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    private MessageService $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    /**
     * Store a newly created message in storage.
     */
    public function store(StoreMessageRequest $request, Conversation $conversation): JsonResponse
    {
        $message = $this->messageService->sendMessage(
            $request->user(),
            $conversation,
            $request->validated('body')
        );

        return response()->json([
            'message' => 'Message sent successfully.',
            'data' => $message
        ], 201);
    }
}

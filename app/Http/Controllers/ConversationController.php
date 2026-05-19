<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConversationRequest;
use App\Models\Conversation;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    private ConversationService $conversationService;

    public function __construct(ConversationService $conversationService)
    {
        $this->conversationService = $conversationService;
    }

    /**
     * Display a listing of the user's conversations.
     */
    public function index(Request $request): JsonResponse
    {
        $conversations = $this->conversationService->getConversationsForUser($request->user());
        return response()->json(['conversations' => $conversations]);
    }

    /**
     * Store a newly created conversation in storage.
     */
    public function store(StoreConversationRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        
        $isGroup = $validated['is_group'] ?? false;
        $participantIds = $validated['participant_ids'];

        if ($isGroup) {
            $conversation = $this->conversationService->createGroup($user, $participantIds, $validated['name']);
        } else {
            // Direct conversation assumes exactly 1 other participant
            $recipientId = collect($participantIds)->reject(fn($id) => $id === $user->id)->first();
            $conversation = $this->conversationService->findOrCreateDirect($user, $recipientId);
        }

        $conversation->load(['participants:id,name', 'latestMessage.sender']);

        return response()->json([
            'message' => 'Conversation retrieved successfully.',
            'conversation' => $conversation
        ]);
    }

    /**
     * Display the specified conversation.
     * In the context of the SPA, this can be used to load initial messages or details.
     */
    public function show(Conversation $conversation, Request $request): JsonResponse
    {
        // Ensure user is participant
        $this->authorizeParticipant($conversation, $request->user());

        $messages = app(\App\Services\MessageService::class)->getMessages($conversation);

        $conversation->load(['participants:id,name']);

        return response()->json([
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    /**
     * Mark the conversation as read by the authenticated user.
     */
    public function markAsRead(Conversation $conversation, Request $request): JsonResponse
    {
        $this->authorizeParticipant($conversation, $request->user());

        $lastReadAt = $this->conversationService->markAsRead($request->user(), $conversation);

        return response()->json(['success' => true, 'last_read_at' => $lastReadAt]);
    }

    private function authorizeParticipant(Conversation $conversation, \App\Models\User $user): void
    {
        if (!$conversation->participants()->where('users.id', $user->id)->exists()) {
            abort(403, 'Unauthorized access to conversation.');
        }
    }
}

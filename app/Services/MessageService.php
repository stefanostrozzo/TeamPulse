<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

class MessageService
{
    /**
     * Get paginated messages for a conversation.
     */
    public function getMessages(Conversation $conversation, int $perPage = 30)
    {
        return $conversation->messages()
            ->with(['sender:id,name'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Send a new message in a conversation.
     */
    public function sendMessage(User $sender, Conversation $conversation, string $body): Message
    {
        // 1. Create message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'body' => $body,
        ]);

        // 2. Load relationships for broadcast
        $message->load('sender:id,name');

        // 3. Update conversation's last_message_at
        $conversation->update(['last_message_at' => now()]);

        // 4. Mark as read for the sender
        $conversation->participants()->updateExistingPivot($sender->id, [
            'last_read_at' => now()
        ]);

        // 5. Broadcast event
        broadcast(new MessageSent($message, $conversation->id))->toOthers();

        return $message;
    }

    /**
     * Get the total count of unread conversations for the given user.
     */
    public function getUnreadCountForUser(User $user): int
    {
        return $user->conversations()
            ->where(function ($q) {
                $q->whereNull('conversation_participants.last_read_at')
                  ->orWhereColumn('conversations.last_message_at', '>', 'conversation_participants.last_read_at');
            })
            ->count();
    }
}

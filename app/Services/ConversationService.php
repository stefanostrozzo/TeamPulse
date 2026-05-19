<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ConversationService
{
    /**
     * Get user's conversations with the latest message and participant info.
     */
    public function getConversationsForUser(User $user): Collection
    {
        return Conversation::forUser($user->id)
            ->with(['participants' => function ($q) {
                $q->select('users.id', 'users.name');
            }, 'latestMessage.sender'])
            ->orderBy('last_message_at', 'desc')
            ->get();
    }

    /**
     * Find an existing 1-on-1 direct conversation or create a new one.
     */
    public function findOrCreateDirect(User $user, int $recipientId): \Illuminate\Database\Eloquent\Builder|Conversation
    {
        // Find existing direct conversation
        $conversation = Conversation::where('is_group', false)
            ->whereHas('participants', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->whereHas('participants', function ($q) use ($recipientId) {
                $q->where('users.id', $recipientId);
            })
            ->first();

        if ($conversation) {
            return $conversation;
        }

        // Create new direct conversation
        $conversation = Conversation::create([
            'is_group' => false,
            'created_by' => $user->id,
            'last_message_at' => now(),
        ]);

        $conversation->participants()->attach([
            $user->id => ['last_read_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            $recipientId => ['last_read_at' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        return $conversation;
    }

    /**
     * Create a group conversation.
     */
    public function createGroup(User $user, array $participantIds, string $name): Conversation
    {
        $conversation = Conversation::create([
            'name' => $name,
            'is_group' => true,
            'created_by' => $user->id,
            'last_message_at' => now(),
        ]);

        // Ensure creator is in the participants list
        if (!in_array($user->id, $participantIds)) {
            $participantIds[] = $user->id;
        }

        $syncData = [];
        foreach ($participantIds as $id) {
            // Creator gets marked as read immediately
            $syncData[$id] = [
                'last_read_at' => $id === $user->id ? now() : null,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        $conversation->participants()->attach($syncData);

        return $conversation;
    }

    /**
     * Mark a conversation as read for the given user and broadcast a read receipt.
     */
    public function markAsRead(User $user, Conversation $conversation): string
    {
        $lastReadAt = now()->toIso8601String();

        $conversation->participants()->updateExistingPivot($user->id, [
            'last_read_at' => $lastReadAt,
        ]);

        // Broadcast the read receipt to other participants so their UI can update
        broadcast(new \App\Events\MessageRead($user->id, $conversation->id, $lastReadAt))->toOthers();

        return $lastReadAt;
    }
}

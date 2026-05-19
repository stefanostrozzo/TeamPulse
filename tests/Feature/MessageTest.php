<?php

namespace Tests\Feature;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use DatabaseTransactions;
    public function test_participant_can_send_message(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $conversation = Conversation::create([
            'is_group' => false,
            'created_by' => $user1->id,
            'last_message_at' => now()->subDay(),
        ]);
        $conversation->participants()->attach([$user1->id, $user2->id]);

        $response = $this->actingAs($user1)->postJson("/messaging/conversations/{$conversation->id}/messages", [
            'body' => 'Hello World!',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.body', 'Hello World!');

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $user1->id,
            'body' => 'Hello World!',
        ]);

        $conversation->refresh();
        $this->assertTrue($conversation->last_message_at->isToday());
    }

    public function test_message_body_cannot_be_whitespace_only(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $conversation = Conversation::create([
            'is_group' => false,
            'created_by' => $user1->id,
        ]);
        $conversation->participants()->attach([$user1->id, $user2->id]);

        $response = $this->actingAs($user1)->postJson("/messaging/conversations/{$conversation->id}/messages", [
            'body' => '   ',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('body');
    }

    public function test_non_participant_cannot_send_message(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $intruder = User::factory()->create();

        $conversation = Conversation::create([
            'is_group' => false,
            'created_by' => $user1->id,
        ]);
        $conversation->participants()->attach([$user1->id, $user2->id]);

        $response = $this->actingAs($intruder)->postJson("/messaging/conversations/{$conversation->id}/messages", [
            'body' => 'Intruder alert!',
        ]);

        $response->assertStatus(403);
    }

    public function test_message_sent_event_is_dispatched(): void
    {
        Event::fake([MessageSent::class]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $conversation = Conversation::create([
            'is_group' => false,
            'created_by' => $user1->id,
        ]);
        $conversation->participants()->attach([$user1->id, $user2->id]);

        $this->actingAs($user1)->postJson("/messaging/conversations/{$conversation->id}/messages", [
            'body' => 'Trigger event!',
        ]);

        Event::assertDispatched(MessageSent::class , function ($event) use ($conversation) {
            return $event->conversationId === $conversation->id && $event->message->body === 'Trigger event!';
        });
    }

    public function test_user_can_load_conversation_messages(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $conversation = Conversation::create([
            'is_group' => false,
            'created_by' => $user1->id,
        ]);
        $conversation->participants()->attach([$user1->id, $user2->id]);

        $conversation->messages()->create([
            'sender_id' => $user1->id,
            'body' => 'Test message',
        ]);

        $response = $this->actingAs($user1)->getJson("/messaging/conversations/{$conversation->id}");

        $response->assertStatus(200)
            ->assertJsonPath('messages.data.0.body', 'Test message');
    }

    public function test_unread_count_scopes_to_user_conversations_only(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $other = User::factory()->create();

        // Convo A: user1+user2. Unread for user1 — last_message_at > user1's last_read_at.
        $convoA = Conversation::create([
            'is_group' => false,
            'created_by' => $user1->id,
            'last_message_at' => now(),
        ]);
        $convoA->participants()->attach([
            $user1->id => ['last_read_at' => now()->subHour()],
            $user2->id => ['last_read_at' => now()],
        ]);

        // Convo B: user1+user2. Read — user1's last_read_at >= last_message_at.
        $convoB = Conversation::create([
            'is_group' => false,
            'created_by' => $user1->id,
            'last_message_at' => now()->subDay(),
        ]);
        $convoB->participants()->attach([
            $user1->id => ['last_read_at' => now()],
            $user2->id => ['last_read_at' => now()],
        ]);

        // Convo C: user1 NOT a participant. Should never count toward user1.
        $convoC = Conversation::create([
            'is_group' => false,
            'created_by' => $other->id,
            'last_message_at' => now(),
        ]);
        $convoC->participants()->attach([
            $other->id => ['last_read_at' => now()->subHour()],
            $user2->id => ['last_read_at' => now()],
        ]);

        $service = app(\App\Services\MessageService::class);
        $count = $service->getUnreadCountForUser($user1);

        $this->assertEquals(1, $count);
    }

    public function test_mark_conversation_as_read_updates_pivot(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $conversation = Conversation::create([
            'is_group' => false,
            'created_by' => $user1->id,
        ]);
        $conversation->participants()->attach([
            $user1->id => ['last_read_at' => null],
            $user2->id => ['last_read_at' => null],
        ]);

        $response = $this->actingAs($user1)->postJson("/messaging/conversations/{$conversation->id}/read");

        $response->assertStatus(200);

        $pivot = $conversation->participants()->where('users.id', $user1->id)->first()->pivot;
        $this->assertNotNull($pivot->last_read_at);
    }
}

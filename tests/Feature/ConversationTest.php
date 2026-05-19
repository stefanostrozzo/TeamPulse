<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ConversationTest extends TestCase
{
    use DatabaseTransactions;
    public function test_user_can_list_their_conversations(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Seed a direct conversation
        $conversation = Conversation::create([
            'is_group' => false,
            'created_by' => $user1->id,
            'last_message_at' => now(),
        ]);
        $conversation->participants()->attach([$user1->id, $user2->id]);

        $response = $this->actingAs($user1)->getJson('/messaging/conversations');

        $response->assertStatus(200)
            ->assertJsonPath('conversations.0.id', $conversation->id);
    }

    public function test_user_can_create_direct_conversation(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->actingAs($user1)->postJson('/messaging/conversations', [
            'participant_ids' => [$user2->id],
            'is_group' => false,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('conversations', [
            'is_group' => false,
            'created_by' => $user1->id,
        ]);
        $this->assertEquals(2, Conversation::first()->participants()->count());
    }

    public function test_user_can_create_group_conversation(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $response = $this->actingAs($user1)->postJson('/messaging/conversations', [
            'participant_ids' => [$user2->id, $user3->id],
            'is_group' => true,
            'name' => 'Developers',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('conversation.name', 'Developers')
            ->assertJsonPath('conversation.is_group', true);

        $this->assertEquals(3, Conversation::first()->participants()->count());
    }

    public function test_duplicate_direct_conversation_reuses_existing(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create first conversation
        $this->actingAs($user1)->postJson('/messaging/conversations', [
            'participant_ids' => [$user2->id],
            'is_group' => false,
        ]);

        $firstConversationId = Conversation::first()->id;

        // Try to create another one with the same participant
        $response = $this->actingAs($user1)->postJson('/messaging/conversations', [
            'participant_ids' => [$user2->id],
            'is_group' => false,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('conversation.id', $firstConversationId);

        $this->assertEquals(1, Conversation::count());
    }

    public function test_direct_conversation_with_only_self_returns_validation_error(): void
    {
        $user1 = User::factory()->create();

        $response = $this->actingAs($user1)->postJson('/messaging/conversations', [
            'participant_ids' => [$user1->id],
            'is_group' => false,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('participant_ids');
    }

    public function test_unauthorized_user_cannot_see_others_conversations(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $intruder = User::factory()->create();

        // Seed a direct conversation between user1 and user2
        $conversation = Conversation::create([
            'is_group' => false,
            'created_by' => $user1->id,
            'last_message_at' => now(),
        ]);
        $conversation->participants()->attach([$user1->id, $user2->id]);

        $response = $this->actingAs($intruder)->getJson("/messaging/conversations/{$conversation->id}");

        $response->assertStatus(403);
    }
}

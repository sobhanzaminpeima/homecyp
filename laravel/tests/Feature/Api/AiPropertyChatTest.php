<?php

namespace Tests\Feature\Api;

use App\Models\BusinessProject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiPropertyChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_finds_real_matching_properties_without_an_api_key(): void
    {
        config(['services.openai.key' => null]);
        $daily = BusinessProject::factory()->create(['status' => 'approved', 'listing_type' => 'rent', 'rental_period' => 'daily', 'property_type' => 'villa']);
        BusinessProject::factory()->create(['status' => 'approved', 'listing_type' => 'sale', 'property_type' => 'apartment']);

        $response = $this->postJson('/api/v1/ai/chat', [
            'message' => 'I need a villa for daily Airbnb rent',
            'locale' => 'en',
        ]);

        $response->assertOk()->assertJsonPath('filters.listing_type', 'rent')->assertJsonPath('filters.rental_period', 'daily');
        $this->assertSame([$daily->id], collect($response->json('properties'))->pluck('id')->all());
        $this->assertDatabaseCount('ai_conversations', 1);
        $this->assertDatabaseCount('ai_messages', 2);
    }

    public function test_chat_uses_responses_api_when_configured(): void
    {
        config(['services.openai.key' => 'test-key', 'services.openai.model' => 'gpt-5.4-mini']);
        Http::fake(['api.openai.com/*' => Http::response(['output' => [['type' => 'message', 'content' => [['type' => 'output_text', 'text' => 'Here are your verified options.']]]]], 200)]);

        $this->postJson('/api/v1/ai/chat', ['message' => 'Show me property for sale', 'locale' => 'en'])
            ->assertOk()->assertJsonPath('message', 'Here are your verified options.');

        Http::assertSent(fn ($request) => $request->url() === 'https://api.openai.com/v1/responses' && $request['model'] === 'gpt-5.4-mini');
    }

    public function test_existing_conversation_keeps_context(): void
    {
        config(['services.openai.key' => null]);
        $first = $this->postJson('/api/v1/ai/chat', ['message' => 'I want to rent an apartment', 'locale' => 'en'])->assertOk();
        $this->postJson('/api/v1/ai/chat', ['conversation_id' => $first->json('conversation_id'), 'message' => 'Make it daily', 'locale' => 'en'])
            ->assertOk()->assertJsonPath('filters.property_type', 'apartment')->assertJsonPath('filters.rental_period', 'daily');
    }

    public function test_persian_digits_and_budget_units_are_understood(): void
    {
        config(['services.openai.key' => null]);
        $matching = BusinessProject::factory()->create([
            'status' => 'approved',
            'listing_type' => 'sale',
            'property_type' => 'apartment',
            'bedrooms' => 2,
            'price' => 145000,
        ]);
        BusinessProject::factory()->create([
            'status' => 'approved',
            'listing_type' => 'sale',
            'property_type' => 'apartment',
            'bedrooms' => 2,
            'price' => 170000,
        ]);

        $response = $this->postJson('/api/v1/ai/chat', [
            'message' => 'آپارتمان ۲ خوابه برای خرید با بودجه زیر ۱۵۰ هزار پوند',
            'locale' => 'fa',
        ]);

        $response->assertOk()
            ->assertJsonPath('filters.listing_type', 'sale')
            ->assertJsonPath('filters.property_type', 'apartment')
            ->assertJsonPath('filters.min_bedrooms', 2)
            ->assertJsonPath('filters.max_price', 150000);
        $this->assertSame([$matching->id], collect($response->json('properties'))->pluck('id')->all());
    }

    public function test_history_restores_property_cards(): void
    {
        config(['services.openai.key' => null]);
        $property = BusinessProject::factory()->create([
            'status' => 'approved',
            'listing_type' => 'rent',
            'rental_period' => 'daily',
        ]);

        $chat = $this->postJson('/api/v1/ai/chat', [
            'message' => 'Show me a daily rental',
            'locale' => 'en',
        ])->assertOk();

        $this->getJson('/api/v1/ai/conversations/'.$chat->json('conversation_id'))
            ->assertOk()
            ->assertJsonPath('messages.1.properties.0.id', $property->id);
    }
}

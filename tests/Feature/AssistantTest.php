<?php

use App\Models\AssistantMessage;
use App\Models\User;

beforeEach(fn () => asAdmin());

it('requires authentication', function () {
    auth()->logout();

    $this->get(route('assistant.history'))->assertRedirect(route('login'));
    $this->post(route('assistant.message'), ['message' => 'hi'])->assertRedirect(route('login'));
});

it('answers a known question from the knowledge base', function () {
    $response = $this->post(route('assistant.message'), ['message' => 'How do I create an invoice?']);

    $response->assertOk();

    expect($response->streamedContent())
        ->toContain('New Invoice')
        ->toContain('draft');
});

it('falls back to a menu for an unknown question', function () {
    $response = $this->post(route('assistant.message'), ['message' => 'what is the meaning of life']);

    expect($response->streamedContent())->toContain('I can help with');
});

it('passes the current page as context without error', function () {
    $this->post(route('assistant.message'), ['message' => 'How does posting work?', 'context' => 'Invoices'])
        ->assertOk();
});

it('persists the user and assistant messages', function () {
    $this->post(route('assistant.message'), ['message' => 'How do I record a payment?'])->streamedContent();

    expect(AssistantMessage::where('role', 'user')->where('content', 'How do I record a payment?')->exists())->toBeTrue()
        ->and(AssistantMessage::where('role', 'assistant')->exists())->toBeTrue();
});

it('returns the conversation history as json', function () {
    $this->post(route('assistant.message'), ['message' => 'What are items?'])->streamedContent();

    $this->getJson(route('assistant.history'))
        ->assertOk()
        ->assertJsonPath('messages.0.role', 'user')
        ->assertJsonPath('messages.0.content', 'What are items?')
        ->assertJsonPath('messages.1.role', 'assistant');
});

it('validates the message', function () {
    $this->post(route('assistant.message'), ['message' => ''])->assertSessionHasErrors('message');
});

it('stops answering after the daily limit', function () {
    config(['assistant.daily_message_limit' => 2]);

    $this->post(route('assistant.message'), ['message' => 'one'])->streamedContent();
    $this->post(route('assistant.message'), ['message' => 'two'])->streamedContent();
    $this->post(route('assistant.message'), ['message' => 'three'])->assertStatus(429);
});

it('keeps each user\'s conversation separate', function () {
    $this->post(route('assistant.message'), ['message' => 'How do I void an invoice?'])->streamedContent();

    $this->actingAs(User::factory()->create());

    $this->getJson(route('assistant.history'))->assertJsonPath('messages', []);
});

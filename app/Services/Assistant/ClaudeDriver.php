<?php

namespace App\Services\Assistant;

use RuntimeException;

/**
 * Real answers from Anthropic's API. Dormant until the SDK is installed and
 * a key is set - `config/assistant.php` explains the switch. Same knowledge
 * base as the canned driver, folded into a cached system prompt.
 */
class ClaudeDriver implements AssistantDriver
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(private array $config) {}

    public function reply(array $history, ?string $context = null): iterable
    {
        if (! class_exists('Anthropic\\Client')) {
            throw new RuntimeException(
                'The "claude" assistant driver needs the SDK: run `composer require anthropic-ai/sdk`.'
            );
        }

        if (empty($this->config['api_key'])) {
            throw new RuntimeException('Set ASSISTANT_API_KEY to use the "claude" assistant driver.');
        }

        $clientClass = 'Anthropic\\Client';
        $client = new $clientClass(apiKey: $this->config['api_key']);

        $stream = $client->messages->createStream(
            model: $this->config['model'],
            maxTokens: $this->config['max_tokens'],
            system: [[
                'type' => 'text',
                'text' => $this->systemPrompt($context),
                'cacheControl' => ['type' => 'ephemeral'],
            ]],
            messages: array_map(
                fn ($m) => ['role' => $m['role'], 'content' => $m['content']],
                $history,
            ),
        );

        foreach ($stream as $event) {
            $delta = $event->delta ?? null;

            if ($delta !== null && ($delta->type ?? null) === 'text_delta') {
                yield $delta->text;
            }
        }
    }

    private function systemPrompt(?string $context): string
    {
        $guide = collect($this->config['topics'])
            ->map(fn ($t) => "## {$t['question']}\n{$t['answer']}")
            ->implode("\n\n");

        $where = $context ? "\n\nThe user is currently on the \"{$context}\" screen." : '';

        return $this->config['intro']."\n\n# How the system works\n\n".$guide.$where;
    }
}

<?php

namespace App\Services\Assistant;

interface AssistantDriver
{
    /**
     * Stream the assistant's reply as a sequence of text chunks.
     *
     * @param  list<array{role: string, content: string}>  $history  oldest first, last item is the new user message
     * @return iterable<string>
     */
    public function reply(array $history, ?string $context = null): iterable;
}

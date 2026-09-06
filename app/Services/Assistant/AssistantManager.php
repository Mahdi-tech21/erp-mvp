<?php

namespace App\Services\Assistant;

use Illuminate\Support\Manager;

class AssistantManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return $this->config->get('assistant.driver', 'canned');
    }

    public function createCannedDriver(): AssistantDriver
    {
        return new CannedDriver($this->config->get('assistant.topics', []));
    }

    public function createClaudeDriver(): AssistantDriver
    {
        return new ClaudeDriver($this->config->get('assistant', []));
    }
}

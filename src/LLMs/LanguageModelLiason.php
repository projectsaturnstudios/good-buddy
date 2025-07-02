<?php

namespace Agents\GoodBuddy\LLMs;

use Agents\GoodBuddy\AgenticComponent;

abstract class LanguageModelLiason extends AgenticComponent
{
    public function call_llm(array $options): array
    {
        return ['success' => false, 'message' => 'Liason call_llm method not implemented'];
    }

    public function get_default_credentials(string $connection): array
    {
        throw new \Exception('get_default_credentials method not implemented in ' . static::class);
    }
}

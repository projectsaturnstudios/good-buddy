<?php

namespace Agents\GoodBuddy\Drivers\RobotBrains;

use Agents\GoodBuddy\ChatHistory\ChatHistory;
use Agents\GoodBuddy\Contracts\LLMCommunicationLayer;

abstract class RobotBrains implements LLMCommunicationLayer
{
    abstract function parseRequest(string $provider, string $model, array $parameters): array;
    abstract public function compileAndSend(
        string $provider, string $model, array $instructions, array $convo, array $tools, ChatHistory $chat
    ): array;
}

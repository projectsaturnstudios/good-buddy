<?php

namespace Agents\GoodBuddy\ChatHistory;

use Spatie\LaravelData\Data;
use Agents\GoodBuddy\Support\Facades\AIBrains;
use Agents\GoodBuddy\Support\Facades\ChatManager;

abstract class ChatHistory extends Data
{
    public array $conversation = [];

    public function save(): static
    {
        return ChatManager::save($this);
    }

    public static function load(string $session_id): ?static
    {
        return ChatManager::load($session_id);
    }

    public function addToConversation(string $role, string $message): static
    {
        $this->conversation[] = [
            'role' => $role,
            'content' => $message
        ];
        return $this->save();
    }

    public function addObjectToConversation(string $role, array $message): static
    {
        $this->conversation[] = [
            'role' => $role,
            'content' => $message
        ];
        return $this->save();
    }

    public function addWholeObjectToConversation(string $role, array $message): static
    {
        $this->conversation[] = array_merge(['role' => $role], $message);
        return $this->save();
    }

    public function toTranscription(?string $provider = null, ?string $model = null): array
    {
        if(empty($provider)) return $this->conversation;
        if(empty($model)) return $this->conversation;
        return AIBrains::formatConvo($provider, $this->conversation);
    }
}

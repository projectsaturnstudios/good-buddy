<?php

namespace Agents\GoodBuddy\MemoryStores;

use Spatie\LaravelData\Data;

class ChatConversation extends Data
{
    public array $conversation = [];

    public function __construct(
        public readonly string $conversation_id
    ) {}

    public function addTextMessage(string $role, string $content): static
    {
        $this->conversation[] = [
            'role' => $role,
            'type' => 'text',
            'content' => $content,
        ];
        return $this;
    }

    public function addToolRequest(array $entry): static
    {
        $this->conversation[] = [
            'type' => 'tool_request',
            'content' => $entry,
        ];
        return $this;
    }

    public function addToolResult(array $entry): static
    {
        $this->conversation[] = [
            'type' => 'tool_result',
            'content' => $entry,
        ];
        return $this;
    }

    public function humanConversation(): array
    {
        $results = array_filter($this->conversation, function ($entry) {
            return $entry['type'] === 'text';
        });

        if(count($results) > 0) {
            $results = array_map(function ($entry) {
                return ['role' => $entry['role'], 'content' => $entry['content']];
            }, $results);
        }

        return $results;
    }
}

<?php

namespace Agents\GoodBuddy;

use LLMSpeak\Schema\LLMResponse;
use Agents\GoodBuddy\ChatHistory\ChatHistory;
use Agents\GoodBuddy\Support\Facades\AIBrains;
use Agents\GoodBuddy\Support\Facades\ChatManager;

abstract class LocallyTooledAgent
{
    protected string $model;
    protected string $provider;

    protected string $memory;

    protected string $chat_session_provider;

    protected array $tools = [];

    /** @var ChatHistory  */
    protected ChatHistory $chat;

    public function __construct(
        protected ?string $chat_session_id = null
    )
    {
        if($this->chat_session_id) $this->createOrLoadConvo();
    }

    public static function whereSessionId(string $session_id): static
    {
        return new static($session_id);
    }

    protected function createOrLoadConvo(): void
    {
        $this->chat = ChatManager::createOrLoad($this->chat_session_id);
    }

    public function start(?string $message = null): ?array
    {
        $this->chat = $this->chat->addToConversation('user', $message);

        $response = AIBrains::driver()->compileAndSend(
            $this->provider, $this->model, $this->instructions(),
            $this->chat->toTranscription($this->provider, $this->model),
            $this->tools, $this->chat
        );

        dd($response);
        return $response;
    }


    public function instructions(): array
    {
        return [];
    }

    public function getConvo(): array
    {
        return $this->chat->toTranscription();
    }
}

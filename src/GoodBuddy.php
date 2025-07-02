<?php

namespace Agents\GoodBuddy;

use Agents\GoodBuddy\Actions\AgentWork\BasicChatBotBehavior;
use Agents\GoodBuddy\StateMachines\Agent\AgentActive;
use Agents\GoodBuddy\StateMachines\Agent\AgentInitialized;
use Agents\GoodBuddy\StateMachines\Agent\AgentNotInitialized;
use Agents\GoodBuddy\StateMachines\Agent\AgentState;
use Agents\GoodBuddy\Support\Facades\AgentComms;
use Agents\GoodBuddy\Support\Facades\MemoryStores;

class GoodBuddy extends LocalAgent
{
    protected ?string $memory_store = null; // cached, database
    protected ?string $session_id = null;
    protected ?string $conversation_id = null;

    protected ?int $max_tokens = 300;
    protected float $temperature = 0.7;

    protected array $tools = [];
    protected array $system_instructions = [];
    protected array $default_system_instructions = [
        //'You are a helpful assistant.',
        //'You are designed to assist users with their queries and tasks.',
        //'You should provide accurate and concise information.',
        //'You can engage in natural language conversations.',
        //'You should always be polite and respectful.',
        //'You can ask for clarification if the user\'s request is not clear.',
        //'You should not provide personal opinions or advice.',
        //'You should always prioritize user privacy and data security.',
    ];

    protected array $chat_conversation = [];
    protected AgentState $state;

    public function __construct()
    {
        $this->state = new AgentNotInitialized();
    }

    public function create(): static
    {
        if(is_null($this->model)) throw new \InvalidArgumentException("Model must be set before calling first() on " . static::class);
        if(is_null($this->connection)) throw new \InvalidArgumentException("Connection must be set before calling first() on " . static::class);
        if(is_null($this->memory_store)) throw new \InvalidArgumentException("Memory store must be set before calling first() on " . static::class);
        if(is_null($this->session_id)) throw new \InvalidArgumentException("Session ID must be set before calling first() on " . static::class);

        $this->conversation_id = MemoryStores::create($this->session_id, $this->memory_store);
        return $this->first();
    }

    public function first() : ?static
    {
        if(is_null($this->model)) throw new \InvalidArgumentException("Model must be set before calling first() on " . static::class);
        if(is_null($this->connection)) throw new \InvalidArgumentException("Connection must be set before calling first() on " . static::class);
        if(is_null($this->memory_store)) throw new \InvalidArgumentException("Memory store must be set before calling first() on " . static::class);
        if(is_null($this->session_id)) throw new \InvalidArgumentException("Session ID must be set before calling first() on " . static::class);

        $memory = MemoryStores::load($this->session_id, $this->memory_store);
        if(count($memory['human_convos']) > 0)
        {
            $this->chat_conversation = last($memory['human_convos']);
            $this->conversation_id = last(array_keys($memory['human_convos']));
            $this->state = ($memory['human_convos'] > 0) ? new AgentActive : new AgentInitialized;
        }
        else return null;//throw new \DomainException("No conversation found for session ID: {$this->session_id} in " . static::class);

        return $this;
    }

    public function get(?int $idx = null): array
    {
        $memory = MemoryStores::load($this->session_id, $this->memory_store);
        if($memory)
        {
            $agents = [];
            foreach($memory['human_convos'] as $conversation_id => $human_convo)
            {
                $this->chat_conversation = $human_convo;
                $this->conversation_id = $conversation_id;
                $agents[] = $this;
            }
            return !is_null($idx) ? $agents[$idx] : $agents;
        }

        return [];
    }

    public function update(array $input): static
    {
        $this->work($input);
        return $this;
    }

    public function firstOrCreate(): static
    {
        $agent = $this->first();
        if(!is_null($agent)) return $agent;
        else return $this->create();
    }

    public function whereSessionId(string $session_id): static
    {
        $this->session_id = $session_id;
        return $this;
    }

    public function setMaxTokens(string $token): static
    {
        $this->max_tokens = $token;
        return $this;
    }

    public function setTemperature(float $temperature): static
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function setMemoryStore(string $memory_store): static
    {
        $this->memory_store = $memory_store;
        return $this;
    }

    public function with(string $key, mixed $value): static
    {
        switch ($key) {
            case 'system_instructions':
                $this->addSystemInstructions($value);
                break;
            case 'tools':
                $this->addTools($value);
                break;
            default:
                throw new \InvalidArgumentException("Property {$key} does not exist on " . static::class);
        }
        if (property_exists($this, $key)) {
            $this->{$key} = $value;
        } else {

        }
        return $this;
    }

    protected function addTool(array $tool): static
    {
        $this->tools[] = $tool;
        return $this;
    }

    protected function addTools(array $tools): static
    {
        foreach ($tools as $tool) {
            $this->addTool($tool);
        }
        return $this;
    }

    protected function addSystemInstruction(string $instruction): static
    {
        $this->system_instructions[] = $instruction;
        return $this;
    }

    protected function addSystemInstructions(array $instructions): static
    {
        foreach ($instructions as $instruction) {
            $this->addSystemInstruction($instruction);
        }
        return $this;
    }

    public function system_instructions(): array
    {
        return array_merge($this->default_system_instructions, $this->system_instructions);
    }

    public function tools(): array
    {
        return $this->tools;
    }

    public function memory_store(): ?string
    {
        return $this->memory_store;
    }

    public function session_id(): ?string
    {
        return $this->session_id;
    }

    public function conversation_id(): ?string
    {
        return $this->conversation_id;
    }

    public function max_tokens(): ?int
    {
        return $this->max_tokens;
    }

    public function temperature(): float
    {
        return $this->temperature;
    }

    /**
     * Should be overridden by the agent to provide any credentials needed for the LLM connection.
     * @return array
     */
    public function credentials(): array
    {
        return AgentComms::get_default_credentials($this->connection());
    }

    protected function work(array $input): void
    {
        $this->agent_response = (new BasicChatBotBehavior($this))->handle($input);
    }
}

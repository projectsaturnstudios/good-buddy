<?php

namespace Agents\GoodBuddy;

abstract class LocalAgent
{
    protected ?string $model = null; // claude-sonnet-4-20250514, gemini-2.5-flash
    protected ?string $connection = null; // anthropic, gemini

    protected array $agent_response = [];

    abstract public function first() : ?static;
    abstract public function create() : static;
    abstract public function firstOrCreate() : static;
    abstract public function get(?int $idx) : array|static;
    abstract public function update(array $input) : static;

    public function onConnection(string $connection): static
    {
        $this->connection = $connection;
        return $this;
    }

    public function whereModel(string $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function model(): ?string
    {
        return $this->model;
    }

    public function connection(): ?string
    {
        return $this->connection;
    }

    public function toArray(): array
    {
        return $this->agent_response;
    }
}

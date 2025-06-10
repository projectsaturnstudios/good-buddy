<?php

namespace Agents\GoodBuddy\Contracts;

interface LLMCommunicationLayer
{
    function parseRequest(string $provider, string $model, array $parameters);
}

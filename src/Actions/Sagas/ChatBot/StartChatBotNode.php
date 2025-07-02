<?php

namespace Agents\GoodBuddy\Actions\Sagas\ChatBot;

use ProjectSaturnStudios\PocketFlow\Node;

class StartChatBotNode extends Node
{
    public function __construct() {
        parent::__construct();
    }

    public function prep(mixed &$shared): mixed
    {
        return $shared;
    }

    public function exec(mixed $prep_res): mixed
    {
        // @todo - dispatch an event that an agent was fired up
        // @todo - see if there are any additional system prompts that need to be dynamically added
        return null;
    }
    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {
        return 'input';
    }
}

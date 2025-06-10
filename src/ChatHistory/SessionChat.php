<?php

namespace Agents\GoodBuddy\ChatHistory;

use Agents\GoodBuddy\Support\Facades\ChatManager;

class SessionChat extends ChatHistory
{
    public function __construct(public string|int $session_id)
    {

    }


}

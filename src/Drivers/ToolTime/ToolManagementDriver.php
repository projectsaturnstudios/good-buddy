<?php

namespace Agents\GoodBuddy\Drivers\ToolTime;

abstract class ToolManagementDriver
{
    abstract public function execute($tool_call): array;
}

<?php

namespace Agents\GoodBuddy\Managers;

use Illuminate\Support\Manager;
use Agents\GoodBuddy\Drivers\ToolTime\ToolManagementDriver;
use Agents\GoodBuddy\Drivers\ToolTime\SuperconductorToolDriver;

class ToolManager extends Manager
{
    public function execute(array $tool_call, ?string $driver = null): array
    {
        $driver = $driver ?? $this->getDefaultDriver();
        return $this->driver($driver)->execute($tool_call);
    }

    public function createSuperconductorDriver(): ToolManagementDriver
    {
        return new SuperconductorToolDriver();
    }

    public function getDefaultDriver(): string
    {
        return config('agents.local-agents.tool-management.default', 'superconductor');
    }

    public static function boot(): void
    {
        app()->singleton('tool-time', function ($app) {
            $results = new static($app);

            foreach(config('agents.local-agents.tool-management.add-ons', []) as $driver => $config) {
                $results = $results->extend($driver, fn() => new $config['driver_class']($config));
            }

            return $results;
        });
    }
}

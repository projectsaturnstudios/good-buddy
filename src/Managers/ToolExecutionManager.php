<?php

namespace Agents\GoodBuddy\Managers;

use Agents\GoodBuddy\Tools\Drivers\SuperconductorToolsDriver;
use Agents\GoodBuddy\Tools\ToolRunner;
use Illuminate\Support\Manager;

class ToolExecutionManager extends Manager
{
    public function fire(array $args, ?string $driver = null): mixed
    {
        $driver = $driver ?? $this->getDefaultDriver();
        return $this->driver($driver)->fire($args);
    }

    public function createSuperconductorDriver(): ToolRunner
    {
        return new SuperconductorToolsDriver();
    }

    public function getDefaultDriver(): string
    {
        return config('agents.local.tools.default', 'superconductor');
    }

    public static function boot(): void
    {
        app()->singleton('good-buddy.tools', function ($app) {
            $results = new static($app);

            $add_ons = config('agents.local.tools.boxes', []);
            foreach($add_ons as $name => $add_on) {
                $results->extend($name, fn() => new $add_on['class']($add_on));
            }

            return $results;
        });
    }
}

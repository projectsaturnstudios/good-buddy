<?php

namespace Agents\GoodBuddy\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class CreateAgentCommand extends GeneratorCommand
{
    protected $signature = 'make:agent {name} {--model=claude-sonnet-4-20250514} {--connection=anthropic} {--memory=cached} {--tokens=500} {--temperature=0.7}';

    protected $description = 'Create a new agent class';

    protected $type = 'Agent';

    protected function getStub()
    {
        return __DIR__ . '/../../../stubs/agent.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Agents';
    }

    public function handle(): int
    {
        // Call the parent handle method to generate the file
        if (parent::handle() === false) {
            return 1;
        }

        $this->info("Agent [{$this->getNameInput()}] created successfully!");
        
        // Show some helpful info
        $this->comment('Your agent has been created with the following configuration:');
        $this->line("Model: {$this->option('model')}");
        $this->line("Connection: {$this->option('connection')}");
        $this->line("Memory Store: {$this->option('memory')}");
        $this->line("Max Tokens: {$this->option('tokens')}");
        $this->line("Temperature: {$this->option('temperature')}");
        
        $this->newLine();
        $this->comment('Next steps:');
        $this->line('1. Configure your agent credentials in the credentials() method');
        $this->line('2. Define your agent behavior in the work() method');
        $this->line('3. Add any tools your agent needs to the $tools array');
        
        return 0;
    }

    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        $stub = str_replace(['DummyClass', '{{ class }}', '{{class}}'], $class, $stub);

        // Replace the agent configuration options
        $stub = str_replace('{{ model }}', $this->option('model'), $stub);
        $stub = str_replace('{{ connection }}', $this->option('connection'), $stub);
        $stub = str_replace('{{ memory_store }}', $this->option('memory'), $stub);
        $stub = str_replace('{{ max_tokens }}', $this->option('tokens'), $stub);
        $stub = str_replace('{{ temperature }}', $this->option('temperature'), $stub);

        return $stub;
    }

    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'The LLM model to use', 'claude-sonnet-4-20250514'],
            ['connection', 'c', InputOption::VALUE_OPTIONAL, 'The LLM connection driver', 'anthropic'],
            ['memory', null, InputOption::VALUE_OPTIONAL, 'The memory store type', 'cached'],
            ['tokens', 't', InputOption::VALUE_OPTIONAL, 'Maximum tokens for responses', '500'],
            ['temperature', null, InputOption::VALUE_OPTIONAL, 'Temperature for model responses', '0.7'],
        ];
     }
}

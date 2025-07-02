<?php

namespace Agents\GoodBuddy\Actions\Sagas\ChatBot;


use Agents\GoodBuddy\GoodBuddy;
use Illuminate\Support\Facades\Event;
use Agents\GoodBuddy\Events\AgentIsBusy;
use ProjectSaturnStudios\PocketFlow\Node;
use Symfony\Component\VarDumper\VarDumper;
use Agents\GoodBuddy\Events\PreToolMessage;
use Agents\GoodBuddy\MemoryStores\ChatConversation;
use Agents\GoodBuddy\Actions\Sagas\Tools\ExecuteToolNode;
use Agents\GoodBuddy\Actions\AgentWork\SaveConversationHistory;

class ProcessLLMOutputNode extends Node
{
    public function __construct() {
        parent::__construct();
    }

    public function prep(mixed &$shared): mixed
    {
        $messages = $shared['llm_response']['response']['messages'];
        $agent = $shared['agent']; /** @var GoodBuddy $agent */
        return [
            'memory_store' => $agent->memory_store(),
            'session_id' => $agent->session_id(),
            'messages' => $messages,
            'chat_history' => $shared['conversation_history'],
            'has_tool_calls' => $shared['llm_response']['tool_calls'],
            'has_text_messages' => $shared['llm_response']['text_messages'],
        ];
    }

    public function exec(mixed $prep_res): mixed
    {
        $session_id = $prep_res['session_id']; /** @var string $session_id */
        $memory_store = $prep_res['memory_store']; /** @var string $memory_store */
        $messages = $prep_res['messages']; /** @var array $messages */
        $chat_history = $prep_res['chat_history']; /** @var ChatConversation $chat_history */

        if($prep_res['has_tool_calls']) {
            if($prep_res['has_text_messages']) {
                foreach($messages as $message) {
                    if($message['type'] == 'text')
                    {
                        VarDumper::dump("Processing text message: {$message['content']}");
                        $chat_history = $chat_history->addTextMessage($message['role'], $message['content']);
                        Event::dispatch(new PreToolMessage($session_id, ['author' => 'assistant', 'type' => 'text', 'data' => ['text' => $message['content']]]));
                    }
                    sleep(1); // @todo - remove this, it's just for testing
                    VarDumper::dump("Turning back on chat bubble");
                    Event::dispatch(new AgentIsBusy($session_id));
                    sleep(1);
                }
            }

            foreach($messages as $message) {
                if($message['type'] == 'tool_call')
                {
                    $chat_history = $chat_history->addToolRequest($message['content']);
                }
            }

            $chat_history = (new SaveConversationHistory($session_id, $chat_history->conversation_id))->handle($chat_history, $memory_store);
            return [
                'messages' => $messages,
                'chat_history' => $chat_history,
            ];
        }
        elseif($prep_res['has_text_messages']) {
            foreach($messages as $message) {
                if($message['type'] == 'text') $chat_history = $chat_history->addTextMessage($message['role'], $message['content']);
                // @todo - tool calls
                // @todo - tool results
            }
            $chat_history = (new SaveConversationHistory($session_id, $chat_history->conversation_id))->handle($chat_history, $memory_store);

            return [
                'messages' => $messages,
                'chat_history' => $chat_history,
            ];
        }

        dd("Dude what the fuck happened?", $prep_res);
        return null;

    }
    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {
        $done = true;
        $next = 'finished';
        if($prep_res['has_tool_calls']) {
            $this->next(new ExecuteToolNode($exec_res['messages']), 'use-tool');

            $next = 'use-tool';
        }
        else
        {
            /*foreach($exec_res['messages'] as $message)
            {
                if($message['type'] == 'tool_result') {
                    $done = false;
                    dd('a tool call flow needs to be executed', $exec_res);
                    break; // If we have a tool result, we need to process it
                }
            }*/

            //if($done)
            if(!array_key_exists('messages', $exec_res)) dd($exec_res, 'punk');
            $shared = $exec_res['messages'];
        }

        return $next;
    }
}

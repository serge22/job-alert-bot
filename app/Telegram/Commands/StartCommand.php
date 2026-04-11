<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;

class StartCommand extends Command
{
    protected string $name = 'start';

    protected string $description = 'Start the bot and get a welcome message';

    public function handle(): void
    {
        $firstName = $this->getUpdate()->getMessage()?->getFrom()?->getFirstName() ?? 'there';
        $appUrl = rtrim(config('app.url'), '/');

        $this->replyWithMessage([
            'text' => "👋 Hi, <b>{$firstName}</b>! Welcome to the Upwork Job Alert Bot.\n\n"
                . "I'll notify you here as soon as new Upwork jobs match your criteria.\n\n"
                . "To get started, open the app and create your first subscription feed. "
                . "Once you have a feed set up, I'll start sending you matching job alerts automatically. 🚀",
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'inline_keyboard' => [[
                    [
                        'text' => '📋 Create your first feed',
                        'web_app' => ['url' => "{$appUrl}/feeds/create"],
                    ],
                ]],
            ]),
        ]);
    }
}


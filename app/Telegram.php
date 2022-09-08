<?php

declare(strict_types=1);

namespace app;

use app\value\File;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\HttpException;
use TelegramBot\Api\Types\Message;

class Telegram
{
    private BotApi $bot;

    private bool $debug = false;

    public function __construct(string $token, private readonly string $chatId)
    {
        $this->bot = new BotApi($token);
    }

    public function getDebug(): bool
    {
        return $this->debug;
    }

    public function setDebug($debug = true): void
    {
        if ($this->debug !== $debug) {
            $this->bot->setCurlOption(CURLOPT_VERBOSE, $debug);
        }
        $this->debug = $debug;
    }

    public function sendMessage(string $message): Message
    {
        $result = null;
        do {
            try {
                $result = $this->bot->sendMessage(
                    $this->chatId,
                    $message
                );
            } catch (HttpException $e) {
                syslog(LOG_ERR, $e->getMessage());
                if ($seconds = ($e->getParameters()['retry_after'] ?? null)) {
                    syslog(LOG_INFO, "Sleep {$seconds} sec");
                    sleep($seconds);
                }
            }
        } while (!$result);
        return $result;
    }
}

<?php

declare(strict_types=1);

namespace app;

use TelegramBot\Api\{BotApi, HttpException, Types\Message};

class Telegram
{
    private BotApi $bot;
    private bool $debug = false;
    private bool $emulation = false;

    public function __construct(string $token, private readonly string $chatId)
    {
        $this->bot = new BotApi($token);
    }

    public function setDebug($debug = true): void
    {
        if ($this->debug !== $debug) {
            $this->bot->setCurlOption(CURLOPT_VERBOSE, $debug);
        }
        $this->debug = $debug;
    }

    public function setEmulation(bool $emulation = true): void
    {
        $this->emulation = $emulation;
    }

    public function sendMessage(string $message): ?Message
    {
        if ($this->emulation) {
            return null;
        }

        $result = null;
        do {
            try {
                $result = $this->bot->sendMessage(
                    $this->chatId,
                    $message
                );
            } catch (HttpException $e) {
                // Если слишком много обращений к API Telegram, ждем некоторое количество секунд
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

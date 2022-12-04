<?php

namespace app;

class Og21Parser implements Parser
{
    private const URL = 'https://api.og21.ru/site/poll/select';

    public function parse(): array
    {
        $content = $this->getContent();
        if (!$content) {
            return [];
        }
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return array_map(static function (array $poll) {
            return [
                'title' => $poll['title'],
                'url' => sprintf('https://og21.ru/poll/%u', $poll['id']),
                'description' => $poll['text_short_html'],
                'pubDate' => $poll['begin_date'],
            ];
        }, $data['result']['polls'] ?? []);
    }

    public function getContent(): ?string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::URL);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Sec-Ch-Ua: "Google Chrome";v="105", "Not)A;Brand";v="8", "Chromium";v="105"',
            'Dnt: 1',
            'Sec-Ch-Ua-Mobile: ?0',
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.0.0 Safari/537.36',
            'Content-Type: application/json',
            'Accept: application/json, text/plain, */*',
            'Referer: https://og21.ru/',
            'Sec-Ch-Ua-Platform: "macOS"',
            'Accept-Encoding: gzip',
        ]);

        $json_array = [
            'filters' => [
                'available'
            ],
            'page_number' => 1,
            'count_per_page' => 6,
        ];
        $body = json_encode($json_array, JSON_THROW_ON_ERROR);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_close($ch);

        return curl_exec($ch) ?: null;
    }
}

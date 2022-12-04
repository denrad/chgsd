<?php

declare(strict_types=1);

use app\{Og21Parser, XmlWriter};

require_once 'vendor/autoload.php';

function handler($event, $content): array
{
    $writer = new XmlWriter(new Og21Parser());
    return [
        'statusCode' => 200,
        'body' => $writer->getFeed(),
        'headers' => [
            'Content-Type' => 'application/rss+xml',
        ],
    ];
}



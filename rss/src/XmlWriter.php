<?php

namespace app;

use Laminas\Feed\Writer\{Entry, Feed};

class XmlWriter
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function getFeed(): string
    {
        $records = $this->parser->parse();
        $feed = new Feed();

        try {
            $dateModified = max(array_column($records, 'pubDate'));
            $feed->setDateModified($dateModified);
        } catch (\Throwable $e) {
            echo "Error: Date not found";
        }

        $feed->setTitle('Открытый город')
            ->setEncoding('utf-8')
            ->setLink('https://og21.ru/')
            ->setDescription('Голосования на портале "Открытый город"')
            ->setLanguage('ru-RU');

        foreach ($records as $record) {
            $entry = new Entry();
            $entry->setTitle($record['title'])
                ->setLink($record['url'])
                ->setDateModified($record['pubDate'])
                ->setDescription($record['title'])
                ->setContent($record['description']);

            $feed->addEntry($entry);
        }

        return $feed->export('rss');
    }

}

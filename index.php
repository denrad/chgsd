<?php

declare(strict_types=1);

use app\{Db, Telegram, value\Article};
use voku\helper\HtmlDomParser;

require_once 'vendor/autoload.php';

function getPage(string $url): string
{
    syslog(LOG_INFO, "Open URL {$url}");
    return file_get_contents($url);
}

/**
 * @return Article[]
 */
function getArticles(string $url): array
{
    $dom = HtmlDomParser::str_get_html(getPage($url));
    $articles = [];

    ['scheme' => $scheme, 'host' => $host] = parse_url($url);
    $baseUrl = "$scheme://$host";

    foreach ($dom->find('.child_link a') as $link) {
        [$date, $text] = explode(' ', $link->text, 2);
        $url = $baseUrl . $link->getAttribute('href');

        $text = preg_replace('/\s{2,}/', '', html_entity_decode($text));
        $articles[] = new Article($url, $date, $text);
    }

    syslog(LOG_INFO, sprintf('Get %u links', count($articles)));
    return $articles;
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required(['PAGE_URL', 'TELEGRAM_TOKEN', 'TELEGRAM_CHAT_ID', 'SYSLOG_PREFIX'])->notEmpty();
$dotenv->required(['DEBUG'])->isBoolean();

openlog($_ENV['SYSLOG_PREFIX'] ?? 'chgsd', LOG_PID | LOG_PERROR, LOG_USER);
register_shutdown_function(static function() {
    closelog();
});

$db = new Db(__DIR__ . '/runtime/articles.ser');
$articles = getArticles($_ENV['PAGE_URL']);

$newArticles = array_udiff($articles, $db->toArray(), static function (Article $a, Article $b) {
    return $b->date <=> $a->date;
});

syslog(LOG_INFO, sprintf('Get %u new links', count($newArticles)));

if ($newArticles) {
    $telegram = new Telegram($_ENV['TELEGRAM_TOKEN'], $_ENV['TELEGRAM_CHAT_ID']);
    $telegram->setDebug($_ENV['DEBUG']);

    foreach ($newArticles as $article) {
        syslog(LOG_INFO, "Send to Telegram '{$article->getPrettyString()}'");
        $telegram->sendMessage((string)$article);
        $db->append($article);
    }
}

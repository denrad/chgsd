<?php

declare(strict_types=1);

use app\{Attachment, Db, Telegram, value\Article};
use voku\helper\HtmlDomParser;

require_once 'vendor/autoload.php';

function getPage(): string
{
    $url = $_ENV['PAGE_URL'];
    syslog(LOG_INFO, "Open URL {$url}");
    return file_get_contents($url);
}

/**
 * @return Article[]
 */
function getArticles(): array
{
    $dom = HtmlDomParser::str_get_html(getPage());
    $articles = [];
    foreach ($dom->find('.child_link a') as $link) {
        [$date, $text] = explode(' ', $link->text, 2);
        $url = 'https://chgsd.cap.ru' . $link->getAttribute('href');

        $articles[] = new Article(
            $url,
            $date,
            $text
        );
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
$articles = getArticles();
$documentService = new Attachment(new HtmlDomParser());

$newArticles = array_udiff($articles, $db->toArray(), static function (Article $a, Article $b) {
    return $b->date <=> $a->date;
});

syslog(LOG_INFO, sprintf('Get %u new links', count($newArticles)));

if (!$newArticles) {
    exit(0);
}

$telegram = new Telegram($_ENV['TELEGRAM_TOKEN'], $_ENV['TELEGRAM_CHAT_ID']);
$telegram->setDebug($_ENV['DEBUG']);

foreach ($newArticles as $article) {
    syslog(LOG_INFO, "Send to Telegram '{$article->getPrettyString()}'");
    $telegram->sendMessage((string)$article);
    $db->append($article);
}

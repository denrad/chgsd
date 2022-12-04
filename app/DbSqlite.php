<?php

namespace app;

use app\value\Article;
use app\value\Basic;

class DbSqlite implements DbInterface
{
    private \PDO $pdo;
    public function __construct(string $dbname)
    {
        $this->pdo = new \PDO("sqlite:runtime/{$dbname}.sqlite");
        $this->createTable();
    }

    public function append($item): void
    {
        if (!($item instanceof Basic)) {
            throw new \InvalidArgumentException('Item must be instance of Basic');
        }
        $stm = $this->pdo->prepare("INSERT INTO articles (url, date, text) VALUES (:url, :date, :text)");
        $stm->execute($item->jsonSerialize());
    }

    public function toArray(): array
    {
        $stm = $this->pdo->prepare("SELECT * FROM articles");
        $stm->execute();

        return array_map(
            fn(array $row) => new Article($row['url'], $row['date'], $row['text']),
            $stm->fetchAll(\PDO::FETCH_ASSOC)
        );
    }

    private function createTable(): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS articles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    url varchar NOT NULL UNIQUE ON CONFLICT REPLACE,
    date varchar NOT NULL,
    text varchar NOT NULL
);
SQL;
        $this->pdo->exec($sql);
    }

    private function qq(array $data): string
    {
        return implode(', ', array_map(fn($item) => "'{$item}'", $data));
    }
}

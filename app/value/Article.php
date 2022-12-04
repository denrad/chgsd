<?php

namespace app\value;

class Article extends Basic implements \Stringable
{
    public function __construct(
        public readonly string $url,
        public readonly string $date,
        public readonly string $text
    ) {
    }

    public function getPrettyString(): string
    {
        return sprintf(
            "%s %s \n %s",
            \DateTime::createFromFormat('Y.m.d', $this->date)->format('d.m.Y'),
            $this->text,
            $this->url
        );
    }

    public function __toString(): string
    {
        return $this->getPrettyString();
    }
}

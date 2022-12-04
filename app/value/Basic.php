<?php

namespace app\value;

abstract class Basic implements \JsonSerializable
{
    public static function createFromArray(array $data): static
    {
        return new static(...$data);
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}

<?php

namespace App\Classes;

use App\Data\ParamsData;

class Signature
{
    const HEX = false;

    protected ParamsData $data;

    public function __construct(ParamsData $data)
    {
        $this->data = $data;
    }

    public static function create(ParamsData $data): static
    {
        return new static($data);
    }

    public function toString(): string
    {
        $sortedParams = http_build_query($this->data->toArray());

        return strtoupper(hash('sha256', $sortedParams, self::HEX));
    }

    public function toArray($key = 'signature'): array
    {
        $signature = $this->toString();

        return [ $key => $signature ];
    }
}

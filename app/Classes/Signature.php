<?php

namespace App\Classes;

use App\Data\ParamsData;

class Signature
{
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
        //process $this->data

        return '537';
    }

    public function toArray($associative = true): array
    {
        $signature = $this->toString();

        return $associative ? compact('signature') : [ $signature ];
    }
}

<?php

namespace App\Interfaces;

use App\Data\{ParamsData, ResponseData};
use Brick\Money\Money;

interface PaymentGateway
{
    function setReferenceCode(string $reference_code): self;
    function setAmount(Money $amount): self;
    function getClientConfig(): array;
    function getServerConfig(): mixed;
    function getAttribs(): array;
    function getParamsData(): ParamsData;
    function send(): ResponseData;
}

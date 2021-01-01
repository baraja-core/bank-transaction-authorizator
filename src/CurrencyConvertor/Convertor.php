<?php

declare(strict_types=1);

namespace Baraja\BankTransferAuthorizator\CurrencyConvertor;


interface Convertor
{
	public function convert(string $currentCurrency, string $expectedCurrency, float $price): float;
}

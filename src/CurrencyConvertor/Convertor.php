<?php

declare(strict_types=1);

namespace Baraja\BankTransferAuthorizator\CurrencyConvertor;


interface Convertor
{
	/**
	 * @param numeric-string $price
	 * @return numeric-string
	 */
	public function convert(string $price, string $source, string $target): string;
}

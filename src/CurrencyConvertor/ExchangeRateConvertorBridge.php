<?php

declare(strict_types=1);

namespace Baraja\BankTransferAuthorizator\CurrencyConvertor;


use Baraja\Shop\Currency\ExchangeRateConvertor;

final class ExchangeRateConvertorBridge implements Convertor
{
	public function __construct(
		private ExchangeRateConvertor $exchangeRateConvertor,
	) {
	}


	/**
	 * @param numeric-string $price
	 * @return numeric-string
	 */
	public function convert(string $price, string $source, string $target): string
	{
		return $this->exchangeRateConvertor->convert(
			price: $price,
			source: $source,
			target: $target,
		);
	}
}

<?php

declare(strict_types=1);

namespace Baraja\BankTransferAuthorizator\CurrencyConvertor;


use Baraja\CurrencyExchangeRate\CurrencyExchangeRateManager;

final class BarajaDefaultCurrencyConvertor implements Convertor
{
	private CurrencyExchangeRateManager $manager;


	public function __construct()
	{
		$this->manager = new CurrencyExchangeRateManager;
	}


	public function convert(string $currentCurrency, string $expectedCurrency, float $price): float
	{
		$baseRate = $currentCurrency === 'CZK' ? 1 : $this->manager->getRate($currentCurrency);
		$rate = $expectedCurrency === 'CZK' ? 1 : $this->manager->getRate($expectedCurrency);

		return $price * $baseRate / $rate;
	}
}

<?php

declare(strict_types=1);

namespace Baraja\BankTransferAuthorizator;


final class Helpers
{

	/** @throws \Error */
	public function __construct()
	{
		throw new \Error('Class ' . get_class($this) . ' is static and cannot be instantiated.');
	}


	public static function convertCurrency(string $currentCurrency, string $expectedCurrency, float $price): float
	{
		// TODO: Reserved for future use.

		return $price;
	}
}

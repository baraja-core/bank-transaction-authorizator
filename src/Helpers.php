<?php

declare(strict_types=1);

namespace Baraja\BankTransferAuthorizator;


final class Helpers
{
	/** @throws \Error */
	public function __construct()
	{
		throw new \Error('Class ' . static::class . ' is static and cannot be instantiated.');
	}
}

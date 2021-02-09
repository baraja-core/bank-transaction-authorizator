<?php

declare(strict_types=1);

namespace Baraja\BankTransferAuthorizator;


interface Transaction
{
	public function getCurrency(): string;

	public function getPrice(): float;

	public function isVariableSymbol(int $vs): bool;

	public function isContainVariableSymbolInMessage(int $vs): bool;
}

<?php

declare(strict_types=1);

namespace Baraja\BankTransferAuthorizator;


interface Transaction
{
	public function getCurrency(): string;

	public function getPrice(): float;

	public function isVariableSymbol(int $variable): bool;

	public function isContainVariableSymbolInMessage(int $variableSymbol): bool;
}

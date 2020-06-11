<?php

declare(strict_types=1);

namespace Baraja\BankTransferAuthorizator;


interface Transaction
{

	/**
	 * @return string
	 */
	public function getCurrency(): string;

	/**
	 * @return float
	 */
	public function getPrice(): float;

	/**
	 * @param int $variable
	 * @return bool
	 */
	public function isVariableSymbol(int $variable): bool;

	/**
	 * @param int $variableSymbol
	 * @return bool
	 */
	public function isContainVariableSymbolInMessage(int $variableSymbol): bool;
}
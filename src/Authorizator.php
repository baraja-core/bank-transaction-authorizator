<?php

declare(strict_types=1);

namespace Baraja\BankTransferAuthorizator;


interface Authorizator
{
	/**
	 * Check list of unauthorized variable symbols, compare with read bank account list and authorize paid records.
	 * For valid transaction user record must match price exactly or in given tolerance (default is +/- 1 CZK).
	 *
	 * Example:
	 *    [19010017 => 250]
	 *    Variable: 19010017
	 *    Price: 250 CZK, accept <249, 251>
	 *
	 * @param array<string|int, int|float> $unauthorizedVariables (variable => expectedPrice)
	 * @param callable&(callable(Transaction): void)[] $callback
	 */
	public function authOrders(
		array $unauthorizedVariables,
		callable $callback,
		?string $currency = null,
		float $tolerance = 1.0,
	): void;

	/**
	 * @return Transaction[]
	 */
	public function getTransactions(): array;

	/**
	 * @param int[] $validVariables
	 * @return Transaction[]
	 */
	public function getUnmatchedTransactions(array $validVariables): array;

	public function getDefaultCurrency(): string;
}

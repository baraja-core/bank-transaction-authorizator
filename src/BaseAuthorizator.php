<?php

declare(strict_types=1);

namespace Baraja\BankTransferAuthorizator;


abstract class BaseAuthorizator implements Authorizator
{

	/**
	 * @param int[]|float[] $unauthorizedVariables -> key is variable, value is expected price.
	 * @param callable $callback with first argument of type Transaction.
	 * @param string $currency
	 * @param float $tolerance
	 */
	public function authOrders(array $unauthorizedVariables, callable $callback, string $currency = 'CZK', float $tolerance = 1.0): void
	{
		$variables = array_keys($unauthorizedVariables);

		$process = static function (float $price, Transaction $transaction) use ($callback, $currency, $tolerance): void {
			if ($transaction->getCurrency() !== $currency) { // Fix different currencies
				$price = Helpers::convertCurrency($transaction->getCurrency(), $currency, $price);
			}
			if ($transaction->getPrice() - $price >= -$tolerance) { // Is price in tolerance?
				$callback($transaction);
			}
		};

		foreach ($this->getTransactions() as $transaction) {
			foreach ($variables as $currentVariable) {
				if ($transaction->isVariableSymbol((int) $currentVariable) === true) {
					$process((float) $unauthorizedVariables[(int) $currentVariable], $transaction);
					break;
				}
			}
		}
	}


	/**
	 * @param int[] $validVariables
	 * @return Transaction[]
	 */
	public function getUnmatchedTransactions(array $validVariables): array
	{
		$return = [];
		foreach ($this->getTransactions() as $transaction) {
			$matched = false;
			foreach ($validVariables as $currentVariable) {
				if ($transaction->isVariableSymbol($currentVariable) === true) {
					$matched = true;
					break;
				}
			}
			if ($matched === false) {
				$return[] = $transaction;
			}
		}

		return $return;
	}
}

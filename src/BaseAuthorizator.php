<?php

declare(strict_types=1);

namespace Baraja\BankTransferAuthorizator;


abstract class BaseAuthorizator implements Authorizator
{

	/**
	 * @param int[]|float[] $unauthorizedVariables (variable => expectedPrice)
	 * @param callable&(callable(Transaction): void)[] $callback
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
		if ((!$validVariables || array_keys($validVariables) === range(0, count($validVariables) - 1)) === false) {
			throw new \InvalidArgumentException(
				'The variables array must be associative.' . "\n"
				. 'To solve this issue: Remove other values that are not valid variable symbols. You can do this, '
				. 'for example, with the array_keys() function, or by modifying your algorithm to get a list of orders.'
			);
		}
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

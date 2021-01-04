<?php

declare(strict_types=1);

namespace Baraja\BankTransferAuthorizator;


final class MultiAuthorizator extends BaseAuthorizator
{
	/** @var Authorizator[] */
	private array $authorizators = [];


	public function addAuthorizator(Authorizator $authorizator): void
	{
		$this->authorizators[] = $authorizator;
	}


	/**
	 * @param int[]|float[] $unauthorizedVariables (variable => expectedPrice)
	 * @param callable $callback (\Baraja\BankTransferAuthorizator\Transaction $transaction).
	 */
	public function authOrders(array $unauthorizedVariables, callable $callback, string $currency = 'CZK', float $tolerance = 1.0): void
	{
		foreach ($this->authorizators as $authorizator) {
			$authorizator->authOrders($unauthorizedVariables, $callback, $currency, $tolerance);
		}
	}


	/**
	 * @return Transaction[]
	 */
	public function getTransactions(): array
	{
		$return = [];
		foreach ($this->authorizators as $authorizator) {
			$return[] = $authorizator->getTransactions();
		}

		return array_merge([], ... $return);
	}


	/**
	 * @param int[] $validVariables
	 * @return Transaction[]
	 */
	public function getUnmatchedTransactions(array $validVariables): array
	{
		$return = [];
		foreach ($this->authorizators as $authorizator) {
			$return[] = $authorizator->getUnmatchedTransactions($validVariables);
		}

		return array_merge([], ... $return);
	}
}

<?php

declare(strict_types=1);

namespace Baraja\BankTransferAuthorizator;


final class MultiAuthorizator extends BaseAuthorizator
{
	/** @var Authorizator[] */
	private array $services;


	/**
	 * @param Authorizator[] $services
	 */
	public function __construct(array $services = [])
	{
		$this->services = $services;
	}


	public function addAuthorizator(Authorizator $service): void
	{
		$this->services[] = $service;
	}


	/**
	 * @param int[]|float[] $unauthorizedVariables (variable => expectedPrice)
	 * @param callable $callback (\Baraja\BankTransferAuthorizator\Transaction $transaction).
	 */
	public function authOrders(array $unauthorizedVariables, callable $callback, ?string $currency = null, float $tolerance = 1.0): void
	{
		foreach ($this->services as $service) {
			$service->authOrders($unauthorizedVariables, $callback, $currency, $tolerance);
		}
	}


	/**
	 * @return Transaction[]
	 */
	public function getTransactions(): array
	{
		$return = [];
		foreach ($this->services as $service) {
			$return[] = $service->getTransactions();
		}

		return array_merge([], ...$return);
	}


	/**
	 * @param int[] $validVariables
	 * @return Transaction[]
	 */
	public function getUnmatchedTransactions(array $validVariables): array
	{
		$return = [];
		foreach ($this->services as $service) {
			$return[] = $service->getUnmatchedTransactions($validVariables);
		}

		return array_merge([], ...$return);
	}
}

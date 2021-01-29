<?php

declare(strict_types=1);

namespace Baraja\BankTransferAuthorizator;


use Baraja\BankTransferAuthorizator\CurrencyConvertor\BarajaDefaultCurrencyConvertor;
use Baraja\BankTransferAuthorizator\CurrencyConvertor\Convertor;
use Baraja\CurrencyExchangeRate\CurrencyExchangeRateManager;

abstract class BaseAuthorizator implements Authorizator
{
	private ?Convertor $currencyConvertor = null;


	/**
	 * @param int[]|float[] $unauthorizedVariables (variable => expectedPrice)
	 * @param callable&(callable(Transaction): void)[] $callback
	 */
	public function authOrders(
		array $unauthorizedVariables,
		callable $callback,
		?string $currency = null,
		float $tolerance = 1.0
	): void {
		$variables = array_keys($unauthorizedVariables);
		if (!preg_match('/^[A-Z]{3}$/', $currency = strtoupper($currency ?? $this->getDefaultCurrency()))) {
			throw new \InvalidArgumentException('Requested currency "' . $currency . '" is not valid.');
		}

		$process = function (float $price, Transaction $transaction) use ($callback, $currency, $tolerance): void {
			if ($transaction->getCurrency() !== $currency) { // Fix different currencies
				$price = $this->getCurrencyConvertor()->convert($transaction->getCurrency(), $currency, $price);
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
				. 'for example, with the array_keys() function, or by modifying your algorithm to get a list of orders.',
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


	public function getCurrencyConvertor(): Convertor
	{
		if ($this->currencyConvertor === null) {
			if (\class_exists(CurrencyExchangeRateManager::class) === true) {
				$this->currencyConvertor = new BarajaDefaultCurrencyConvertor;
			} else {
				throw new \LogicException(
					'Currency convertor is not available.' . "\n"
					. 'To solve this issue: Please implement your own class implementing "' . Convertor::class . '" '
					. 'interface and register it to "' . static::class . '" '
					. 'or install Composer package "baraja-core/currency-exchange-rate".',
				);
			}
		}

		return $this->currencyConvertor;
	}


	public function setCurrencyConvertor(Convertor $convertor): void
	{
		$this->currencyConvertor = $convertor;
	}


	public function getDefaultCurrency(): string
	{
		return 'CZK';
	}
}

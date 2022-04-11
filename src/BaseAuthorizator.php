<?php

declare(strict_types=1);

namespace Baraja\BankTransferAuthorizator;


use Baraja\BankTransferAuthorizator\CurrencyConvertor\Convertor;
use Baraja\BankTransferAuthorizator\CurrencyConvertor\ExchangeRateConvertorBridge;
use Baraja\Shop\Currency\ExchangeRateConvertor;

abstract class BaseAuthorizator implements Authorizator
{
	protected ?ExchangeRateConvertor $exchangeRateConvertor = null;
	private ?Convertor $currencyConvertor = null;


	/**
	 * @param array<string|int, int|float> $unauthorizedVariables (variable => expectedPrice)
	 * @param callable&(callable(Transaction): void)[] $callback
	 */
	public function authOrders(
		array $unauthorizedVariables,
		callable $callback,
		?string $currency = null,
		float $tolerance = 1.0,
	): void {
		$variables = array_keys($unauthorizedVariables);
		$currency = strtoupper($currency ?? $this->getDefaultCurrency());
		if (preg_match('/^[A-Z]{3}$/', $currency) !== 1) {
			throw new \InvalidArgumentException('Requested currency "' . $currency . '" is not valid.');
		}

		$process = function (float $price, Transaction $transaction) use ($callback, $currency, $tolerance): void {
			if ($transaction->getCurrency() !== $currency) { // Fix different currencies
				$price = (float) $this->getCurrencyConvertor()->convert((string) $price, $transaction->getCurrency(), $currency);
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
		if ($validVariables === []) {
			return [];
		}
		if (array_keys($validVariables) !== range(0, count($validVariables) - 1)) {
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
			if ($this->exchangeRateConvertor !== null) {
				$this->currencyConvertor = new ExchangeRateConvertorBridge($this->exchangeRateConvertor);
			} else {
				throw new \LogicException(
					'Currency convertor is not available.' . "\n"
					. 'To solve this issue: Please implement your own class implementing "' . Convertor::class . '" '
					. 'interface and register it to "' . static::class . '" '
					. 'or install Composer package "baraja-core/currency".',
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


	public function setExchangeRateConvertor(ExchangeRateConvertor $exchangeRateConvertor): void
	{
		$this->exchangeRateConvertor = $exchangeRateConvertor;
	}
}

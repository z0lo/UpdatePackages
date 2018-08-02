<?php

namespace Milon\Barcode;

use Exception;

class WrongCheckDigitException extends \LogicException
{
	/**
	 * WrongCheckDigitException constructor.
	 *
	 * @param int|null       $actual
	 * @param int|null       $expected
	 * @param Exception      $code
	 * @param Exception|null $previous
	 */
	public function __construct($actual = null, $expected = null, $code, \Exception $previous = null)
	{
		$message = null;
		if ($actual && $expected) {
			$message = 'Expected ' . $expected . ' get ' . $actual;
		}

		parent::__construct($message, $code, $previous);
	}
}
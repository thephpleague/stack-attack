<?php

namespace Ugli\StackAttack\Filters;

use Symfony\Component\HttpFoundation\Request;

abstract class Check
{
	/**
	 * @var  string
	 */
	protected $message;

	/**
	 * @var \Closure
	 */
	protected $rule;

	/**
	 * @var string
	 */
	protected $type;

	public function __construct($message, \Closure $rule)
	{
		$this->message = $message;
		$this->rule = $rule;
	}

	public function test(Request $request)
	{
		if (call_user_func($this->rule, $request)) {
			$request->attributes->set('stackattack.match.message', $this->getMessage());
			$request->attributes->set('stackattack.match.type', $this->getType());

			return true;
		}

		return false;
	}

	public function getMessage()
	{
		return $this->message;
	}

	public function getType()
	{
		return $this->type;
	}
}
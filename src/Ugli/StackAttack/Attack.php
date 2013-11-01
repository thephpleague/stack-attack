<?php

namespace Ugli\StackAttack;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Attack implements HttpKernelInterface
{
	/**
	 * @var \Symfony\Component\HttpKernel\HttpKernelInterface
	 */
	private $app;

	/**
	 * @var string
	 */
	private $blacklistMessage = 'Unauthorized';

	/**
	 * @var FilterCollection
	 */
	private $filters;

	public function __construct(HttpKernelInterface $app, FilterCollection $filters)
	{
		$this->app = $app;
		$this->filters = $filters;
	}

	public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
	{
		if ($this->whitelisted($request)) {
			return $this->app->handle($request, $type, $catch);
		} elseif ($this->blacklisted($request)) {
			return new Response($this->blacklistMessage, 401);
		}

		return $this->app->handle($request, $type, $catch);
	}

	private function whitelisted(Request $request)
	{
		$whitelist = $this->filters->getWhitelist();
		if (!empty($whitelist)) {
			foreach ($whitelist as $rule) {
				// If the rule passes, then this is a whitelisted request.
				if ($rule->test($request) === true) {
					return true;
				}
			}
		}

		return false;
	}

	private function blacklisted(Request $request)
	{
		$blacklist = $this->filters->getBlacklist();
		if (!empty($blacklist)) {
			foreach ($blacklist as $rule) {
				// If the rule passes, then this is a blacklisted request.
				if ($rule->test($request)) {
					$this->blacklistMessage = $request->attributes->get('stackattack.match.message');
					return true;
				}
			}
		}

		return false;
	}
}

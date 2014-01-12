<?php namespace League\StackAttack;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use League\StackAttack\Throttle;
use League\StackAttack\FilterCollection;

class Attack implements HttpKernelInterface
{
    /**
     * @var \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    protected $app;

    /**
     * @var FilterCollection
     */
    protected $filters;

    /**
     * @var Throttle
     */
    protected $throttle;

    /**
     * Class Constructor
     *
     * @param HttpKernelInterface $app      The App instance
     * @param FilterCollection    $filters  The filter object we are using
     * @param Throttle            $throttle The throttle object we are using
     */
    public function __construct(HttpKernelInterface $app, FilterCollection $filters = null, Throttle $throttle = null) {
        $this->app = $app;
        $this->filters = $filters;
        $this->throttle = $throttle;
    }

    /**
     * Handle method
     *
     * @param  Request  $request Request object
     * @param  int      $type    Request type
     * @param  boolean  $catch   Catch exceptions?
     * @return Response         Returns response
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        // if we have filters...
        if ($this->filters) {
            // is this a whitelisted request?
            if ($this->filters->checkWhitelist($request)) {
                return $this->app->handle($request, $type, $catch);

            // is this a blacklisted request?
            } elseif ($this->filters->checkBlacklist($request)) {
                return call_user_func($this->filter->blacklistResponse, $request);
            }
        }

        // if we have a throttle...
        if ($this->throttle && $this->throttle->checkThrottle($request)) {
            return call_user_func($this->throttle->throttleResponse(), $request);
        }

        return $this->app->handle($request, $type, $catch);
    }

}
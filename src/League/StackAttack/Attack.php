<?php namespace League\StackAttack;

use Symfony\Component\HttpFoundation\Request;
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
     * Class Constructor
     *
     * @param HttpKernelInterface $app      The App instance
     * @param FilterCollection    $filters  The filter object we are using
     */
    public function __construct(HttpKernelInterface $app, FilterCollection $filters) {
        $this->app = $app;
        $this->filters = $filters;
        $this->filters->setRequest($this->app);
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
        // check the filters - "true" means you can pass, "false" means go away
        if (! $this->filters->checkFilters($request)) {
            return $this->filters->response;
        }

        return $this->app->handle($request, $type, $catch);
    }
}
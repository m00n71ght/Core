<?php namespace Modules\Core\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

abstract class RoutingServiceProvider extends ServiceProvider
{
    /**
     * The root namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router $router
     * @return void
     */
    public function boot(Router $router)
    {
        parent::boot($router);
    }

    /**
     * @return string
     */
    abstract protected function getFrontendRoute();

    /**
     * @return string
     */
    abstract protected function getBackendRoute();

    /**
     * @return string
     */
    abstract protected function getApiRoute();

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router $router
     * @return void
     */
    public function map(Router $router)
    {
        $router->group(['namespace' => $this->namespace, 'prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localizationRedirect'] ], function (Router $router) {
            $frontend = $this->getFrontendRoute();

            if ($frontend && file_exists($frontend)) {
                require $frontend;
            }

            $backend = $this->getBackendRoute();

            if ($backend && file_exists($backend)) {
                $router->group(['namespace' => 'Admin', 'prefix' => config('asgard.core.core.admin-prefix'), 'middleware' => 'auth.admin'], function (Router $router) use ($backend) {
                    require $backend;
                });
            }

            $api = $this->getApiRoute();

            if ($api && file_exists($api)) {
                $router->group(['namespace' => 'Api'], function (Router $router) use ($api) {
                    require $api;
                });
            }
        });
    }
}

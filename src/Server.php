<?php

namespace PerfexApiSdk;

use CI_Controller;

class Server
{
    protected $ci;

    public function __construct()
    {
        // Make CI instance accessible
        $this->ci =& get_instance();
    }

    /**
     * Register all API routes (to be called from routes.php or hooks.php)
     */
    public function registerRoutes()
    {
        $CI =& get_instance();
        $routes = $CI->router;

        // Register example routes
        require_once __DIR__ . '/config/routes.php';
    }
}

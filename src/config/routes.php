<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['api/delete/(:any)/(:num)']          = 'PerfexApi/Controllers/$1/data/$2';
$route['api/(:any)/search/(:any)']          = 'PerfexApi/Controllers/$1/data_search/$2';
$route['api/(:any)/search']                 = 'PerfexApi/Controllers/$1/data_search';
$route['api/login/auth']                    = 'PerfexApi/Controllers/login/login_api';
$route['api/login/view']                    = 'PerfexApi/Controllers/login/view';
$route['api/login/key']                     = 'PerfexApi/Controllers/login/api_key';
$route['api/(:any)/(:any)/(:num)']          = 'PerfexApi/Controllers/$1/data/$2/$3';
$route['api/(:any)/(:num)/(:num)']          = 'PerfexApi/Controllers/$1/data/$2/$3';
$route['api/custom_fields/(:any)/(:num)']   = 'PerfexApi/Controllers/custom_fields/data/$1/$2';
$route['api/custom_fields/(:any)']          = 'PerfexApi/Controllers/custom_fields/data/$1';
$route['api/common/(:any)/(:num)']          = 'PerfexApi/Controllers/common/data/$1/$2';
$route['api/common/(:any)']                 = 'PerfexApi/Controllers/common/data/$1';
$route['api/(:any)/(:num)']                 = 'PerfexApi/Controllers/$1/data/$2';
$route['api/(:any)']                        = 'PerfexApi/Controllers/$1/data';
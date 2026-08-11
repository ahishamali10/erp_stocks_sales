<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['login'] = 'auth/login';
$route['logout'] = 'auth/logout';
$route['home'] = 'welcome/index';
$route['products'] = 'products/index';
$route['products/create'] = 'products/create';
$route['products/store'] = 'products/store';
$route['products/edit/(:num)'] = 'products/edit/$1';
$route['products/update/(:num)'] = 'products/update/$1';
$route['products/toggle-status/(:num)'] = 'products/toggle_status/$1';
$route['categories'] = 'categories/index';
$route['categories/create'] = 'categories/create';
$route['categories/store'] = 'categories/store';
$route['categories/edit/(:num)'] = 'categories/edit/$1';
$route['categories/update/(:num)'] = 'categories/update/$1';
$route['categories/delete/(:num)'] = 'categories/delete/$1';
$route['stock'] = 'stock/index';
$route['stock/edit/(:num)/(:num)'] = 'stock/edit/$1/$2';
$route['stock/update/(:num)/(:num)'] = 'stock/update/$1/$2';
$route['warehouses'] = 'warehouses/index';
$route['warehouses/create'] = 'warehouses/create';
$route['warehouses/store'] = 'warehouses/store';
$route['warehouses/edit/(:num)'] = 'warehouses/edit/$1';
$route['warehouses/update/(:num)'] = 'warehouses/update/$1';
$route['warehouses/toggle-status/(:num)'] = 'warehouses/toggle_status/$1';
$route['customers'] = 'customers/index';
$route['customers/create'] = 'customers/create';
$route['customers/store'] = 'customers/store';
$route['customers/edit/(:num)'] = 'customers/edit/$1';
$route['customers/update/(:num)'] = 'customers/update/$1';
$route['customers/delete/(:num)'] = 'customers/delete/$1';
$route['sales'] = 'sales/index';
$route['sales/create'] = 'sales/create';
$route['sales/search-products'] = 'sales/search_products';
$route['sales/store'] = 'sales/store';
$route['reports/low-stock'] = 'reports/low_stock';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

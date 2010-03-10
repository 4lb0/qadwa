<?php

session_start();

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/src/Qadwa.php';

if (!isset($_SESSION['debug'])) {
	$_SESSION['debug'] = 0;
}

print Qadwa::getInstance(array(
    'baseUrl' => '/examples/06_errors/',

    // the debug mode is helpful to watch the errors.
    'debug' => $_SESSION['debug'],

    // this set the static Not Found page. By default is "error400"
    'error400Template' => 'error/not-found',
    
    // this set the static Internal Error page. By default is "error500"
    'error500Template' => 'error/internal',
));

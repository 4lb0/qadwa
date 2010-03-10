<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/src/Qadwa.php';

// This is the same case that the hello world example but now
// you must ensure to have the .htacess and the Apache Rewrite module 
print Qadwa::getInstance(array(
    'baseUrl' => '/examples/02_static/',
));

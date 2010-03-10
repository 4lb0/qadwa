<?php

// Include the class
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/src/Qadwa.php';

// Just print the Qadwa instance
print Qadwa::getInstance(array(
    // ... and you only need to change this base url
    'baseUrl' => '/examples/01_helloworld/',
));

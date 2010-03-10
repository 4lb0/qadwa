<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/src/Qadwa.php';

print Qadwa::getInstance(array(
    'baseUrl' => '/examples/05_controller/',
));

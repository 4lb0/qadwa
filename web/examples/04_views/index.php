<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/src/Qadwa.php';

print Qadwa::getInstance(array(
    'baseUrl' => '/examples/04_views/',

    // Change the path and extension of the templates and layout
    'templateFile' => 'templates/%s.html',
    'layoutFile' => 'templates/%s.html',

    // Change the default module (default is index)
    'defaultModule' => 'home',
    // No layout, by default try to load "layout" as layout.
    'layout' => false,
));

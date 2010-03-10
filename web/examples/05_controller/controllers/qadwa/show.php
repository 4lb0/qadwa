<?php

// Here you're in the Qadwa singleton scope so $this
// is the same as Qadwa::getInstance().

// you can use /key/value in the URL
$show = $this->getParam('show');
// or you can use the number of the param /first/second/third..
$show = $this->getParam(2);

$return = array(
    'title' => 'Qadwa'
);

// The model and library are autoloaded
// by default you can found them in models/ and library/
// but you can change those paths.
$model = new ListModel();

// this is only to show the getParam example
switch ($show) {
    case 'pros':
        $return['title'] .= ' Pros';
        $return['items'] = $model->getPros();
        break;
    case 'cons':
        $return['title'] .= ' Cons';
        $return['items'] = $model->getCons();         
        break;
    default:
        $return['items'] = array(); 
}

return $return;
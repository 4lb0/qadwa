<?php

// here we check if a POST is received.
if (Qadwa::getInstance()->isPost()) {    
    $body = '';
    foreach (array('name', 'email', 'message') as $name) {
        $value = $_POST[$name];
        $body .= "$name: $value\n";
    }
    // Here fetch the user defined configuration
    // it is in the etc/defaul.ini file
    $contactMail = Qadwa::getInstance()->contact_mail;    
    $result = mail($contactMail, 'Contact', $body);
    $message = $result ? 
    	'Form sended ok!':
    	'There was an error while sending the form';
} else {
    $message = null;    
}

// Set the var to the template
// yes! is a return without any function, 
// it terminates the processing in this file
// you can check more info in http://php.net/include
return array('message' => $message);
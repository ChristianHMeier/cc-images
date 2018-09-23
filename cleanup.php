<?php
//load the handler class
include './class.handler.php';

//initialize the class with the upload parameters
$handler = new Handler(array('mode' => 'cleanup', 'images' => $_POST['images']));

//run the upload and output the result
echo json_encode($handler->run());


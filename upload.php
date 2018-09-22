<?php
//load the handler class
include './class.handler.php';

//initialize the class with the upload parameters
$handler = new Handler(array('mode' => 'upload', 'image' => array('binary' => $_POST['imageData'], 'name' => $_POST['imageName'])));

//run the upload and output the result
echo json_encode($handler->run());


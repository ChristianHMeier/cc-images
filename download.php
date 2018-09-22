<?php
//load the handler class
include './class.handler.php';

//initialize the class with the download parameters
$downloadArray = array('mode' => 'download', 'index' => $_POST['index']);
if (isset($_POST['list']))
{
    $downloadArray['list'] = $_POST['list'];
}
$handler = new Handler($downloadArray);//array('mode' => 'download', 'user' => $_POST['user'], 'image' => $_POST['image']));

//run the download and output the result
echo json_encode($handler->run());
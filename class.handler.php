<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of class
 *
 * @author ChristianHeriberto
 */

require_once 'lib/S3.php';



class Handler
{
    private $s3;
    function __construct($params)
    {
        $this->params = $params;
        $this->s3 = new S3();
    }
    //this is the function that executes once the object is created to determine 
    //if images  must be uploaded or downloaded
    public function run()
    {
        $mode = $this->params['mode'];
        $return = null;
        switch ($mode)
        {
            case 'upload':
                $return = $this->receiveImage($this->params['image']['binary'], $this->params['image']['name']);
                break;
            case 'download':
                $downloadArray['index'] = $this->params['index'];
                if (isset($this->params['list']))
                {
                    $downloadArray['list'] = $_POST['list'];
                }
                $return = $this->sendImage($downloadArray);//['index'], $this->params['list']);
                break;
            case 'cleanup':
                $return = $this->cleanUp($this->params['images']);//['index'], $this->params['list']);
                break;
            default: $return = array('success' => false, message => 'Unauthorized operation.');
        }
        return $return;
    }
    //this function receives the base64 string of the image and uses it to 
    //create a copy of the image on the server
    private function receiveImage($imageString, $imageName)
    {
	$message = "";
	$success = true;
	//take the base64 string of the image and separate the type and file data
        $data = explode(',', $imageString);

        //filter the 'data:' out of the image type info, leaving either 'image/jpeg', 'image/png' or 'image/gif' for future use
        $type = str_replace('data:', '', $data[0]);
        
        //call to the S3 instance
        $copied = $this->s3->putObject(base64_decode($data[1]), 'django-balti', $imageName, S3::ACL_PUBLIC_READ, array(), array('Content-Type' => $type));
        
        //if block to verify the image was copied properly
        if ($copied == false)
        {
            $message = 'Could not save image "'.$imageName.'" to server. Please try again later, in case the error persists, contact the system provider.';
            $success = false;
        }
        if (strlen($message) == 0)
        {
            $message = 'Upload successful.';
        }
        return array('success' => $success, 'message' => $message);
    }
    
    //this function retrieves the images from the S3 bucket and sends them to the frontend
    private function sendImage($params)//$index, $list = array())
    {
        if (!isset($params['list']))
        {
            $list = $this->s3->getBucket('django-balti');
            $filteredList = array();
            $images = array();
            foreach ($list as $key => $value)
            {
                $breakKey = explode('.', $key);
                $extension = strtolower($breakKey[(count($breakKey)-1)]);
                if ($extension == 'jpg' || $extension == 'jpeg' ||
                    $extension == 'png' || $extension == 'gif'  || $extension == 'bmp')
                {
                    $filteredList[] = array('key' => $key, 'value' => $value, 'extension' => $extension);
                }
            }
            $params['list'] = $filteredList;
        }
        $localFile = '';
        for ($i = 0; $i < count($params['list']); $i++)
        {
            $folders = explode('/', $params['list'][$i]['key']);
            $images[] =  $folders[(count($folders)-1)];
        }
        for ($i = 0; $i < count($images); $i++)
        {
            $fileHandle = fopen($images[$i], 'wb');
            $file = $this->s3->getObject('django-balti', $params['list'][$i]['key'], $fileHandle);//$params['list'][$params['index']]['key'], $fileHandle);//fopen($localFile, 'wb'));//, fopen($list$params['index']->Key, 'wb'));
        }
        $base = $_SERVER['SERVER_NAME'].'/cc-images/';
	return array(
                        'base' => $base,
                        'images' => $images
                    );
    }
    
    private function cleanUp($images)
    {
        for ($i = 0; $i < count($images); $i++)
        {
            unlink($images[$i]);
        }
        return array('success' => 1, 'message' => 'All images cleared from the server');
    }
}

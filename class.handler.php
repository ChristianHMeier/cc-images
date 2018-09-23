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

        //create a temprary copy of the image in the server using the second part of the split string
        //$image = imagecreatefromstring(base64_decode($data[1]));

        //fetch the image dimensions
	//$height = imagesy($image);
        //$width = imagesx($image);

        //create canvas to deposit the permanent copy of the image
        //$newImage = imagecreatetruecolor($width, $height);

        //apply filters to keep image quality
        //imagealphablending($newImage, false);
        //imagesavealpha($newImage, true);

        //make the path for the image copy, this must be updated to make the 
        //call to the S3 instance
        //$path = 'img'.DIRECTORY_SEPARATOR.$user.DIRECTORY_SEPARATOR.$imageName;
        $copied = $this->s3->putObject(base64_decode($data[1]), 'django-balti', $imageName, S3::ACL_PUBLIC_READ, array(), array('Content-Type' => $type));
        //save the image on the path
        //$copied = imagecopyresized($newImage, $image, 0, 0, 0, 0, $width, $height, $width, $height);
        //$copied = file_put_contents($path, base64_decode($data[1]));
        
        //if block to verify the image was copied properly
        if ($copied == false)
        {

            /*$check = null;
            switch ($type)
            {
                case 'image/jpeg':
                    $check = imagejpeg($newImage, $path, 100);
                    break;
                case 'image/png':
                    $check = imagepng($newImage, $path);
                    break;
                case 'image/gif':
                    $check = imagegif($newImage, $path);
                    break;
                /*case 'bmp':
                 *   $check = imagewbmp($newImage, $path);
                 *   break;
                 */
            //}

            //error in saving according to format
            /*if ($check == false)
            {*/
                $message = 'Could not save image "'.$imageName.'" to server. Please try again later, in case the error persists, contact the system provider.';
                $success = false;
            //}
        }
        /*else
        {
            $message = 'Could not process the image "'.$imageName.'". Please try again later, in case the error persists, contact the system provider.';
            $success = false;
        }*/
        if (strlen($message) == 0)
        {
            $message = 'Upload successful.';
        }
        return array('success' => $success, 'message' => $message);
    }
    //this function lists the images already saved in the server and sends the 
    //one corresponding to the current index value back to the app
    private function sendImage($params)//$index, $list = array())
    {
        //get the path for the user's folder
        //$path = 'img'.DIRECTORY_SEPARATOR.$user;
        
        //get the list of image files in alphabetic order
        //$files = array_values(array_diff(scandir($path), array('..', '.'))); //array_slice(scandir($path, SCANDIR_SORT_DESCENDING), 2);
        
        //if the current image feld is set, add it to the path, opposite case get the first image the file reader can get
        //$path .= DIRECTORY_SEPARATOR.$files[$index];
	//get the parts to download the name and extension
	//$pathParts = pathinfo($path);
        if (!isset($params['list']))
        {
            $list = $this->s3->getBucket('django-balti');
            //$list = $list['body']['Contents'];
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
        //if (!file_exists($params['list'][$params['index']]['key']))
        //{
            //$folders = explode('/', $params['list'][$params['index']]['key']);
            /*for ($i = 0; $i < count($folders)-1; $i++)
            {
                mk_dir($folders[$i], 0655);
                $localFile .= $folders[$i];
                if ($i != (count($folders)-2))
                {
                    $localFile .= DIRECTORY_SEPARATOR;
                }
            }*/
            //$localFile .= $folders[(count($folders)-1)];
        for ($i = 0; $i < count($params['list']); $i++)
        {
            $folders = explode('/', $params['list'][$i]['key']);
            $images[] =  $folders[(count($folders)-1)];
        }
            /*$newImage = imagecreatetruecolor('200', '200');
            file_put_contents($list[$params['index']]['key'], $newImage);
            $check = false;
            switch (strtolower($list[$params['index']]['extension']))
            {
                case 'jpeg':
                case 'jpg':
                    $check = imagejpeg($newImage, $list[$params['index']]['key'], 100);
                    break;
                case 'png':
                    $check = imagepng($newImage, $list[$params['index']]['key']);
                    break;
                case 'gif':
                    $check = imagegif($newImage, $list[$params['index']]['key']);
                    break;
                case 'bmp':
                    $check = imagewbmp($newImage, $list[$params['index']]['key']);
                    break;
                 
            }
            if ($check == false)
            {
                return array('success' => 0, 'message' => 'Failed to create placeholder file');
            }*/
        //}
        for ($i = 0; $i < count($images); $i++)
        {
            $fileHandle = fopen($images[$i], 'wb');
            $file = $this->s3->getObject('django-balti', $params['list'][$i]['key'], $fileHandle);//$params['list'][$params['index']]['key'], $fileHandle);//fopen($localFile, 'wb'));//, fopen($list$params['index']->Key, 'wb'));
        }
        //fclose($fileHandle);
        //$base64 = 'data:image/'.$params['list'][$params['index']]['extension'].';base64,'.base64_encode(file_get_contents($localFile));
        $base = $_SERVER['SERVER_NAME'].'/cc-images/';//.$localFile;
        //unlink($localFile);
	//$this->s3->copyObject('django-balti', 'django-balti'.'/'.$list[$params['index']]['key'], '', $list[$params['index']]['key'], S3::ACL_PRIVATE);
        //get the file contents and turn them into base64 notation
	//$data = file_get_contents($file);
	//$base64 = 'data:image/'.$pathParts['extension'].';base64,'.base64_encode($data);
        
        //increase the index value for the next download
        //$params['index']++;

	return array(
                        'base' => $base,
                        'images' => $images//$params['list'][0]['key'],
                        //'name' => $pathParts['filename'],
                        //'extension' => $pathParts['extension'],
                        //'index' => $params['index'],
                        //'list' => $params['list'],//count($files),
                        //'file' => $file
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

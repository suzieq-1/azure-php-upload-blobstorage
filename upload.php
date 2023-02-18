<?php 

require_once 'vendor/autoload.php';

// load libraries
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;

// connection string to azure, insert your string here
$connectionString = "<copy it from the azure portal";

// simple function to return 500 code
function send500() {
    header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error',true,500);
    // make the script stop as soon as possible so we save on compute seconds
    die();
}

// function to upload the file to azure blob service
function upload($file,$connectionString)
{
    // random filename 30 characters long
    $filename = bin2hex(random_bytes(30));
    $blobClient = BlobRestProxy::createBlobService($connectionString);
    $content = fopen($file, "r");
    $containerName = "yourcontainer";
    
    // you can add metadata if you want
    //$options = new CreateBlockBlobOptions();
    //$metadata = array(
    //    'foo' => 'bar',
    //    'foo2' => 'bar2',
    //    'foo3' => 'bar3');
    //$options->setMetadata($metadata);
    
    $options = new CreateBlockBlobOptions();
    
    // set the content type, this usually is not automatic
    $options->setContentType("image/jpeg");
    
    try {
        // Upload blob
        $blobClient->createBlockBlob($containerName, $filename, $content, $options);
        // edit your URL here
        $url = "https://storagename.blob.core.windows.net/container/$filename";
    } catch(ServiceException $e){
        $code = $e->getCode();
        $error_message = $e->getMessage();
        // you can either print or send an error code for testing
        send500();
        //echo($code.": ".$error_message."<br />");
    }

    // return the direct URL of the blob object (could be handy) or just return TRUE or else FALSE
    return $url;
}


// check the uploaded file
try {
    
    // check if the right name is set and there is no error
    if ( !isset($_FILES['file']['name']) || is_array($_FILES['file']['error']) ) {   
        send500();
    }

    // Check $_FILES['file']['error'] value 
    switch ($_FILES['file']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            send500();
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            send500();
        default:
            send500();
    }

    if ($_FILES['file']['size'] > 2000000) {
        send500();
    }

    // Check MIME type, we only accept image/jpeg in this case, add anything you want
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search(
        $finfo->file($_FILES['file']['tmp_name']),
        array(
            'jpg' => 'image/jpeg'
        ),
        true    
    )) {
        send500();
    }

    // this it the file handle
    $file = $_FILES['file']['tmp_name'];
    
} 
finally {}


// do whatever you want here
try {
    
    // upload the file
    $url = upload($file,$connectionString);

    // if the $url doesn't return or the function is false
    if (!$url === false) {
        send500();
    }
      
}
finally {}



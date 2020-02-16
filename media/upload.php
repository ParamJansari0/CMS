<?php
include_once 'includes/dbh.inc.php';
include_once 'includes/Media.php';

if(isset($_POST['submit'])){
    $file = $_FILES['file'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = $file['type'];
    $userId = 1;

    $fileExt = explode('.',$fileName); // file extension with '.' (ex: '.jpg')
    $fileActualExt = strtolower(end($fileExt)); // file extension without '.' (ex: 'jpg')

    $allowed = array('jpg', 'jpeg','png', 'pdf');

    if(in_array($fileActualExt,$allowed)){
        if($fileError===0){
            if($fileSize < 1000000){
//                $fileNameNew = uniqid('',true).".".$fileActualExt;// for files with same name
                $fileDestination = 'uploads/'.$userId.'/'.$fileName;
                $check = move_uploaded_file($fileTmpName, $fileDestination);
                if($check != true){
                    mkdir('uploads/'.$userId);
                    $check = move_uploaded_file($fileTmpName, $fileDestination);
                }
                $media = new Media();
                $media->addImage(1,$fileActualExt,$fileDestination,"rawrXD");
                header("Location: user_media.php?uploadsuccess");
            }else{
                echo "Your file is too big.";
            }

        }else{
            echo("There was an error uploading your file");
        }
    }
    else{
        echo"You cannot upload files of this type!";
    }
}



<?php

function clean($data)
{
    return htmlspecialchars(
        trim($data)
    );
}


function uploadProductImage($file)
{
    if (
        empty($file["name"]) ||
        $file["error"] != 0
    ) {
        return "";
    }


    $allowedFiles = [
        "jpg",
        "jpeg",
        "png",
        "webp"
    ];


    $fileExtension = strtolower(
        pathinfo(
            $file["name"],
            PATHINFO_EXTENSION
        )
    );


    if (
        !in_array(
            $fileExtension,
            $allowedFiles
        )
    ) {
        return "";
    }


    if (
        $file["size"] > 5000000
    ) {
        return "";
    }


    $newFileName =
        time() .
        "_" .
        rand(1000, 9999) .
        "." .
        $fileExtension;


    $uploadFolder =
        __DIR__ .
        "/../uploads/";


    if (!is_dir($uploadFolder)) {

        mkdir(
            $uploadFolder,
            0777,
            true
        );
    }


    $destination =
        $uploadFolder .
        $newFileName;


    if (
        move_uploaded_file(
            $file["tmp_name"],
            $destination
        )
    ) {
        return $newFileName;
    }


    return "";
}


function getProductImage($image)
{
    if (!empty($image)) {

        $filePath =
            __DIR__ .
            "/../uploads/" .
            $image;


        if (file_exists($filePath)) {

            return
                "uploads/" .
                $image;
        }
    }


    return
        "images/default-product.png";
}

?>
<?php

namespace Image;

class Image
{
    /* upload image & return file path */
    public static function uploadImage($requestImage, $extension, $service, $width = 300, $height = 300, $base64 = false, $prefix = '')
    {
        $resize_img = ($base64) ? self::resizeBase64Image($requestImage, $extension, $width, $height) : self::resizeFileImage($requestImage, $width, $height);
        return self::imageUploadToServer($resize_img, $extension, $service, $prefix);
    }


    /* resize base64 image */
    public static function resizeBase64Image($base64_img, $extension, $newwidth = 500, $newheight = 500)
    {
        $image = base64_decode($base64_img);
        list($width, $height) = getimagesizefromstring($image);
        $thumb = imagecreatetruecolor($newwidth, $newheight);
        $source = imagecreatefromstring($image);
        imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

        ob_start();
        if($extension == 'png' || $extension == 'PNG') {
            imagepng($thumb);
        } else {
            imagejpeg($thumb);
        }
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }


    /* resize file image */
    public static function resizeFileImage($request_img, $newwidth = 500, $newheight = 500)
    {
        list($width, $height) = getimagesize($request_img);
        $thumb = imagecreatetruecolor($newwidth, $newheight);

        switch ($request_img->getClientmimeType()) {
            case 'image/jpeg':
            case 'image/jpg':
                $source = imagecreatefromjpeg($request_img);
                imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                ob_start();
                imagejpeg($thumb);
                $contents = ob_get_contents();
                ob_end_clean();
                return $contents;
                break;

            case 'image/png':
                $source = imagecreatefrompng($request_img);
                imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                ob_start();
                imagepng($thumb, null, 5);
                $contents = ob_get_contents();
                ob_end_clean();
                return $contents;
                break;

            default:
                return false;
                break;
        }
    }


    /* upload base64 image & return file path */
    public static function imageUploadToServer($resize_img, $extension, $service, $prefix = '')
    {
        //Replace Special Chars & Spaces
        $prefix = str_replace(' ', '_', $prefix); // Replaces all spaces with hyphens.
        $prefix = preg_replace('/[^A-Za-z0-9\-]/', '_', $prefix); // Removes special chars.
        $prefix = preg_replace('/_+/', '_', $prefix); // Replaces multiple _ with single one.

        $yearMonth = date("Y") . "/" . date("m") . "/";
        $path = 'uploads/' . $service . '/' . $yearMonth;
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $root_dir = getServerRootDirectory(); // Path to the project's root folder
        $imageName = $prefix . rand(1, 99) . '_' . time() . '.' . $extension;
        $imagePath = $root_dir . '/' . $path . $imageName;
        file_put_contents($imagePath, $resize_img);

        return $yearMonth . $imageName;
    }


    /* remove previous uploaded image */
    public static function removeUploadImage($path = '', $image)
    {
        $root_dir = getServerRootDirectory();
        $file = $root_dir . '/' . $path . '/' . $image;
        if (file_exists($file)) {
            unlink($file);
        }
    }

}

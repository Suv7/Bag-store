<?php

if (!function_exists('upload_image')) {
    function upload_image($file) {
        if (!isset($file)) return;

        $target_file = UPLOAD_DIR . "/" . basename($file["image"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $check = getimagesize($file["image"]["tmp_name"]);

        if ($check) {
            $uploadOk = 1;
        } else {
            echo "File is not an image";
            $uploadOk = 0;
        }

        if (file_exists($target_file)) $uploadOk = 0;
        if ($file["image"]["size"] > UPLOAD_MAX_FILE_SIZE) $uploadOk = 0;

        if (
            $imageFileType != "jpg" &&
            $imageFileType != "png" &&
            $imageFileType != "jpeg" &&
            $imageFileType != "gif" &&
            $imageFileType != "webp"
        ) $uploadOk = 0;

        if ($uploadOk == 1) {
            if (!file_exists(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0777, true);
            }

            if (move_uploaded_file($file["image"]["tmp_name"], $target_file)) {
                return $target_file;
            } else {
                echo "Error moving uploaded file";
            }
        } else {
            echo "There was an error while uploading a file";
        }

        return null;
    }
}

if (!function_exists('getImageUrl')) {
    function getImageUrl($imagePath) {
        if (empty($imagePath)) {
            return '';
        }

        if (strncmp($imagePath, 'http', 4) === 0) {
            return $imagePath;
        }

        $imagePath = ltrim($imagePath, '/');
        $parts = explode('/', $imagePath);
        $encodedPath = implode('/', array_map('rawurlencode', $parts));

        return 'http://localhost:80/bags_store/' . $encodedPath;
    }
}
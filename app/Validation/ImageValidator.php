<?php

namespace App\Validation;

use App\Controllers\Controller;

class ImageValidator extends Controller
{
    protected $errors = [];
    //image mimetype
    protected $mimeType = "image/jpeg";
    //image size allowed(1mb)
    protected $size = 1048576;

    protected function isEmpty($image)
    {
        if ($image->getSize() == 0) {
            array_push($this->errors, 'Please add any image');
            var_dump($image->getSize());
        }
    }

    protected function validateSize($image)
    {
        if ($image->getSize() > $this->size) {
            array_push($this->errors, '"' . $image->getClientFilename() . '" is too large (' . $image->getSize() . ' > 1mb)!');
        }
    }

    protected function validateType($image)
    {
        if ($image->getClientMediaType() !== $this->mimeType) {
            // var_dump($image->getClientMediaType());
            // die();
            array_push($this->errors, '"' . $image->getClientFilename() . '" is wrong file format! Please use "'.$this->mimeType.'" only');
        }
    }

    public function failed($image)
    {
        if(file_exists($_FILES['image']['tmp_name']) || is_uploaded_file($_FILES['image']['tmp_name'])) {
            $this->isEmpty($image);
            $this->validateSize($image);
            $this->validateType($image);
            $_SESSION['errors']['image'] = $this->errors;
            $errors = $this->errors;
            return !empty($errors);
        } else {
            return;
        }
    }

    public function moveUploadedFile($directory, $image, $id, $table)
    {
        if(file_exists($_FILES['image']['tmp_name']) || is_uploaded_file($_FILES['image']['tmp_name'])) {

        $extension = pathinfo($image->getClientFilename(), PATHINFO_EXTENSION );
        $filename = sprintf('%s.%0.8s', $image->getClientFilename(), $extension);

        $image->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
     
        return $filename;

        } else {
            $exImg = $this->DBcontroller->getLastImage($id, $table);

            return $exImg['image'];
        }
    }
}

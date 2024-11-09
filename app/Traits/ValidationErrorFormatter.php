<?php

namespace App\Traits;

trait ValidationErrorFormatter
{
    public function formatValidationError(array $errors){
        $formattedErrors = [];
        foreach($errors as $key => $value){
            $formattedErrors[$key] = $value[0];
        }
        return $formattedErrors;
    }
}

<?php

namespace App\Exceptions;

use Exception;

class FilterException extends Exception
{
    private array $errors = [];

    private array $debugData = [];

    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setDebugData(array $data)
    {
        $this->debugData = $data;
    }

    public function getDebugData(): array
    {
        return $this->debugData;
    }
}

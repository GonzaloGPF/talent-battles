<?php

namespace App\Exceptions;

use Exception;

class FilterException extends Exception
{
    /**
     * @var array<string, string>
     */
    private array $errors = [];

    /**
     * @var array<int, string>
     */
    private array $debugData = [];

    /**
     * @param  array<string, string>  $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param  array<int, string>  $data
     */
    public function setDebugData(array $data): void
    {
        $this->debugData = $data;
    }

    /**
     * @return array<int, string>
     */
    public function getDebugData(): array
    {
        return $this->debugData;
    }
}

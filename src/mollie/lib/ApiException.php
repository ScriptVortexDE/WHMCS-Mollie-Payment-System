<?php

namespace ScriptVortex\WhmcsMollie;

class ApiException extends \RuntimeException
{
    private ?string $field;

    public function __construct(string $message, int $httpStatus = 0, ?string $field = null)
    {
        parent::__construct($message, $httpStatus);
        $this->field = $field;
    }

    public function getHttpStatus(): int
    {
        return $this->getCode();
    }

    public function getField(): ?string
    {
        return $this->field;
    }
}

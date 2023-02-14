<?php

namespace CoreSys\UserManagement\Exceptions;

use Exception;
use Throwable;

class UserExistsException extends Exception
{
    public function __construct(?string $email = null, ?int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(
            sprintf('User with email `%s` already exists', $email ??= ''),
            $code,
            $previous
        );
    }
}

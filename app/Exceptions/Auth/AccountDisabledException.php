<?php

namespace App\Exceptions\Auth;

use RuntimeException;

class AccountDisabledException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Account is disabled.', 403);
    }
}

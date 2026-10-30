<?php

namespace App\Models;

/**
 * Backward compatibility alias for the User model.
 */
class users extends User
{
    protected $table = 'users';
}

<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * HTTP-контроллер .
 */
abstract class Controller
{
    use AuthorizesRequests;
}

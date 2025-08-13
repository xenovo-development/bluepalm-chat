<?php

namespace App\Facades;

use App\Services\MessageService as MessageServiceInstance;
use Illuminate\Support\Facades\Facade;

/**
 *
 */

class MessageService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
       return MessageServiceInstance::class;
    }
}

<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use App\Services\ConversationService as ConversationServiceInstance;

/**
 *
 */

class ConversationService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
       return ConversationServiceInstance::class;
    }
}

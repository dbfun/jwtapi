<?php

namespace Dbfun\JwtApi\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuthOk
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $request;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, array $request)
    {
        $this->user = $user;
        $this->request = $request;
    }

}

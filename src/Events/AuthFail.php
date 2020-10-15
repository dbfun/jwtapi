<?php

namespace Dbfun\JwtApi\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuthFail
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $error;
    public $request;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $error, array $request)
    {
        $this->error = $error;
        $this->request = $request;
    }

}

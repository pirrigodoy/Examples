<?php
declare(strict_types=1);
namespace Cookie;


// ############################################################################
// Cookie Class
// ############################################################################

class Cookie {

    public string $name;
    public string $value;
    public int    $expiration_date;

    // ----------------------------------------------------------------------------
    public function __construct(string  $name            = '',
                                string  $value           = '',
                                int     $expiration_date = 0  ){
        
        $this->name             = $name;
        $this->value            = $value;
        $this->expiration_date  = $expiration_date;
    }
}

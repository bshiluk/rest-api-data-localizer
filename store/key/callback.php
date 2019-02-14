<?php

namespace RADL\Store\Key;

class Callback implements Value
{
    /**
     * Identifies callback
     * @var callable
     */
    private $callable;
    /**
     * If callback has already been called
     * @var boolean
     */
    private $called = false;
    /**
     * Callback output
     * @var mixed
     */
    private $returned;

    public function __construct( callable $callable )
    {
        $this->callable = $callable;
    }

    private function call( array $args = array() )
    {
        $this->returned = call_user_func( $this->callable, $args );
        $this->called = true;
    }

    public function get( $args )
    {

        if ( !$this->called ) {
            $this->call( $args );
        }

        return $this->returned;
    }

    public function render()
    {

        if ( !$this->called ) {
            $this->call();
        }

        return $this->returned;
    }
}

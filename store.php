<?php

namespace RADL;

use RADL\Store\Key\Value;

class Store
{
    /**
     * Handle for script where Store will be localized
     * @var string
     */
    public $script_handle;

    /**
     * Single state tree for all requested data - initialized from $schema
     * @var array
     */
    private $state;

    /**
     * Will be name of Store variable in script
     * @var string
     */
    public $name;

    public function __construct( $name, $script_handle, array $schema )
    {
        $this->name = $name;
        $this->script_handle = $script_handle;
        $this->state = $schema;
    }

    public function get( $key_path, $args )
    {
        $value = $this->key_value( $key_path );

        if ( $value instanceof Value ) {
            $value = $value->get( $args );
        }

        return $value;
    }

    public function rendered()
    {
        $this->render_values( $this->state );
        return $this->state;
    }

    private function key_value( $key_path )
    {
        $value = $this->state;

        foreach ( explode( '.', $key_path ) as $key ) {

            if ( $key !== '' ) {
                $value = $value[$key];
            }

        }

        return $value;
    }

    private function render_values( array &$arr )
    {

        foreach ( $arr as $key => $value ) {

            if ( $value instanceof Value ) {
                $arr[$key] = $value->render();
            } elseif ( is_array( $value ) ) {
                $this->render_values( $value );
            }

        }

    }

}

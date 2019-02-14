<?php

namespace RADL\Store\Key;

interface Value
{
    public function get( $args );
    public function render();
}

<?php

namespace RADL\Store\Key;

class Internal_WP_REST_Request
{
    /**
     * Identifier for specific resource to request
     * @var mixed
     */
    private $id;
    /**
     * Parameters for request added if $id not defined
     * @var array
     */
    private $params = array();
    /**
     * WP core class used for REST requests
     * @var WP_REST_Request
     */
    private $request;
    /**
     * WP core class used for REST responses
     * @var WP_REST_Response
     */
    private $response;
    /**
     * Identifies endpoint to use for request
     * @var string
     */
    private $route;
    /**
     * Route base needed to identify endpoint
     * @var string
     */
    private $route_base;
    /**
     * Base for all REST routes
     * @var string
     */
    private static $rest_base = '/wp/v2/';

    public function __construct( $route_base, $args )
    {
        $this->route_base = $route_base;
        if ( is_array( $args ) ) {
            $this->params = $args;
        } else {
            $this->id = $args;
        }
        $this->create_route();
        $this->create_request();
        $this->set_params();
        $this->do_request();
    }

    public function get_response_data()
    {
        $server = \rest_get_server();
        return $server->response_to_data( $this->response, false );
    }

    public function get_response_headers()
    {
        return $this->response->get_headers();
    }

    private function create_request()
    {
        $this->request = new \WP_REST_Request( 'GET', $this->route );
    }

    private function create_route()
    {
        $route = self::$rest_base . $this->route_base;
        if ( $this->id ) {
            $route .= '/' . $this->id;
        }
        $this->route = $route;
    }

    private function do_request()
    {
        $this->response = \rest_do_request( $this->request );
        if ( $this->response->is_error() ) {
            echo '<script>console.log(' . json_encode( array( 'route' => $this->route, 'params' => $this->params, 'response' => $this->get_response_data() ) ) . ')</script>';
            echo '<h1>RADL Error - see console for details</h1>';
            wp_die();
        }
        \wp_reset_postdata();
    }

    private function set_params()
    {
        if ( !$this->id ) {
            $this->request->set_query_params( $this->params );
        }
    }

}

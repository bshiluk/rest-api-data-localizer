<?php

namespace RADL\Store\Key;

class Endpoint implements Value
{
    /**
     * Items fetched normalized as $items[item.id] = item
     * @var array
     */
    private $items = array();
    /**
     * Route base to use for requests
     * @var string
     */
    private $name;
    /**
     * $args of requests to make by default
     * @var array
     */
    private $prefetch;
    /**
     * Keeps track of requests made and associated response data
     * @var array
     */
    private $requests = array();

    public function __construct( $name, array $prefetch )
    {
        $this->name = $name;
        $this->prefetch = $prefetch;
    }

    public function get( $args )
    {

        if ( is_array( $args ) ) {
            return $this->get_all( $args );
        } elseif ( $args )  {
            return $this->get_by_id( $args );
        }

    }

    private function add_item( $val, $key )
    {

        if ( !isset( $this->items[$key] ) ) {
            $this->items[$key] = $val;
        }

    }

    private function add_items( array $items, array $args, array $headers )
    {

        $id_key = $this->id_key_from_items( $items );
        $request = array(
            'params' => $args,
            'data' => array(),
        );

        if ( $id_key ) {

            foreach ( $items as $item ) {
                $this->add_item( $item, $item[$id_key] );
                array_push( $request['data'], $item[$id_key] );
            }

        } else {

            foreach ( $items as $key => $val ) {
                $this->add_item( $val, $key );
                array_push( $request['data'], $key );
            }

        }

        if ( isset( $headers['X-WP-Total'] ) ) {
            $request['total'] = $headers['X-WP-Total'];
            $request['totalPages'] = $headers['X-WP-TotalPages'];
        }

        array_push( $this->requests, $request );

    }

    private function get_all( array $args )
    {
        $requested = $this->get_requested( $args );

        if ( is_null( $requested ) ) {
            $this->request( $args );
            $requested = $this->get_requested( $args );
        }

        return $this->ids_to_items( $requested['data'] );
    }

    private function get_by_id( $id )
    {

        if ( !isset( $this->items[$id] ) ) {
            $this->request( $id );
        }

        return $this->items[$id];

    }

    private function get_requested( array $args )
    {

        foreach ( $this->requests as $request ) {

            if ( count( $request['params'] ) === count( $args ) && empty( array_diff_assoc( $request['params'], $args ) ) ) {
                return $request;
            }

        }

    }

    private function id_key_from_items( array $items )
    {

        if ( count( $items ) ) {

            $item = array_values( $items )[0];

            if ( is_array( $item ) ) {

                if ( isset( $item['id'] ) ) {
                    return 'id';
                } elseif ( isset( $item['slug'] ) ) {
                    return 'slug';
                }

            }

        }

    }

    private function ids_to_items( array $ids )
    {
        $items = array();

        foreach ( $ids as $id ) {
            array_push( $items, $this->items[$id] );
        }

        return $items;
    }

    public function render()
    {

        foreach ( $this->prefetch as $args ) {
            $this->get( $args );
        }

        $this->items['requests'] = $this->requests;
        return $this->items;
    }

    private function request( $args )
    {
        $request = new Internal_WP_REST_Request( $this->name, $args );
        $data = $request->get_response_data();

        if ( is_array( $args ) ) {
            $headers = $request->get_response_headers();
            $this->add_items( $data, $args, $headers );
        } else {
            $this->add_item( $data, $args );
        }

    }

}

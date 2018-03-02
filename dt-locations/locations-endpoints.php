<?php

/**
 * Disciple_Tools_Locations_Endpoints
 *
 * @class   Disciple_Tools_Locations_Endpoints
 * @version 0.1.0
 * @since   0.1.0
 * @package Disciple_Tools
 *
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Locations_Endpoints
 */
class Disciple_Tools_Locations_Endpoints
{

    private $version = 1;
    private $context = "dt";
    private $namespace;

    /**
     * Disciple_Tools_Locations_Endpoints The single instance of Disciple_Tools_Locations_Endpoints.
     *
     * @var    object
     * @access private
     * @since  0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Locations_Endpoints Instance
     * Ensures only one instance of Disciple_Tools_Locations_Endpoints is loaded or can be loaded.
     *
     * @since  0.1.0
     * @static
     * @return Disciple_Tools_Locations_Endpoints instance
     */
    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access public
     * @since  0.1.0
     */
    public function __construct()
    {
        $this->namespace = $this->context . "/v" . intval( $this->version );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    } // End __construct()

    /**
     * Registers all of the routes associated with locations
     */
    public function add_api_routes()
    {
        $base = '/locations';

        // Holds all routes for locations
        $routes = [
            $base => [
                'methods' => 'GET',
                'callback' => [$this, 'get_locations'],
            ],
            '/locations-compact' => [
                'methods' => 'GET',
                'callback' => [$this, 'get_locations_compact'],
            ],
            "$base/findbyaddress" => [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'find_by_address'],
            ],
            "$base/gettractmap" => [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'get_tract_map'],
            ],
            "$base/getmapbygeoid" => [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'get_map_by_geoid'],
            ],
            "$base/import_check" => [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'import_check'],
            ],
        ];

        // Register each route
        foreach ($routes as $route => $args) {
            register_rest_route($this->namespace, $route, $args);
        }
    }

    /**
     * This import check is run at the end of the location import utility
     * @see /dt-core/utilities/locations-import-csv.php
     * @return array|\WP_Error
     */
    public function import_check() {
        $count = get_transient( 'dt_import_finished_count' );
        $errors = get_transient( 'dt_import_finished_with_errors' );

        if ( empty( $count ) ) {
            $count = 0;
        }
        if ( empty( $errors ) ) {
            $errors = [];
        }

        return [
        'count' => $count,
        'errors' => $errors,
        ];
    }

    /**
     * Get tract from submitted address
     *
     * @param  WP_REST_Request $request
     *
     * @access public
     * @since  0.1.0
     * @return string|WP_Error The contact on success
     */
    public function find_by_address( WP_REST_Request $request )
    {
        $params = $request->get_params();
        if ( isset( $params['address'] ) ) {
            $result = Disciple_Tools_Locations::get_tract_by_address( $params['address'] );
            if ( $result["status"] == 'OK' ) {
                return $result["tract"];
            } else {
                return new WP_Error( "tract_status_error", $result["message"], [ 'status' => 400 ] );
            }
        } else {
            return new WP_Error( "tract_param_error", "Please provide a valid address", [ 'status' => 400 ] );
        }
    }

    /**
     * Get tract from submitted address
     *
     * @param  WP_REST_Request $request
     *
     * @access public
     * @since  0.1.0
     * @return string|WP_Error|array The contact on success
     */
    public function get_tract_map( WP_REST_Request $request )
    {
        $params = $request->get_params();
        if ( isset( $params['address'] ) ) {
            $result = Disciple_Tools_Locations::get_tract_map( $params['address'] );
            if ( $result["status"] == 'OK' ) {
                return $result;
            } else {
                return new WP_Error( "map_status_error", $result["message"], [ 'status' => 400 ] );
            }
        } else {
            return new WP_Error( "map_param_error", "Please provide a valid address", [ 'status' => 400 ] );
        }
    }

    /**
     * Get map by geoid
     *
     * @param  WP_REST_Request $request
     *
     * @access public
     * @since  0.1.0
     * @return string|WP_Error|array The contact on success
     */
    public function get_map_by_geoid( WP_REST_Request $request )
    {
        $params = $request->get_params();
        if ( isset( $params['geoid'] ) ) {
            $result = Disciple_Tools_Locations::get_map_by_geoid( $params );
            if ( $result["status"] == 'OK' ) {
                return $result;
            } else {
                return new WP_Error( "map_status_error", $result["message"], [ 'status' => 400 ] );
            }
        } else {
            return new WP_Error( "map_param_error", "Please provide a valid address", [ 'status' => 400 ] );
        }
    }

    /**
     * @return array|\WP_Error
     */
    public function get_locations()
    {
        //        $params = $request->get_params();
        //        @TODO check permissions
        $locations = Disciple_Tools_Locations::get_locations();

        return $locations;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array
     */
    public function get_locations_compact( WP_REST_Request $request )
    {
        $params = $request->get_params();
        $search = "";
        if ( isset( $params['s'] ) ) {
            $search = $params['s'];
        }
        $locations = Disciple_Tools_Locations::get_locations_compact( $search );

        return $locations;
    }
}

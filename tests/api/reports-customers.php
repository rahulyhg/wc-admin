<?php
/**
 * Reports Customers REST API Test
 *
 * @package WooCommerce\Tests\API
 * @since 3.5.0
 */

/**
 * Reports Customers REST API Test Class
 *
 * @package WooCommerce\Tests\API
 * @since 3.5.0
 */
class WC_Tests_API_Reports_Customers extends WC_REST_Unit_Test_Case {
	/**
	 * Endpoint.
	 *
	 * @var string
	 */
	protected $endpoint = '/wc/v3/reports/customers';

	/**
	 * Setup test reports products data.
	 *
	 * @since 3.5.0
	 */
	public function setUp() {
		parent::setUp();

		$this->user = $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
	}

	/**
	 * Test route registration.
	 *
	 * @since 3.5.0
	 */
	public function test_register_routes() {
		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( $this->endpoint, $routes );
	}

	/**
	 * Test reports schema.
	 *
	 * @since 3.5.0
	 */
	public function test_reports_schema() {
		wp_set_current_user( $this->user );

		$request    = new WP_REST_Request( 'OPTIONS', $this->endpoint );
		$response   = $this->server->dispatch( $request );
		$data       = $response->get_data();
		$properties = $data['schema']['properties'];

		$this->assertEquals( 14, count( $properties ) );
		$this->assertArrayHasKey( 'customer_id', $properties );
		$this->assertArrayHasKey( 'user_id', $properties );
		$this->assertArrayHasKey( 'name', $properties );
		$this->assertArrayHasKey( 'username', $properties );
		$this->assertArrayHasKey( 'country', $properties );
		$this->assertArrayHasKey( 'city', $properties );
		$this->assertArrayHasKey( 'postcode', $properties );
		$this->assertArrayHasKey( 'date_registered', $properties );
		$this->assertArrayHasKey( 'date_registered_gmt', $properties );
		$this->assertArrayHasKey( 'date_last_active', $properties );
		$this->assertArrayHasKey( 'date_last_active_gmt', $properties );
		$this->assertArrayHasKey( 'orders_count', $properties );
		$this->assertArrayHasKey( 'total_spend', $properties );
		$this->assertArrayHasKey( 'avg_order_value', $properties );
	}

	/**
	 * Test getting reports without valid permissions.
	 *
	 * @since 3.5.0
	 */
	public function test_get_reports_without_permission() {
		wp_set_current_user( 0 );
		$response = $this->server->dispatch( new WP_REST_Request( 'GET', $this->endpoint ) );
		$this->assertEquals( 401, $response->get_status() );
	}

	/**
	 * Test getting reports.
	 *
	 * @since 3.5.0
	 */
	public function test_get_reports() {
		wp_set_current_user( $this->user );
		WC_Helper_Reports::reset_stats_dbs();

		$test_customers = array();

		// Create 10 test customers.
		for ( $i = 1; $i <= 10; $i++ ) {
			$test_customers[] = WC_Helper_Customer::create_customer( "customer{$i}", 'password', "customer{$i}@example.com" );
		}

		// Initialize the report lookup table.
		delete_transient( 'wc_update_350_all_customers' );
		WC_Admin_Api_Init::customer_lookup_store_init();

		// Create a test product for use in an order.
		$product = new WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->set_regular_price( 25 );
		$product->save();

		// Place an order for the first test customer.
		$order = WC_Helper_Order::create_order( $test_customers[0]->get_id(), $product );
		$order->set_status( 'processing' );
		$order->set_total( 100 );
		$order->save();

		$request  = new WP_REST_Request( 'GET', $this->endpoint );
		$request->set_query_params(
			array(
				'per_page' => 5,
				'order'    => 'asc',
				'orderby'  => 'username',
			)
		);

		$response = $this->server->dispatch( $request );
		$reports  = $response->get_data();
		$headers  = $response->get_headers();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 5, count( $reports ) );
		$this->assertArrayHasKey( 'X-WP-Total', $headers );
		$this->assertEquals( 10, $headers['X-WP-Total'] );
		$this->assertArrayHasKey( 'X-WP-TotalPages', $headers );
		$this->assertEquals( 2, $headers['X-WP-TotalPages'] );
		$this->assertEquals( $test_customers[0]->get_id(), $reports[0]['user_id'] );
		$this->assertEquals( 1, $reports[0]['orders_count'] );
		$this->assertEquals( 100, $reports[0]['total_spend'] );
	}
}
<?php

namespace Yoast\WP\SEO\Tests\Unit;

use Yoast\WP\SEO\Tests\Unit\Doubles\Main_Double;
use Brain\Monkey;
use Mockery;

/**
 * Class Loader_Test
 *
 * @coversDefaultClass \Yoast\WP\SEO\Main;
 */
class Main_Test extends TestCase {

	/**
	 * Represents the instance we are testing.
	 *
	 * @var Main_Double The Main class double.
	 */
	private $instance;

	/**
	 * Aliasses set in the DI container.
	 *
	 * @var array
	 */
	private $aliasses = [
		'service_container' => 'YoastSEO_Vendor\\YoastSEO_Vendor\\Symfony\\Component\\DependencyInjection\\ContainerInterface',
	];

	/**
	 * Sets an instance for test purposes.
	 */
	protected function set_up() {
		parent::set_up();

		$this->instance = new Main_Double();
		$this->instance->load();

		global $wpdb;
		$wpdb = Mockery::mock( '\wpdb' );

		// Some classes call the YoastSEO function in the constructor.
		Monkey\Functions\expect( 'YoastSEO' )
			->andReturn( $this->instance );
		// Deprecated classes call _deprecated_function in the constructor.
		Monkey\Functions\expect( '_deprecated_function' );
		// Permalink watcher calls wp_next_scheduled in the constructor.
		Monkey\Functions\expect( 'wp_next_scheduled' )
			->andReturn( true );
	}

	/**
	 * Tests the DI container.
	 *
	 * @covers ::get_container
	 */
	public function test_surfaces() {
		$container = $this->instance->get_container();

		foreach ( $container->getServiceIds() as $service_id ) {
			if ( isset( $this->aliasses[ $service_id ] ) ) {
				$service_id = $this->aliasses[ $service_id ];
			}
			if ( str_starts_with( $service_id, 'YoastSEO_Vendor' ) ) {
				continue;
			}

			$this->assertInstanceOf( $service_id, $container->get( $service_id ) );
		}
	}
}

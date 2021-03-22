<?php
/**
 * Tests WP_Mock_Demo_Plugin
 */
class Test_WP_Mock_Demo_Plugin extends PHPUnit\Framework\TestCase {
	/**
	 * Setup WP_Mock for each test
	 */
	public function setUp() {
		\WP_Mock::setUp();
	}
	/**
	 * Clean up after the test is run
	 */
	public function tearDown() {
		$this->addToAssertionCount(
			\Mockery::getContainer()->mockery_getExpectationCount()
		);
		\WP_Mock::tearDown();
	}

	public function test_hello() {
		$this->assertTrue( true );
	}
}

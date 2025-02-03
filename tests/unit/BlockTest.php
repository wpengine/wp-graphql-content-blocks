<?php

namespace WPGraphQL\ContentBlocks\Unit;

use Mockery;
use WPGraphQL\ContentBlocks\Blocks\Block;
use WPGraphQL\ContentBlocks\Type\Scalar\Scalar;
use WPGraphQL\ContentBlocks\Registry\Registry;
use WP_Block_Type;
use WP_Block_Type_Registry;
use WPGraphQL;

class BlockTest extends PluginTestCase {
	protected $block;

	public function setUp(): void {
		parent::setUp();

		// Ensure WP_Block_Type mock has a valid name.
		$blockMock = Mockery::mock(WP_Block_Type::class);
		$blockMock->name = 'test-block';  // Prevent null argument issue
		$blockMock->attributes = [];

		// Retrieve real instances required for Registry.
		$typeRegistry = WPGraphQL::get_type_registry();
		$blockTypeRegistry = WP_Block_Type_Registry::get_instance();

		// Create an instance of Registry with dependencies.
		$registry = new Registry($typeRegistry, $blockTypeRegistry);

		// Create an instance of Block with the real dependencies.
		$this->block = new Block($blockMock, $registry);
	}

	/**
	 * Access private method get_attribute_type using reflection.
	 */
	private function invokeGetAttributeType($name, $attribute, $prefix) {
		$method = new \ReflectionMethod($this->block, 'get_attribute_type');
		$method->setAccessible(true);
		return $method->invoke($this->block, $name, $attribute, $prefix);
	}

	public function testStringType() {
		$attribute = ['type' => 'string'];
		$this->assertEquals('String', $this->invokeGetAttributeType('block_name', $attribute, 'prefix'));
	}

	public function testBooleanType() {
		$attribute = ['type' => 'boolean'];
		$this->assertEquals('Boolean', $this->invokeGetAttributeType('block_name', $attribute, 'prefix'));
	}

	public function testFloatType() {
		$attribute = ['type' => 'number'];
		$this->assertEquals('Float', $this->invokeGetAttributeType('block_name', $attribute, 'prefix'));
	}

	public function testIntType() {
		$attribute = ['type' => 'integer'];
		$this->assertEquals('Int', $this->invokeGetAttributeType('block_name', $attribute, 'prefix'));
	}

	public function testArrayTypeWithoutQuery() {
		$expectedArrayType = Scalar::get_block_attributes_array_type_name();

		$attribute = ['type' => 'array'];
		$this->assertEquals(
			$expectedArrayType,
			$this->invokeGetAttributeType('block_name', $attribute, 'prefix')
		);
	}

	public function testArrayTypeWithQuery() {
		$attribute = [
			'type' => 'array',
			'query' => [
				'key' => [
					'type' => 'string',
				],
			],
		];

		$result = $this->invokeGetAttributeType('block_name', $attribute, 'prefix');

		$this->assertIsArray($result);
		$this->assertArrayHasKey('list_of', $result);
		$this->assertIsString($result['list_of']); // Expecting a generated GraphQL type name
	}


	public function testObjectType() {
		// Call the real method instead of mocking
		$expectedObjectType = Scalar::get_block_attributes_object_type_name();

		$this->assertEquals(
			$expectedObjectType,
			$this->invokeGetAttributeType('block_name', ['type' => 'object'], 'prefix')
		);
	}

	public function testWithDefaultValue() {
		$attribute = ['type' => 'string', 'default' => 'test_value'];
		$this->assertEquals(['non_null' => 'String'], $this->invokeGetAttributeType('block_name', $attribute, 'prefix'));
	}

	public function testArrayTypeWithItems() {
		$attribute = [
			'type' => 'array',
			'items' => ['type' => 'integer']
		];

		$result = $this->invokeGetAttributeType('block_name', $attribute, 'prefix');

		$this->assertIsArray($result);
		$this->assertArrayHasKey('list_of', $result);
		$this->assertEquals('Int', $result['list_of']); // Should be a list of integers
	}

	public function testAttributeWithSourceKey() {
		$attribute = ['source' => 'meta'];

		$this->assertEquals('String', $this->invokeGetAttributeType('block_name', $attribute, 'prefix'));
	}

	public function testUnknownOrMissingType() {
		$attribute = []; // No 'type' key

		$this->assertNull($this->invokeGetAttributeType('block_name', $attribute, 'prefix'));
	}

	public function testDefaultValuesForAllTypes() {
		$cases = [
			['type' => 'string', 'default' => 'text', 'expected' => ['non_null' => 'String']],
			['type' => 'boolean', 'default' => true, 'expected' => ['non_null' => 'Boolean']],
			['type' => 'number', 'default' => 3.14, 'expected' => ['non_null' => 'Float']],
			['type' => 'integer', 'default' => 42, 'expected' => ['non_null' => 'Int']],
			['type' => 'array', 'default' => [1, 2, 3], 'expected' => ['non_null' => Scalar::get_block_attributes_array_type_name()]],
		];

		foreach ($cases as $case) {
			$this->assertEquals($case['expected'], $this->invokeGetAttributeType('block_name', $case, 'prefix'));
		}
	}

	public function tearDown(): void {
		Mockery::close();
		parent::tearDown();
	}
}

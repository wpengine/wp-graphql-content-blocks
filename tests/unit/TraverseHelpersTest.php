<?php

namespace WPGraphQL\ContentBlocks\Unit;

use PHPUnit\Framework\TestCase;
use WPGraphQL\ContentBlocks\Utilities\TraverseHelpers;

class TraverseHelpersTest extends PluginTestCase {
    public $post_id;
    public function setUp(): void {
		parent::setUp();
		global $wpdb;

		$this->post_id = wp_insert_post(
			array(
				'post_title'   => 'Post Title',
				'post_content' => preg_replace(
					'/\s+/',
					' ',
					trim(
						'
				<!-- wp:paragraph -->
				<p class="has-background" style="background-color:#a62929">Test</p>
				<!-- /wp:paragraph -->'
					)
				),
				'post_status'  => 'publish',
			)
		);
	}

	public function tearDown(): void {
		// your tear down methods here
		parent::tearDown();
		wp_delete_post( $this->post_id, true );
	}
	public function testTraverseBlocks() {
		// Sample blocks data
		$blocks = [ 
			[ 
				'blockName' => 'core/group',
				'attrs' => [],
				'innerBlocks' => [ 
					[ 
						'blockName' => 'core/block',
						'attrs' => [ 'ref' => $this->post_id ],
						'innerBlocks' => []
					]
				]
			],
			[ 
				'blockName' => 'core/block',
				'attrs' => [ 'ref' => $this->post_id ],
				'innerBlocks' => []
			]
		];

		// Expected result after replacing reusable blocks
		$expected = [ 
			[ 
				'blockName' => 'core/group',
				'attrs' => [],
				'innerBlocks' => [ 
					[ 
						'blockName' => 'core/paragraph',
						'attrs' => [],
						'innerBlocks' => [],
						'innerHTML' => ' <p class="has-background" style="background-color:#a62929">Test</p> ',
						'innerContent' => [ 0 => ' <p class="has-background" style="background-color:#a62929">Test</p> ']
					]
				]
			],
			[ 
				'blockName' => 'core/paragraph',
				'attrs' => [],
				'innerBlocks' => [],
				'innerHTML' => ' <p class="has-background" style="background-color:#a62929">Test</p> ',
				'innerContent' => [ 0 => ' <p class="has-background" style="background-color:#a62929">Test</p> ']
			]
		];

		TraverseHelpers::traverse_blocks( $blocks, [ 'WPGraphQL\ContentBlocks\Utilities\TraverseHelpers', 'replace_reusable_blocks' ], 0, PHP_INT_MAX );
		$this->assertEquals( $expected, $blocks );
	}
}
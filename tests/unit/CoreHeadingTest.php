<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CoreHeadingTest extends PluginTestCase
{
    public $instance;
    public $post_id;

    public function setUp(): void
    {
        parent::setUp();
        global $wpdb;

        $this->post_id = wp_insert_post(
            array(
                'post_title' => 'Post with Heading',
                'post_content' => preg_replace(
                    '/\s+/',
                    ' ',
                    trim(
                        '
                        <!-- wp:heading {"level":2,"textAlign":"center","style":{"typography":{"fontSize":"28px","fontStyle":"normal","fontWeight":"700"}}} -->
                        <h2 class="wp-block-heading has-text-align-center" style="font-size:28px;font-style:normal;font-weight:700">Sample Heading</h2>
                        <!-- /wp:heading -->
                        '
                    )
                ),
                'post_status' => 'publish',
            )
        );
    }

    public function tearDown(): void
    {
        parent::tearDown();
        wp_delete_post($this->post_id, true);
    }

    public function test_retrieve_core_heading_attributes()
    {
        $query = '
          fragment CoreHeadingBlockFragment on CoreHeading {
            attributes {
              content
              level
              textAlign
              style
            }
          }

          query GetPosts {
            posts(first: 1) {
              nodes {
                databaseId
                editorBlocks {
                  name
                  ...CoreHeadingBlockFragment
                }
              }
            }
          }
        ';

        $actual = graphql(array('query' => $query));
        $node = $actual['data']['posts']['nodes'][0];

        // Verify that the ID of the first post matches the one we just created.
        $this->assertEquals($this->post_id, $node['databaseId']);

        // There should be only one block using that query when not using flat: true
        $this->assertEquals(count($node['editorBlocks']), 1);
        $this->assertEquals($node['editorBlocks'][0]['name'], 'core/heading');

        $this->assertEquals(
            $node['editorBlocks'][0]['attributes'],
            [
                'content' => 'Sample Heading',
                'level' => 2,
                'textAlign' => 'center',
                'style' => [
                    'typography' => [
                        'fontSize' => '28px',
                        'fontStyle' => 'normal',
                        'fontWeight' => '700',
                    ],
                ],
            ]
        );
    }

    public function test_retrieve_core_heading_content()
    {
        $query = '
          fragment CoreHeadingBlockFragment on CoreHeading {
            content
          }

          query GetPosts {
            posts(first: 1) {
              nodes {
                editorBlocks {
                  ...CoreHeadingBlockFragment
                }
              }
            }
          }
        ';

        $actual = graphql(array('query' => $query));
        $node = $actual['data']['posts']['nodes'][0];

        $this->assertEquals($node['editorBlocks'][0]['content'], 'Sample Heading');
    }
}

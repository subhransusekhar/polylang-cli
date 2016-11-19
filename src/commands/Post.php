<?php

namespace Polylang_CLI\Commands;

/**
 * Class Post
 *
 * @package Polylang_CLI
 */
class Post extends BaseCommand {

    /**
     * Get post for a language.
     *
     * ## OPTIONS
     *
     * <post_id>
     * : Post ID of the post to get. Required.
     *
     * [<language-code>]
     * : The language code (slug) to get the post ID for, when using the --api flag. Optional.
     *
     * [--api]
     * : Use the Polylang API function pll_get_post()
     *
     * ## EXAMPLES
     *
     *     wp pll post get 2
     *     wp pll post get 67 es
     */
    public function get( $args, $assoc_args ) {

        list( $post_id ) = $args;

        if ( ! $post = get_post( $post_id ) ) {
            $this->cli->error( sprintf( '%d is not a valid post object', $post_id ) );
        }

        if ( ! $this->api->is_translated_post_type( $post->post_type ) ) {
            $this->cli->error( 'Polylang does not manage languages and translations for this post type.' );
        }

        if ( $this->cli->flag( $assoc_args, 'api' ) ) {

            # second param of pll_get_post() is empty string by default
            $slug = isset( $args[1] ) && $args[1] ? $args[1] : '';

            echo $this->api->get_post( $args[0], $slug );

        } else {

            var_dump( $this->api->get_post_translations( $post_id ) );

        }
    }

    /**
     * Count posts for a language
     *
     * ## OPTIONS
     *
     * <language-code>
     * : The language code (slug) to get the post count for. Required.
     *
     * [--post_type=<post_type>]
     * : One or more post types to get the count for for. Default: post. Optional.
     *
     * ## EXAMPLES
     *
     *     wp pll post count nl
     *     wp pll post count es --post_type=page
     */
    public function count( $args, $assoc_args ) {

        $language = $this->pll->model->get_language( $args[0] );

        $this->cli->success( sprintf( 'Post count: %d', $this->api->count_posts( $language, $assoc_args ) ) );
    }

    /**
     * Get a list of posts in a language.
     *
     * ## OPTIONS
     *
     * <language-code>
     * : The language code (slug) to get the post count for. Required.
     *
     * [--<field>=<value>]
     * : One or more args to pass to WP_Query.
     *
     * [--field=<field>]
     * : Prints the value of a single field for each post.
     *
     * [--fields=<fields>]
     * : Limit the output to specific object fields.
     *
     * [--format=<format>]
     * : Render output in a particular format.
     * ---
     * default: table
     * options:
     *   - table
     *   - csv
     *   - ids
     *   - json
     *   - count
     *   - yaml
     * ---
     *
     * ## AVAILABLE FIELDS
     *
     * These fields will be displayed by default for each post:
     *
     * * ID
     * * post_title
     * * post_name
     * * post_date
     * * post_status
     *
     * These fields are optionally available:
     *
     * * post_author
     * * post_date_gmt
     * * post_content
     * * post_excerpt
     * * comment_status
     * * ping_status
     * * post_password
     * * to_ping
     * * pinged
     * * post_modified
     * * post_modified_gmt
     * * post_content_filtered
     * * post_parent
     * * guid
     * * menu_order
     * * post_type
     * * post_mime_type
     * * comment_count
     * * filter
     * * url
     *
     * ## EXAMPLES
     *
     *     wp pll post list nl
     *
     *     # List post
     *     $ wp pll post list es --field=ID
     *     568
     *     829
     *     1329
     *     1695
     *
     *     # List posts in JSON
     *     $ wp pll post list en-gb --post_type=post --posts_per_page=5 --format=json
     *     [{"ID":1,"post_title":"Hello world!","post_name":"hello-world","post_date":"2015-06-20 09:00:10","post_status":"publish"},{"ID":1178,"post_title":"Markup: HTML Tags and Formatting","post_name":"markup-html-tags-and-formatting","post_date":"2013-01-11 20:22:19","post_status":"draft"}]
     *
     *     # List all pages
     *     $ wp pll post list nl --post_type=page --fields=post_title,post_status
     *     +-------------+-------------+
     *     | post_title  | post_status |
     *     +-------------+-------------+
     *     | Sample Page | publish     |
     *     +-------------+-------------+
     *
     *     # List ids of all pages and posts
     *     $ wp pll post list es --post_type=page,post --format=ids
     *     15 25 34 37 198
     *
     *     # List given posts
     *     $ wp pll post list nl --post__in=1,3
     *     +----+--------------+-------------+---------------------+-------------+
     *     | ID | post_title   | post_name   | post_date           | post_status |
     *     +----+--------------+-------------+---------------------+-------------+
     *     | 1  | Hello world! | hello-world | 2016-06-01 14:31:12 | publish     |
     *     +----+--------------+-------------+---------------------+-------------+
     *
     * @subcommand list
     */
    public function list_( $args, $assoc_args ) {

        $assoc_args['lang'] = $args[0];

        $this->cli->command( array( 'post', 'list' ), $assoc_args );
    }

	/**
     * Generate some posts and their translations.
     *
     * Creates a specified number of sets of new posts with dummy data.
     *
     * ## OPTIONS
     *
     * [--count=<number>]
     * : How many posts to generate?
     * ---
     * default: 5
     * ---
     *
     * [--post_type=<type>]
     * : The type of the generated posts.
     * ---
     * default: post
     * ---
     *
     * [--post_status=<status>]
     * : The status of the generated posts.
     * ---
     * default: publish
     * ---
     *
     * [--post_author=<login>]
     * : The author of the generated posts.
     * ---
     * default:
     * ---
     *
     * [--post_date=<yyyy-mm-dd>]
     * : The date of the generated posts. Default: current date
     *
     * [--post_content]
     * : If set, the command reads the post_content from STDIN.
     *
     * [--max_depth=<number>]
     * : For hierarchical post types, generate child posts down to a certain depth.
     * ---
     * default: 1
     * ---
     *
     * [--format=<format>]
     * : Render output in a particular format.
     * ---
     * default: ids
     * options:
     *   - progress
     *   - ids
     * ---
     *
     * ## EXAMPLES
     *
     *     # Generate posts.
     *     $ wp pll post generate --count=10 --post_type=page --post_date=1999-01-04
     *     Generating posts  100% [================================================] 0:01 / 0:04
     *
     *     # Generate posts with fetched content.
     *     $ curl http://loripsum.net/api/5 | wp pll post generate --post_content --count=10
     *       % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
     *                                      Dload  Upload   Total   Spent    Left  Speed
     *     100  2509  100  2509    0     0    616      0  0:00:04  0:00:04 --:--:--   616
     *     Generating posts  100% [================================================] 0:01 / 0:04
     *
     *     # Add meta to every generated posts.
     *     $ wp pll post generate --format=ids | xargs -d ' ' -I % wp post meta add % foo bar
     *     Success: Added custom field.
     *     Success: Added custom field.
     *     Success: Added custom field.
     */
    public function generate( $args, $assoc_args ) {

        $languages = $this->api->languages_list();
        $default_language = $this->api->default_language();

        if ( ! $this->api->is_translated_post_type( $this->cli->flag( $assoc_args, 'post_type' ) ) ) {

            $this->cli->error( 'Polylang does not manage languages and translations for this post type.' );
        }

        $assoc_args['count'] = isset( $assoc_args['count'] ) ? intval( $assoc_args['count'] ) : 3;
        $assoc_args['count'] = count( $languages ) * $assoc_args['count'];

        ob_start();

        $this->cli->command( array( 'post', 'generate' ), $assoc_args );

        $ids = ob_get_clean();

        $ids = array_chunk( explode( ' ', $ids ), count( $languages ) );

        foreach ( $ids as $i => $chunk ) {

            $ids[$i] = array_combine( $languages, $chunk );

            foreach ( $ids[$i] as $lang => $post_id ) {

                $this->api->set_post_language( $post_id, $lang );
            }

            $this->api->save_post_translations( $ids[$i] );
        }

        $this->cli->success( sprintf( 'Generated %d posts.', $assoc_args['count'] ) );
    }

}

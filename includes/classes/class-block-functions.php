<?php
namespace LSX\Blocks\Classes;

/**
 * All the functions that run on the frontend and the rendering of the blocks.
 *
 * @package   LSX
 * @author    LightSpeed
 * @license   GPL3
 * @link
 * @copyright 2023 LightSpeed
 */
class Block_Functions {

	/**
	 * Contructor
	 */
	public function __construct() {
	}

	/**
	 * Initiate our class.
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'pre_render_block', array( $this, 'pre_render_related_block' ), 10, 2 );
		add_filter( 'pre_render_block', array( $this, 'pre_render_featured_block' ), 10, 2 );
		add_action( 'init', array( $this, 'register_block_loginout' ) );
	}

	/**
	 * A function to detect variation, and alter the query args.
	 * 
	 * Following the https://developer.wordpress.org/news/2022/12/building-a-book-review-grid-with-a-query-loop-block-variation/
	 *
	 * @param string|null   $pre_render   The pre-rendered content. Default null.
	 * @param array         $parsed_block The block being rendered.
	 * @param WP_Block|null $parent_block If this is a nested block, a reference to the parent block.
	 */
	public function pre_render_related_block( $pre_render, $parsed_block ) {
		// Determine if this is the custom block variation.
		if ( isset( $parsed_block['attrs']['namespace'] ) && 'lsx/lsx-related-posts' === $parsed_block['attrs']['namespace'] ) {
			add_filter(
				'query_loop_block_query_vars',
				function( $query, $block ) use ( $parsed_block ) {
	
					// Add rating meta key/value pair if queried.
					if ( 'lsx/lsx-related-posts' === $parsed_block['attrs']['namespace'] ) {
						$group     = array();
						$terms     = get_the_terms( get_the_ID(), 'category' );
				
						if ( is_array( $terms ) && ! empty( $terms ) ) {
							foreach( $terms as $term ) {
								$group[] = $term->term_id;
							}
						}
						$query['tax_query'] = array(
							array(
								'taxonomy' => 'category',
								'field'    => 'term_id',
								'terms'     => $group,
							)
						);
						$query['orderby']             = 'rand';
						$query['post__not_in']        = array( get_the_ID() );
						$query['ignore_sticky_posts'] = 1;
					}
					return $query;
				},
				10,
				2
			);
		}
	
		return $pre_render;
	}

	/**
	 * A function to detect variation, and alter the query args.
	 * 
	 * Following the https://developer.wordpress.org/news/2022/12/building-a-book-review-grid-with-a-query-loop-block-variation/
	 *
	 * @param string|null   $pre_render   The pre-rendered content. Default null.
	 * @param array         $parsed_block The block being rendered.
	 * @param WP_Block|null $parent_block If this is a nested block, a reference to the parent block.
	 */
	public function pre_render_featured_block( $pre_render, $parsed_block ) {
		// Determine if this is the custom block variation.
		if ( isset( $parsed_block['attrs']['namespace'] ) && 'lsx/lsx-featured-posts' === $parsed_block['attrs']['namespace'] ) {
			add_filter(
				'query_loop_block_query_vars',
				function( $query, $block ) use ( $parsed_block ) {
	
					// Add rating meta key/value pair if queried.
					if ( 'lsx/lsx-featured-posts' === $parsed_block['attrs']['namespace'] ) {	
						unset( $query['post__not_in'] );
						unset( $query['offset'] );
						$query['nopaging'] = false;
						
						// if its sticky posts, only include those.
						if ( 'post' === $query['post_type'] ) {
							$sticky_posts = get_option( 'sticky_posts', array() );
							if ( ! is_array( $sticky_posts ) ) {
								$sticky_posts = array( $sticky_posts );
							}
							$query['post__in'] = $sticky_posts;
							$query['ignore_sticky_posts'] = 1;
						} else {
							//Use the "featured" custom field.
							$query['meta_query'] = array(
								array(
									'key'     => 'featured',
									'compare' => 'EXISTS',
								)
							);
						}
					}
					return $query;
				},
				10,
				2
			);
		}
	
		return $pre_render;
	}

	/**
	 * Registers the `lsx/loginout` block on server.
	 */
	public function register_block_loginout() {
		register_block_type_from_metadata(
			LSX_BLOCKS_PATH . 'dist/blocks/loginout/block.json',
			array(
				'render_callback' => array( $this, 'render_block_loginout' ),
			)
		);
	}

	/**
	 * Renders the `lsx/loginout` block on server.
	 *
	 * @param array $attributes The block attributes.
	 *
	 * @return string Returns the login-out link or form.
	 */
	public function render_block_loginout( $attributes ) {
		// Build the redirect URL.
		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	
		$classes  = is_user_logged_in() ? 'logged-in' : 'logged-out';
		$classes .= ' wp-block-loginout';

		$contents = wp_loginout(
			isset( $attributes['redirectToCurrent'] ) && $attributes['redirectToCurrent'] ? $current_url : '',
			false
		);
	
		// If logged-out and displayLoginAsForm is true, show the login form.
		if ( ! is_user_logged_in() && ! empty( $attributes['displayLoginAsForm'] ) ) {
			// Add a class.
			$classes .= ' has-login-form';
	
			// Get the form.
			$contents = $this->login_form( array( 'echo' => false ) );
		}
	
		$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $classes ) );
	
		return '<div ' . $wrapper_attributes . '>' . $contents . '</div>';
	}

	/**
	 * Provides a login form for use anywhere within WordPress.
	 *
	 * The login form HTML is echoed by default. Pass a false value for `$echo` to return it instead.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args {
	 *     Optional. Array of options to control the form output. Default empty array.
	 *
	 *     @type bool   $echo           Whether to display the login form or return the form HTML code.
	 *                                  Default true (echo).
	 *     @type string $redirect       URL to redirect to. Must be absolute, as in "https://example.com/mypage/".
	 *                                  Default is to redirect back to the request URI.
	 *     @type string $form_id        ID attribute value for the form. Default 'loginform'.
	 *     @type string $label_username Label for the username or email address field. Default 'Username or Email Address'.
	 *     @type string $label_password Label for the password field. Default 'Password'.
	 *     @type string $label_remember Label for the remember field. Default 'Remember Me'.
	 *     @type string $label_log_in   Label for the submit button. Default 'Log In'.
	 *     @type string $id_username    ID attribute value for the username field. Default 'user_login'.
	 *     @type string $id_password    ID attribute value for the password field. Default 'user_pass'.
	 *     @type string $id_remember    ID attribute value for the remember field. Default 'rememberme'.
	 *     @type string $id_submit      ID attribute value for the submit button. Default 'wp-submit'.
	 *     @type bool   $remember       Whether to display the "rememberme" checkbox in the form.
	 *     @type string $value_username Default value for the username field. Default empty.
	 *     @type bool   $value_remember Whether the "Remember Me" checkbox should be checked by default.
	 *                                  Default false (unchecked).
	 *
	 * }
	 * @return void|string Void if 'echo' argument is true, login form HTML if 'echo' is false.
	 */
	function login_form( $args = array() ) {
		$defaults = array(
			'echo'           => true,
			// Default 'redirect' value takes the user back to the request URI.
			'redirect'       => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
			'form_id'        => 'loginform',
			'label_username' => __( 'Username or Email Address' ),
			'label_password' => __( 'Password' ),
			'label_remember' => __( 'Remember Me' ),
			'label_log_in'   => __( 'Log In' ),
			'id_username'    => 'user_login',
			'id_password'    => 'user_pass',
			'id_remember'    => 'rememberme',
			'id_submit'      => 'wp-submit',
			'remember'       => true,
			'value_username' => '',
			// Set 'value_remember' to true to default the "Remember me" checkbox to checked.
			'value_remember' => false,
		);

		/**
		 * Filters the default login form output arguments.
		 *
		 * @since 3.0.0
		 *
		 * @see wp_login_form()
		 *
		 * @param array $defaults An array of default login form arguments.
		 */
		$args = wp_parse_args( $args, apply_filters( 'login_form_defaults', $defaults ) );

		/**
		 * Filters content to display at the top of the login form.
		 *
		 * The filter evaluates just following the opening form tag element.
		 *
		 * @since 3.0.0
		 *
		 * @param string $content Content to display. Default empty.
		 * @param array  $args    Array of login form arguments.
		 */
		$login_form_top = apply_filters( 'login_form_top', '', $args );

		/**
		 * Filters content to display in the middle of the login form.
		 *
		 * The filter evaluates just following the location where the 'login-password'
		 * field is displayed.
		 *
		 * @since 3.0.0
		 *
		 * @param string $content Content to display. Default empty.
		 * @param array  $args    Array of login form arguments.
		 */
		$login_form_middle = apply_filters( 'login_form_middle', '', $args );

		/**
		 * Filters content to display at the bottom of the login form.
		 *
		 * The filter evaluates just preceding the closing form tag element.
		 *
		 * @since 3.0.0
		 *
		 * @param string $content Content to display. Default empty.
		 * @param array  $args    Array of login form arguments.
		 */
		$login_form_bottom = apply_filters( 'login_form_bottom', '', $args );

		$form =
			sprintf(
				'<form name="%1$s" id="%1$s" action="%2$s" method="post">',
				esc_attr( $args['form_id'] ),
				esc_url( site_url( 'wp-login.php', 'login_post' ) )
			) .
			$login_form_top .
			sprintf(
				'<p class="login-username">
					<input type="text" name="log" id="%1$s" autocomplete="username" class="input" value="%3$s" size="20" placeholder="%2$s" />
				</p>',
				esc_attr( $args['id_username'] ),
				esc_html( $args['label_username'] ),
				esc_attr( $args['value_username'] )
			) .
			sprintf(
				'<p class="login-password">
					<input type="password" name="pwd" id="%1$s" autocomplete="current-password" spellcheck="false" class="input" value="" size="20"  placeholder="%2$s"/>
				</p>',
				esc_attr( $args['id_password'] ),
				esc_html( $args['label_password'] )
			) .
			$login_form_middle .
			( $args['remember'] ?
				sprintf(
					'<p class="login-remember"><label><input name="rememberme" type="checkbox" id="%1$s" value="forever"%2$s /> %3$s</label></p>',
					esc_attr( $args['id_remember'] ),
					( $args['value_remember'] ? ' checked="checked"' : '' ),
					esc_html( $args['label_remember'] )
				) : ''
			) .
			sprintf(
				'<p class="login-submit">
					<input type="submit" name="wp-submit" id="%1$s" class="button button-primary" value="%2$s" />
					<input type="hidden" name="redirect_to" value="%3$s" />
				</p>',
				esc_attr( $args['id_submit'] ),
				esc_attr( $args['label_log_in'] ),
				esc_url( $args['redirect'] )
			) .
			$login_form_bottom .
			'</form>';

		if ( $args['echo'] ) {
			echo $form;
		} else {
			return $form;
		}
	}
}

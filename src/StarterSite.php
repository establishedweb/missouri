<?php

namespace App;

use Timber\Site;
use Timber\Timber;

/**
 * Class StarterSite
 */
class StarterSite extends Site
{
	public function __construct()
	{
		add_action('after_setup_theme', array($this, 'theme_supports'));
		add_action('init', array($this, 'register_post_types'));
		add_action('init', array($this, 'register_taxonomies'));

		add_filter('timber/context', array($this, 'add_to_context'));
		add_filter('timber/twig', array($this, 'add_to_twig'));
		add_filter('timber/twig/environment/options', [$this, 'update_twig_environment_options']);

		add_action('acf/init', array($this, 'register_acf_blocks'));

		parent::__construct();
	}

	/**
	 * This is where you can register custom post types.
	 */
	public function register_post_types()
	{
		// Register the Testimonials custom post type
		$labels = array(
			'name'                  => __('Testimonials', 'textdomain'),
			'singular_name'         => __('Testimonial', 'textdomain'),
			'menu_name'             => __('Testimonials', 'textdomain'),
			'name_admin_bar'        => __('Testimonial', 'textdomain'),
			'add_new'               => __('Add New', 'textdomain'),
			'add_new_item'          => __('Add New Testimonial', 'textdomain'),
			'new_item'              => __('New Testimonial', 'textdomain'),
			'edit_item'             => __('Edit Testimonial', 'textdomain'),
			'view_item'             => __('View Testimonial', 'textdomain'),
			'all_items'             => __('All Testimonials', 'textdomain'),
			'search_items'          => __('Search Testimonials', 'textdomain'),
			'parent_item_colon'     => __('Parent Testimonials:', 'textdomain'),
			'not_found'             => __('No testimonials found.', 'textdomain'),
			'not_found_in_trash'    => __('No testimonials found in Trash.', 'textdomain'),
			'featured_image'        => __('Testimonial Image', 'textdomain'),
			'set_featured_image'    => __('Set testimonial image', 'textdomain'),
			'remove_featured_image' => __('Remove testimonial image', 'textdomain'),
			'use_featured_image'    => __('Use as testimonial image', 'textdomain'),
			'archives'              => __('Testimonial archives', 'textdomain'),
			'insert_into_item'      => __('Insert into testimonial', 'textdomain'),
			'uploaded_to_this_item' => __('Uploaded to this testimonial', 'textdomain'),
			'filter_items_list'     => __('Filter testimonials list', 'textdomain'),
			'items_list_navigation' => __('Testimonials list navigation', 'textdomain'),
			'items_list'            => __('Testimonials list', 'textdomain'),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'has_archive'        => false,
			'rewrite'            => array('slug' => 'testimonials'),
			'supports'           => array('title', 'editor', 'thumbnail'),
			'menu_icon'          => 'dashicons-testimonial',
			'menu_position'      => 5,
			'capability_type'    => 'post',
		);

		register_post_type('testimonial', $args);
	}

	/**
	 * This is where you can register custom ACF blocks.
	 */
	public function register_acf_blocks()
	{
		if (function_exists('acf_register_block_type')) {
			acf_register_block_type(array(
				'name'              => 'testimonials-slider',
				'title'             => __('Testimonials Slider'),
				'description'       => __('A slider block to display testimonials.'),
				'render_callback'   => array($this, 'render_testimonials_block'),
				'category'          => 'widgets',
				'icon'              => 'slides',
				'keywords'          => array('testimonials', 'slider'),
				'enqueue_assets'    => array($this, 'enqueue_testimonials_slider_assets'),
				'supports'          => array(
					'align' => true,
					'mode'  => false,
					'jsx'   => true,
				),
			));
		}
	}

	/**
	 * This is the render callback for the Testimonials Slider block.
	 *
	 * @param array $block The block settings and attributes.
	 */
	public function render_testimonials_block($block, $content = '', $is_preview = false, $post_id = 0)
	{
		$context = Timber::context();

		// Block settings
		$context['block'] = $block;
		$context['is_preview'] = $is_preview;

		// Fetch testimonials
		$args = array(
			'post_type'      => 'testimonial',
			'posts_per_page' => -1,
		);
		$testimonials = Timber::get_posts($args);

		// Add thumbnail to each testimonial
		foreach ($testimonials as $testimonial) {
			$testimonial->thumbnail = $testimonial->thumbnail('full');
		}

		$context['testimonials'] = $testimonials;

		// Render the Twig template
		Timber::render('blocks/testimonials-slider.twig', $context);
	}

	/**
	 * Enqueue assets for the Testimonials Slider block.
	 */
	public function enqueue_testimonials_slider_assets()
    {
        // Enqueue SwiperJS CSS
        wp_enqueue_style(
            'swiper-css',
            'https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css',
            array(),
            '8.0.0'
        );

        // Enqueue SwiperJS JS
        wp_enqueue_script(
            'swiper-js',
            'https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js',
            array(),
            '8.0.0',
            true
        );

        // Enqueue Custom CSS
        wp_enqueue_style(
            'testimonials-slider-css',
            get_template_directory_uri() . '/assets/styles/testimonials-slider.css',
            array(),
            '1.0.0'
        );
    }


	/**
	 * This is where you can register custom taxonomies.
	 */
	public function register_taxonomies() {}

	/**
	 * This is where you add some context
	 *
	 * @param string $context context['this'] Being the Twig's {{ this }}.
	 */
	public function add_to_context($context)
	{
		$context['foo']   = 'bar';
		$context['stuff'] = 'I am a value set in your functions.php file';
		$context['notes'] = 'These values are available everytime you call Timber::context();';
		$context['menu']  = Timber::get_menu();
		$context['site']  = $this;

		return $context;
	}

	public function theme_supports()
	{
		// Add default posts and comments RSS feed links to head.
		add_theme_support('automatic-feed-links');

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support('title-tag');

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support('post-thumbnails');

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			)
		);

		/*
		 * Enable support for Post Formats.
		 *
		 * See: https://codex.wordpress.org/Post_Formats
		 */
		add_theme_support(
			'post-formats',
			array(
				'aside',
				'image',
				'video',
				'quote',
				'link',
				'gallery',
				'audio',
			)
		);

		add_theme_support('menus');
	}

	/**
	 * This would return 'foo bar!'.
	 *
	 * @param string $text being 'foo', then returned 'foo bar!'.
	 */
	public function myfoo($text)
	{
		$text .= ' bar!';
		return $text;
	}

	/**
	 * This is where you can add your own functions to twig.
	 *
	 * @param Twig\Environment $twig get extension.
	 */
	public function add_to_twig($twig)
	{
		/**
		 * Required when you want to use Twig’s template_from_string.
		 * @link https://twig.symfony.com/doc/3.x/functions/template_from_string.html
		 */
		// $twig->addExtension( new Twig\Extension\StringLoaderExtension() );

		$twig->addFilter(new \Twig\TwigFilter('myfoo', [$this, 'myfoo']));

		return $twig;
	}

	/**
	 * Updates Twig environment options.
	 *
	 * @link https://twig.symfony.com/doc/2.x/api.html#environment-options
	 *
	 * @param array $options An array of environment options.
	 *
	 * @return array
	 */
	function update_twig_environment_options($options)
	{
		// $options['autoescape'] = true;

		return $options;
	}
}

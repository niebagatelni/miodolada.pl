<?php

// ---------------------------------------
// Ustawienia w motywie

function adm_customizer_blog($wp_customize) {
    // Dodanie nowej sekcji "Ustawienia bloga"
    $wp_customize->add_section('adm_blog_settings', [
        'title'       => __('Blog', 'your-text-domain'),
        'description' => __('Zarządzaj ustawieniami wyświetlania bloga', 'your-text-domain'),
        'priority'    => 1001,
    ]);

    // Ustawienia dla układu bloga
    $wp_customize->add_setting('adm__blog_layout', [
        'default'   => 'grid',
        'transport' => 'refresh',
    ]);

    $wp_customize->add_control('adm__blog_layout_control', [
        'label'    => __('Układ bloga', 'your-text-domain'),
        'section'  => 'adm_blog_settings',
        'settings' => 'adm__blog_layout',
        'type'     => 'radio',
        'choices'  => [
            'grid'  => __('Siatka (grid)', 'your-text-domain'),
            'cards' => __('Karty (cards)', 'your-text-domain'),
        ],
    ]);

	
    // Ustawienia dla obrazka wyróżniającego
    $wp_customize->add_setting('adm__blog_featured_image', [
        'default'   => 'true',
        'transport' => 'refresh', // Dynamiczne odświeżanie
    ]);

    $wp_customize->add_control('adm__blog_featured_image_control', [
        'label'    => __('Obrazek wyróżniający', 'your-text-domain'),
        'section'  => 'adm_blog_settings', // Dedykowana sekcja
        'settings' => 'adm__blog_featured_image',
        'type'     => 'radio',
        'choices'  => [
            'true'  => __('Pokaż', 'your-text-domain'),
            'false' => __('Ukryj', 'your-text-domain'),
        ],
    ]);
	
	$wp_customize->add_setting('adm__show_facebook_link', [
        'default'   => 'true',
        'transport' => 'refresh',
    ]);
	$wp_customize->add_control('adm__show_facebook_link_control', [
        'label'    => __('Pokaż Link FB', 'your-text-domain'),
        'section'  => 'adm_blog_settings',
        'settings' => 'adm__show_facebook_link',
        'type'     => 'checkbox',
    ]);
	
    $wp_customize->add_setting('adm__show_date', [
        'default'   => 'false',
        'transport' => 'refresh',
    ]);

    $wp_customize->add_control('adm__show_date_control', [
        'label'    => __('Pokaż datę', 'your-text-domain'),
        'section'  => 'adm_blog_settings',
        'settings' => 'adm__show_date',
        'type'     => 'checkbox',
    ]);	
	
}
add_action('customize_register', 'adm_customizer_blog');



// ---------------------------------------
// wygląd postów i listy postów

	add_filter( 'excerpt_length', function() { return 50; }, 999 );

	add_action( 'init', function() {
		remove_action( 'storefront_loop_post', 'storefront_post_taxonomy', 40 );
		remove_action( 'storefront_single_post_bottom', 'storefront_post_taxonomy', 5 );
		remove_action( 'storefront_single_post', 'storefront_post_taxonomy', 5 );
		remove_action( 'storefront_single_post_bottom', 'storefront_display_comments', 20 );
		remove_action( 'storefront_post_header_before', 'storefront_post_meta', 10 );
		remove_action( 'storefront_loop_post', 'storefront_post_header', 10 );
		remove_action( 'storefront_loop_post', 'storefront_post_content', 30 );
		remove_action('storefront_single_post', 'storefront_post_header', 10 );
		remove_action( 'storefront_post_content_before', 'storefront_post_thumbnail', 10 );		
		add_action('storefront_single_post', 'adm__single_post_content_header', 10);		
	});


	add_action( 'storefront_loop_post', function() {
			$adm__featured_image = get_theme_mod('adm__blog_featured_image', 'true') === 'true';

 
			?>
			<a href="<?php echo the_permalink(); ?>" class="absolute-link" rel="bookmark"></a>

				<?php
			
			if( isset($adm__featured_image) && $adm__featured_image === true && has_post_thumbnail() ) {
				
			echo '<div class="adm--post-loop-image">';
							the_post_thumbnail( 'large', [ 'itemprop' => 'image' ] );
			echo '</div>';
				}	?>
			
			<div class="adm--post-loop-content">
				<div class="adm--post-loop-date">
					<time datetime="<?php echo get_the_date('c'); ?>">
						<?php echo get_the_date('l, j F Y'); ?>
					</time>
				</div>
				
				<h2 class="adm--post-loop-title entry-title">
					<a href="<?php echo the_permalink(); ?>" rel="bookmark">
						<?php echo the_title(); ?>
					</a>
				</h2>		

				<div class="adm--post-loop-excerpt entry-content" itemprop="articleBody">
					<?php the_excerpt(); ?>
				</div>
			</div>

		<?php
		}, 30 );	// <-- add_action( 'storefront_loop_post



	add_action( 'wp_enqueue_scripts', function() {
		if( adm_is_blog_context() ){
			wp_enqueue_style(
				'adm--blog-layout',
				get_theme_file_uri() . '/adm-inc/css/blog-layout-'. get_theme_mod('adm__blog_layout', 'grid') .'.css',
				array(), '1.0.0', 'all'
			);
		}
	}, 23);

	


	function adm__single_post_content_header() {
	?>
		<header class="adm-header-single-post entry-header">
			<?php 

		if ( is_single() ) {	
			
			if( has_post_thumbnail() ) {
				the_post_thumbnail( 'large', [ 'class' => 'adm-post-thumbnail', 'itemprop' => 'image' ] );
			}

			the_title( '<h1 class="entry-title">', '</h1>' );

			if( get_theme_mod('adm__show_facebook_link', false) ){ ?>
				<a href="#" class="fb-share-link" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u=<?php echo get_permalink(); ?>&quote=Danie%20Dnia%20w%20Amadeo%20Zbuczyn&hashtag=%23DanieDniaAmadeo', 'facebook-share', 'width=600,height=400'); return false;">
				<img class="share-img" src="<?php echo WP_CONTENT_URL ?>/uploads/2025/03/facebook-share.png" />
				</a>

				<style>
					.fb-share-link .share-img{
						max-width:30px;
					}
					.fb-share-link{
						float: right;
						display: inline-block;
					}
					article .entry-header .entry-title{
						display: inline-block;				
					}
				</style>
			
			<?php 
			} // <-- if( get_theme_mod('adm__show_facebook_link', false) ){

			if( get_theme_mod('adm__show_date', false) ){
				echo '<time style="display: block;" datetime="'. get_the_date("c") .'">'. get_the_date('l, j F Y') .'</time>';
			}
			
		
		} else {  // <-- if(is_single() 
			
			if( has_post_thumbnail() ) {
				the_post_thumbnail( 'large', [ 'class' => 'adm-post-thumbnail', 'itemprop' => 'image' ] );
			}
			the_title( sprintf( '<h2 class="alpha entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
		}	// <-- if(is_single() 
		
		
		do_action( 'storefront_post_header_after' );

			?>
		</header>

	<?php
	} // <--  function adm__single_post_content_header
	


	

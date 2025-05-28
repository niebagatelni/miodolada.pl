<?php



require_once ADM_THEME_DIR . 'adm--site-branding.php';

add_action('init', function() {

			remove_action( 'storefront_header', 'storefront_header_container', 0 );
			remove_action( 'storefront_header', 'storefront_skip_links', 5 );
		//	remove_action( 'storefront_header', 'storefront_site_branding', 20 );
		//	remove_action( 'storefront_header', 'storefront_secondary_navigation', 30 );
			remove_action( 'storefront_header', 'storefront_header_cart', 60 );
			remove_action( 'storefront_header', 'storefront_product_search', 40 );

  		//	remove_action( 'storefront_footer', 'storefront_handheld_footer_bar', 999 );

			remove_action( 'storefront_header', 'storefront_header_container_close', 41 );
		//	remove_action( 'storefront_header', 'storefront_primary_navigation_wrapper', 42 );
		//	remove_action( 'storefront_header', 'storefront_primary_navigation', 50 );
			remove_action( 'storefront_header', 'storefront_primary_navigation_wrapper_close', 68 );

		//	remove_action( 'storefront_header', 'storefront_site_branding', 20 );
		//	add_action( 'storefront_header', 'storefront_site_branding', 20 );

	
});

// */
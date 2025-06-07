<?php




// adm--flexbox style for block core/columns

add_action( 'init', 'adm_block_styles' );
function adm_block_styles() {	
	//unregister_block_style( 'core/columns', 'wp-block-library-css' );
	
	//wp_register_style('adm_block_styles', get_stylesheet_directory_uri() . '/assets/custom-blocks/cover-block.css', false);
	//wp_enqueue_style( 'adm_block_styles', get_stylesheet_directory_uri() . '/assets/custom-blocks/cover-block.css');
	
    register_block_style(
        'core/group',
    	 array(
    	    'name'  => 'adm-flexbox-group',
			'label' => __( 'ADM Flexbox', 'textdomain' ),
    	)
    );
	
	register_block_style(
		'core/group',
    	 array(
    	    'name'  => 'adm-gridbox-group',
			'label' => __( 'ADM Gridbox', 'textdomain' ),
    	)
    );
	
}


add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style(
		'adm--flexbox',
		get_theme_file_uri() . '/adm-inc/css/adm-flexbox.css',
		array(), '1.0.0', 'all'
	);
}, 25);




// Rejestracja stylu "Slim" dla bloku Tabela
function adm__table_slim_register() {
    register_block_style(
        'core/table',
        array(
            'name'  => 'adm-table-slim',
            'label' => 'Slim',
        )
    );
}
add_action('init', 'adm__table_slim_register');

// Wstrzyknięcie czystego CSS inline do <head>
function adm__table_slim_style() {
    echo '<style name="adm--table-slim">
        .is-style-adm-table-slim {
            width: 100%;
            display: block;
        }
        .is-style-adm-table-slim > * {
            width: auto;
            display: inline-block;
        }
        .is-style-adm-table-slim td {
            padding: 0 1em !important;
        }
    </style>';
}
add_action('wp_footer', 'adm__table_slim_style', 25);


// ---------------------------------------
// adm__media_text_style
// 
function adm__media_text_style() {
    wp_register_style(
        'adm--media-text-gladki-style',
        get_stylesheet_directory_uri() . '/adm-inc/css/style-media-text-gladki.css',
        array(),
        filemtime( get_stylesheet_directory() . '/adm-inc/css/style-media-text-gladki.css' )
    );

    register_block_style(
        'core/media-text',
        array(
            'name'         => 'gladki',
            'label'        => 'Gładki',
            'style_handle' => 'media-text-gladki-style',
        )
    );
	
	
}
add_action( 'init', 'adm__media_text_style' ,25);


add_filter( 'render_block', function( $block_content, $block ) {
    if (
        'core/media-text' === $block['blockName']
        && ! empty( $block['attrs']['className'] )
        && strpos( $block['attrs']['className'], 'is-style-gladki' ) !== false
    ) {
        wp_enqueue_style( 'adm--media-text-gladki-style' );
    }
    return $block_content;
}, 10, 2 );

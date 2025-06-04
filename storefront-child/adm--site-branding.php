<?php

function storefront_site_branding() {
    $is_front = is_front_page();

    // Pobierz odpowiednie ustawienia
	$prefix = is_front_page() ? '_home' : '_other';

	$hide_logo        	= get_theme_mod('hide_logo' . $prefix, false);
	$hide_site_title  	= get_theme_mod('hide_site_title' . $prefix, false);
	$hide_tagline     	= get_theme_mod('hide_site_tagline' . $prefix, false);
	$site_header_style 	= get_theme_mod('site_header_style' . $prefix, true) ? 'flex' : 'grid';
	$description      	= get_bloginfo('description', 'display') ?: '';		
	
?>

    <div class="adm site-branding <?php echo $site_header_style; ?>"> 

            <?php if ( ! $hide_logo ) : ?>
                <div class="logo-wrap">
                    <?php the_custom_logo(); ?>
                </div>
            <?php endif; ?>

            <div class="header-text">
                <?php if ( ! $hide_site_title ) : ?>
                    <?php if ( is_front_page() && is_home() ) : ?>
                        <h1 class="site-title">
                            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
                        </h1>
                    <?php else : ?>
                        <p class="site-title">
                            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ( ! $hide_tagline && ( $description || is_customize_preview() ) ) : ?>
                    <p class="site-description"><?php echo $description; ?></p>
                <?php endif; ?>
        </div>

    </div>

<?php

} // <-- function 


<?php include_once( WPLB_PATH . '/templates/header.php' ); ?>

<div class="wrapper">
    <div class="header">
        <a href="<?php echo site_url(); ?>" title="<?php bloginfo('blogname'); ?>">
            <?php
            $logo_url = get_option( 'wplb_logo_url' );
            if ( $logo_url ) :
            ?>
            <img src="<?php echo $logo_url; ?>" alt="<?php bloginfo('blogname'); ?>" class="logo" />
            <?php else : ?>
            <?php bloginfo('blogname'); ?>
            <?php endif; ?>
        </a>
    </div>

    <?php 
    $display_location = get_option( 'wplb_sn_display_location', 'before' );
    $social_networks_template = WPLB_PATH . '/templates/pro/social-networks.php';
    if (
        $display_location == 'before'
        && file_exists($social_networks_template)
        ) {
        include_once $social_networks_template;
    }
    ?>

    <?php
    $links = new WP_Query(
        [
            'post_type' => 'wp-link-bio',
            'order' => 'ASC',
            'orderby' => 'menu_order',
            'posts_per_page' => -1, // show all links
        ]
    );
    ?>

    <section class="links">

        <ul>
            <?php while ( $links->have_posts() ) : 
                $links->the_post();

                $link_url = get_post_meta( get_the_ID(), 'wplb_link_url', true );

                $parsed_url = parse_url( $link_url );

                $url_path = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '';

                $clean_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $url_path;

                $link_url = add_query_arg(
                    [
                        'utm_medium' => 'social',
                        'utm_source' => 'wp-link-bio',
                        'utm_campaign' => $post->post_name,
                    ],
                    $clean_url
                );

                if ( isset($parsed_url['fragment']) ) {
                    $link_url .= '#' . $parsed_url['fragment'];
                }
                
                $link_animation = get_post_meta( get_the_ID(), 'wplb_link_animation', true );

                $link_class = '';
                if ( ! empty($link_animation) && $link_animation !== 'none' ) {
                    $link_class = ' class="link-animation animated" data-animation-name="' . $link_animation . '"';
                }
                
                $thumb = wp_get_attachment_image_src( get_post_thumbnail_id() );
            ?>
            <li<?php echo $link_class ?>>
                <a href="<?php echo esc_url( $link_url ); ?>" id="link-<?php the_ID(); ?>">
                    <?php if ( $thumb ) : ?>
                    <img src="<?php echo esc_attr( $thumb[0] ) ?>" width="48" alt="<?php echo esc_attr( get_the_title() ); ?>" class="link-thumb" />
                    <?php endif; ?>
                    <span class="link-title"><?php the_title(); ?></span>
                </a>
            </li>
            <?php endwhile; wp_reset_postdata(); ?>
        </ul>

    </section>

    <?php
    if ($display_location == 'after') {
        include_once( WPLB_PATH . '/templates/pro/social-networks.php' );
    }
    ?>

<?php include_once( WPLB_PATH . '/templates/footer.php' ); ?>
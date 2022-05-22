<?php 
$social_networks = get_option( 'wplb_social_networks' );
if ( count($social_networks) ) :
?>

<div class="social-media">
    <ul class="social-media__links">

        <?php 
            $social_icons = wplb_social_networks_icons();
            foreach ($social_networks as $id => $url) :
                if( !empty($url) ) :
                    $icon = $social_icons[$id];
        ?>
        
        <li class="social-media__links__item">
            <a href="<?php echo esc_url_raw( $url ); ?>"><i class="fa-brands fa-<?php echo esc_attr( $icon ); ?>"></i></a>
        </li>

        <?php 
                endif;
            endforeach; 
        ?>

    </ul>
</div>
<?php endif; ?>
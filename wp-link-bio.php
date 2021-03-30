<?php

/** 
 * Plugin Name: WP Link Bio
 * Plugin URI: https://blastmkt.com/wp-link-bio
 * Description: Add unlimited links to your Instagram bio, powered by WordPress.
 * Version: 1.4.2
 * Author: Blast Marketing
 * Author URI: https://blastmkt.com/
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-link-bio
 * Domain Path: /languages
 * {Plugin Name} is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *  
 * {Plugin Name} is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *  
 * You should have received a copy of the GNU General Public License
 * along with {Plugin Name}. If not, see {URI to Plugin License}.
 * 
 */
define( 'WPLB_PATH', plugin_dir_path( __FILE__ ) );
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( 'wplb_fs' ) ) {
    wplb_fs()->set_basename( false, __FILE__ );
} else {
    
    if ( !function_exists( 'wplb_fs' ) ) {
        // Create a helper function for easy SDK access.
        function wplb_fs()
        {
            global  $wplb_fs ;
            
            if ( !isset( $wplb_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $wplb_fs = fs_dynamic_init( array(
                    'id'              => '3932',
                    'slug'            => 'wp-link-bio',
                    'premium_slug'    => 'wp-link-bio-pro',
                    'type'            => 'plugin',
                    'public_key'      => 'pk_ccfdf6ca1145a1848f0514a626e1d',
                    'is_premium'      => false,
                    'premium_suffix'  => '(Pro)',
                    'has_addons'      => false,
                    'has_paid_plans'  => true,
                    'trial'           => array(
                    'days'               => 14,
                    'is_require_payment' => false,
                ),
                    'has_affiliation' => 'selected',
                    'menu'            => array(
                    'slug'        => 'edit.php?post_type=wp-link-bio',
                    'support'     => true,
                    'affiliation' => false,
                ),
                    'is_live'         => true,
                ) );
            }
            
            return $wplb_fs;
        }
        
        // Init Freemius.
        wplb_fs();
        // Signal that SDK was initiated.
        do_action( 'wplb_fs_loaded' );
    }
    
    function wplb_activate()
    {
        wplb_setup_post_type();
        flush_rewrite_rules();
    }
    
    register_activation_hook( __FILE__, 'wplb_activate' );
    function wplb_deactivate()
    {
        unregister_post_type( 'wp-link-bio' );
        flush_rewrite_rules();
    }
    
    register_deactivation_hook( __FILE__, 'wplb_deactivate' );
    function wplb_add_plugin_page_settings_link( $links )
    {
        $links[] = '<a href="' . admin_url( 'edit.php?post_type=wp-link-bio&page=wp-link-bio' ) . '">' . __( 'Settings', 'wp-link-bio' ) . '</a>';
        return $links;
    }
    
    add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wplb_add_plugin_page_settings_link' );
    function wplb_setup_post_type()
    {
        register_post_type( 'wp-link-bio', [
            'labels'     => [
            'menu_name'     => 'WP Link Bio',
            'name'          => __( 'Links', 'wp-link-bio' ),
            'singular_name' => __( 'Link', 'wp-link-bio' ),
            'add_new'       => _x( 'Add New Link', 'wp-link-bio' ),
            'all_items'     => __( 'All Links', 'wp-link-bio' ),
        ],
            'rewrite'    => [
            'slug' => 'link',
        ],
            'show_ui'    => true,
            'rest_base'  => 'wp-link-bio',
            'supports'   => [ 'title', 'page-attributes', 'thumbnail' ],
            'menu_icon'  => 'dashicons-admin-links',
            'capability' => 'page',
        ] );
        add_image_size(
            'wplb_square',
            500,
            500,
            true
        );
    }
    
    add_action( 'init', 'wplb_setup_post_type' );
    function wplb_admin_enqueue_scripts()
    {
        $current_screen = get_current_screen();
        if ( !$current_screen->base || $current_screen->base !== 'wp-link-bio_page_wp-link-bio' ) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'font-awesome', '//kit.fontawesome.com/59ad578af5.js' );
        wp_enqueue_script(
            'wplb-script',
            plugins_url( 'js/wp-link-bio-admin.js', __FILE__ ),
            array( 'jquery', 'wp-color-picker' ),
            '',
            true
        );
        wp_localize_script( 'wplb-script', 'wplb', [
            'plugin_url' => plugins_url( '', __FILE__ ),
        ] );
        wp_enqueue_style( 'wplb-styles', plugins_url( 'css/wp-link-bio-admin.css', __FILE__ ) );
        // Animate CSS
        wp_enqueue_style( 'animate-css', plugin_dir_url( __FILE__ ) . 'templates/css/animate.min.css' );
    }
    
    add_action( 'admin_enqueue_scripts', 'wplb_admin_enqueue_scripts' );
    is_admin() && add_action( 'pre_get_posts', 'wplb_orderby' );
    function wplb_orderby( $query )
    {
        if ( !$query->is_main_query() || 'wp-link-bio' != $query->get( 'post_type' ) ) {
            return;
        }
        $orderby = strtolower( $query->get( 'orderby' ) );
        
        if ( empty($orderby) ) {
            $query->set( 'orderby', 'menu_order' );
            $query->set( 'order', 'asc' );
        }
    
    }
    
    //add_filter( 'manage_edit-wp-link-bio_columns', 'wplb_edit_columns' );
    function wplb_edit_columns( $columns )
    {
        $columns = [
            'cb'    => $columns['cb'],
            'order' => __( 'Order' ),
            'title' => __( 'Title' ),
            'date'  => __( 'Date' ),
        ];
        return $columns;
    }
    
    // Display link order
    //add_action( 'manage_wp-link-bio_posts_custom_column', 'wplb_order_column', 10, 2 );
    function wplb_order_column( $column_name, $post_id )
    {
        
        if ( 'order' === $column_name ) {
            $menu_order = get_post_field( 'menu_order', $post_id );
            echo  $menu_order ;
        }
    
    }
    
    // Make column sortable
    //add_filter( 'manage_edit-wp-link-bio_sortable_columns', 'wplb_sortable_columns' );
    function wplb_sortable_columns( $columns )
    {
        $columns['order'] = 'menu_order';
        return $columns;
    }
    
    // Custom column width
    add_action( 'admin_head', 'wplb_order_column_width' );
    function wplb_order_column_width()
    {
        echo  '<style type="text/css">
        .column-order { width: 5em; }
        </style>' ;
    }
    
    function wplb_options_page()
    {
        add_submenu_page(
            'edit.php?post_type=wp-link-bio',
            'WP Link Bio',
            __( 'Settings', 'wp-link-bio' ),
            'manage_options',
            'wp-link-bio',
            'wplb_options_page_html'
        );
    }
    
    function wplb_register_fields( $fields, $group = null )
    {
        if ( isset( $group ) && $group != null ) {
            register_setting( 'wp-link-bio', $group );
        }
        foreach ( $fields as $field ) {
            if ( $group == null ) {
                register_setting( 'wp-link-bio', $field['id'] );
            }
            $args = [
                'class'       => 'wplb-row',
                'type'        => ( isset( $field['type'] ) ? $field['type'] : '' ),
                'label_for'   => ( isset( $field['id'] ) ? $field['id'] : '' ),
                'placeholder' => ( isset( $field['placeholder'] ) ? $field['placeholder'] : '' ),
                'group'       => ( isset( $group ) ? $group : '' ),
                'description' => ( isset( $field['description'] ) ? $field['description'] : '' ),
            ];
            if ( isset( $field['choices'] ) ) {
                $args['choices'] = $field['choices'];
            }
            if ( isset( $field['default'] ) ) {
                $args['default'] = $field['default'];
            }
            add_settings_field(
                $field['id'],
                __( $field['title'], 'wp-link-bio' ),
                ( isset( $field['callback'] ) ? $field['callback'] : '' ),
                'wp-link-bio',
                $field['section'],
                $args
            );
        }
    }
    
    function wplb_input_text_cb( $field )
    {
        
        if ( isset( $field['group'] ) ) {
            $option = get_option( $field['group'] )[$field['label_for']];
            $name = sprintf( '%s[%s]', $field['group'], $field['label_for'] );
        } else {
            $option = get_option( $field['label_for'] );
            $name = $field['label_for'];
        }
        
        echo  sprintf(
            '<input type="%s" name="%s" id="%s" placeholder="%s" value="%s" size="40" />',
            esc_attr( $field['type'] ),
            esc_attr( $name ),
            esc_attr( $field['label_for'] ),
            esc_attr( $field['placeholder'] ),
            esc_url_raw( $option )
        ) ;
        if ( isset( $field['description'] ) && !empty($field['description']) ) {
            echo  sprintf( '<p class="description" id="tagline-description">%s</p>', $field['description'] ) ;
        }
    }
    
    function wplb_select_cb( $field )
    {
        $option = get_option( $field['label_for'] );
        $choices = $field['choices'];
        echo  sprintf( '<select name="%1$s" id="%1$s">', esc_attr( $field['label_for'] ) ) ;
        foreach ( $choices as $id => $value ) {
            echo  sprintf( '<option value="%s"' . selected( $option, $id, false ) . '>%s</option>', $id, $value ) ;
        }
        echo  '</select>' ;
    }
    
    add_action( 'admin_menu', 'wplb_options_page' );
    function wplb_settings()
    {
        add_settings_section(
            'wplb_section_general_settings',
            __( 'Settings', 'wp-link-bio' ),
            false,
            //'wplb_section_general_settings_cb',
            'wp-link-bio'
        );
        register_setting( 'wp-link-bio', 'wplb_link_bio' );
        register_setting( 'wp-link-bio', 'wplb_endpoint' );
        register_setting( 'wp-link-bio', 'wplb_logo_url' );
        register_setting( 'wp-link-bio', 'wplb_body_bg_color' );
        register_setting( 'wp-link-bio', 'wplb_body_link_color' );
        $endpoint = get_option( 'wplb_endpoint' );
        if ( $endpoint ) {
            add_settings_field(
                'wplb_link_bio',
                __( 'Your Link', 'wp-link-bio' ),
                'wplb_link_bio_cb',
                'wp-link-bio',
                'wplb_section_general_settings',
                [
                'label_for' => 'wplb_link_bio',
                'class'     => 'wplb-row',
            ]
            );
        }
        add_settings_field(
            'wplb_endpoint',
            __( 'Page', 'wp-link-bio' ),
            'wplb_endpoint_cb',
            'wp-link-bio',
            'wplb_section_general_settings',
            [
            'label_for' => 'wplb_endpoint',
            'class'     => 'wplb-row',
        ]
        );
        add_settings_field(
            'wplb_logo_url',
            __( 'Photo or Logo', 'wp-link-bio' ),
            'wplb_logo_url_cb',
            'wp-link-bio',
            'wplb_section_general_settings',
            [
            'label_for' => 'wplb_logo_url',
            'class'     => 'wplb-row',
        ]
        );
        add_settings_field(
            'wplb_body_bg_color',
            __( 'Background Color', 'wp-link-bio' ),
            'wplb_body_bg_color_cb',
            'wp-link-bio',
            'wplb_section_general_settings',
            [
            'label_for'   => 'wplb_body_bg_color',
            'class'       => 'wplb-row',
            'placeholder' => '#ffffff',
        ]
        );
        add_settings_field(
            'wplb_body_link_color',
            __( 'Link Color', 'wp-link-bio' ),
            'wplb_body_link_color_cb',
            'wp-link-bio',
            'wplb_section_general_settings',
            [
            'label_for'   => 'wplb_body_link_color',
            'class'       => 'wplb-row',
            'placeholder' => '#000000',
        ]
        );
        // if ( wplb_fs()->can_use_premium_code__premium_only() ) {
        add_settings_section(
            'wplb_section_social_networks_settings',
            __( 'Social Networks', 'wp-link-bio' ),
            false,
            //'wplb_section_integration_settings_cb',
            'wp-link-bio'
        );
        $display_location = [ [
            'id'       => 'wplb_sn_display_location',
            'title'    => __( 'Display Location', 'wp-link-bio' ),
            'callback' => 'wplb_select_cb',
            'section'  => 'wplb_section_social_networks_settings',
            'choices'  => [
            'before' => __( 'Before Content', 'wp-link-bio' ),
            'after'  => __( 'After Content', 'wp-link-bio' ),
        ],
            'default'  => 'before',
        ] ];
        wplb_register_fields( $display_location );
        $social_networks = [
            [
            'id'          => 'wplb_sn_facebook',
            'title'       => __( 'Facebook', 'wp-link-bio' ),
            'callback'    => 'wplb_input_text_cb',
            'section'     => 'wplb_section_social_networks_settings',
            'placeholder' => 'https://facebook.com',
            'type'        => 'url',
        ],
            [
            'id'          => 'wplb_sn_instagram',
            'title'       => __( 'Instagram', 'wp-link-bio' ),
            'callback'    => 'wplb_input_text_cb',
            'section'     => 'wplb_section_social_networks_settings',
            'placeholder' => 'https://instagram.com',
            'type'        => 'url',
        ],
            [
            'id'          => 'wplb_sn_twitter',
            'title'       => __( 'Twitter', 'wp-link-bio' ),
            'callback'    => 'wplb_input_text_cb',
            'section'     => 'wplb_section_social_networks_settings',
            'placeholder' => 'https://twitter.com',
            'type'        => 'url',
        ],
            [
            'id'          => 'wplb_sn_youtube',
            'title'       => __( 'Youtube', 'wp-link-bio' ),
            'callback'    => 'wplb_input_text_cb',
            'section'     => 'wplb_section_social_networks_settings',
            'placeholder' => 'https://youtube.com',
            'type'        => 'url',
        ],
            [
            'id'          => 'wplb_sn_twitch',
            'title'       => __( 'Twitch', 'wp-link-bio' ),
            'callback'    => 'wplb_input_text_cb',
            'section'     => 'wplb_section_social_networks_settings',
            'placeholder' => 'https://twitch.com',
            'type'        => 'url',
        ],
            [
            'id'          => 'wplb_sn_pinterest',
            'title'       => __( 'Pinterest', 'wp-link-bio' ),
            'callback'    => 'wplb_input_text_cb',
            'section'     => 'wplb_section_social_networks_settings',
            'placeholder' => 'https://pinterest.com',
            'type'        => 'url',
        ],
            [
            'id'          => 'wplb_sn_linkedin',
            'title'       => __( 'Linkedin', 'wp-link-bio' ),
            'callback'    => 'wplb_input_text_cb',
            'section'     => 'wplb_section_social_networks_settings',
            'placeholder' => 'https://linkedin.com',
            'type'        => 'url',
        ],
            [
            'id'          => 'wplb_sn_whatsapp',
            'title'       => __( 'WhatsApp', 'wp-link-bio' ),
            'callback'    => 'wplb_input_text_cb',
            'section'     => 'wplb_section_social_networks_settings',
            'placeholder' => 'https://wa.me',
            'type'        => 'url',
            'description' => __( 'How to create WhatsApp link <a href="https://faq.whatsapp.com/en/android/26000030/" target="_blank">here</a>', 'wp-link-bio' ),
        ],
            [
            'id'          => 'wplb_sn_messenger',
            'title'       => __( 'Messenger', 'wp-link-bio' ),
            'callback'    => 'wplb_input_text_cb',
            'section'     => 'wplb_section_social_networks_settings',
            'placeholder' => 'https://m.me',
            'type'        => 'url',
        ],
            [
            'id'          => 'wplb_sn_telegram',
            'title'       => __( 'Telegram', 'wp-link-bio' ),
            'callback'    => 'wplb_input_text_cb',
            'section'     => 'wplb_section_social_networks_settings',
            'placeholder' => 'https://t.me',
            'type'        => 'url',
        ]
        ];
        wplb_register_fields( $social_networks, 'wplb_social_networks' );
        // }
        add_settings_section(
            'wplb_section_integration_settings',
            __( 'Integrations', 'wp-link-bio' ),
            false,
            //'wplb_section_integration_settings_cb',
            'wp-link-bio'
        );
        $integrations = [ [
            'id'       => 'wplb_fb_pixel_id',
            'title'    => __( 'Facebook Pixel ID', 'wp-link-bio' ),
            'callback' => 'wplb_fb_pixel_id_cb',
            'page'     => 'wp-link-bio',
            'section'  => 'wplb_section_integration_settings',
            'args'     => [
            'label_for' => 'wplb_fb_pixel_id',
            'class'     => 'wplb-row',
        ],
        ], [
            'id'       => 'wplb_ga_tracking_code',
            'title'    => __( 'Google Analytics Tracking ID', 'wp-link-bio' ),
            'callback' => 'wplb_ga_tracking_code_cb',
            'page'     => 'wp-link-bio',
            'section'  => 'wplb_section_integration_settings',
            'args'     => [
            'label_for'   => 'wplb_ga_tracking_code',
            'class'       => 'wplb-row',
            'placeholder' => 'UA-XXXXXXXXX-X',
        ],
        ], [
            'id'       => 'wplb_gtm_code',
            'title'    => __( 'Google Tag Manager Code', 'wp-link-bio' ),
            'callback' => 'wplb_gtm_code_cb',
            'page'     => 'wp-link-bio',
            'section'  => 'wplb_section_integration_settings',
            'args'     => [
            'label_for'   => 'wplb_gtm_code',
            'class'       => 'wplb-row',
            'placeholder' => 'GTM-XXXXXXX',
        ],
        ] ];
        wplb_register_fields( $integrations );
    }
    
    add_action( 'admin_init', 'wplb_settings' );
    function wplb_section_general_settings_cb( $args )
    {
        ?>
        <p id="<?php 
        echo  esc_attr( $args['id'] ) ;
        ?>"><?php 
        esc_html_e( 'Utilize os campos abaixo para configurar o plugin.', 'wp-link-bio' );
        ?></p>
        <?php 
    }
    
    function wplb_link_bio_cb( $args )
    {
        $endpoint = get_option( 'wplb_endpoint' );
        
        if ( $endpoint ) {
            ?>
        <input type="text" name="wplb_link" class="wplb-link" value="<?php 
            echo  esc_attr( site_url( $endpoint ) ) ;
            ?>" size="40" readonly />
        <a href="#" class="wplb-copy-link button"><?php 
            _e( 'Copy URL', 'wp-link-bio' );
            ?></a>
        <a href="<?php 
            echo  esc_attr( site_url( $endpoint ) ) ;
            ?>" target="_blank" class="wplb-preview-link button"><?php 
            _e( 'Preview', 'wp-link-bio' );
            ?></a>
        <p class="description" id="tagline-description"><?php 
            _e( 'This is the link to add to your profile bio.', 'wp-link-bio' );
            ?></p>
        <?php 
        }
    
    }
    
    function wplb_endpoint_cb( $args )
    {
        $endpoint = get_option( 'wplb_endpoint', 'ig' );
        $pages = get_pages();
        ?>
        <select name="wplb_endpoint" id="<?php 
        echo  esc_attr( $args['label_for'] ) ;
        ?>">
            <?php 
        foreach ( $pages as $page ) {
            ?>
            <option value="<?php 
            echo  $page->post_name ;
            ?>" <?php 
            selected( $endpoint, $page->post_name );
            ?>><?php 
            echo  $page->post_title ;
            ?></option>
            <?php 
        }
        ?>
        </select>
        <?php 
    }
    
    function wplb_template_cb( $args )
    {
        ?>
            <input type="hidden" name="wplb_template" value="link" />
            <?php 
    }
    
    function wplb_num_posts_cb( $args )
    {
        $links = get_option( 'wplb_num_posts', '10' );
        ?>
        <input type="number"  
            name="wplb_num_posts" 
            id="<?php 
        echo  esc_attr( $args['label_for'] ) ;
        ?>"
            value="<?php 
        echo  esc_attr( $links ) ;
        ?>" 
            min="-1"
            />
        <p class="description" id="tagline-description"><?php 
        _e( 'To display all posts/products, enter "-1" (without quotes).', 'wp-link-bio' );
        ?></p>
        <?php 
    }
    
    function wplb_logo_url_cb( $args )
    {
        $logo_url = get_option( 'wplb_logo_url' );
        $default_image = plugins_url( 'images/placeholder.png', __FILE__ );
        if ( empty($logo_url) ) {
            $logo_url = $default_image;
        }
        ?>
        <div class="upload">
            <img class="wplb-image" data-src="<?php 
        echo  $default_image ;
        ?>" src="<?php 
        echo  $logo_url ;
        ?>" height="100" style="border: 1px solid #ccc; margin-bottom: 20px;" />
            <div>
                <input type="hidden" name="wplb_logo_url" id="<?php 
        echo  esc_attr( $args['label_for'] ) ;
        ?>" value="<?php 
        echo  esc_attr( $logo_url ) ;
        ?>" />
                <button type="submit" class="wplb-upload-image button"><?php 
        _e( 'Upload', 'wp-link-bio' );
        ?></button>
                <button type="submit" class="wplb-remove-image button">&times;</button>
            </div>
        </div>
        
        <?php 
    }
    
    function wplb_body_bg_color_cb( $args )
    {
        $body_bg_color = get_option( 'wplb_body_bg_color' );
        ?>
        <input type="text"  
            name="wplb_body_bg_color"
            class="wplb-color-picker" 
            id="<?php 
        echo  esc_attr( $args['label_for'] ) ;
        ?>"
            value="<?php 
        echo  esc_attr( $body_bg_color ) ;
        ?>"
            minlength="4"
            maxlength="7"/>
        <?php 
    }
    
    function wplb_body_link_color_cb( $args )
    {
        $body_link_color = get_option( 'wplb_body_link_color' );
        ?>
        <input type="text"  
            name="wplb_body_link_color" 
            class="wplb-color-picker" 
            id="<?php 
        echo  esc_attr( $args['label_for'] ) ;
        ?>"
            value="<?php 
        echo  esc_attr( $body_link_color ) ;
        ?>"
            minlength="4"
            maxlength="7"/>
        <?php 
    }
    
    function wplb_show_credits_cb( $args )
    {
        $show_credits = get_option( 'wplb_show_credits', '1' );
        $checked = checked( $show_credits, true, false );
        ?>
        <input type="checkbox"  
            name="wplb_show_credits"  
            id="<?php 
        echo  esc_attr( $args['label_for'] ) ;
        ?>"
            value="1" <?php 
        echo  $checked ;
        ?> />
        <?php 
    }
    
    function wplb_sn_facebook_cb( $args )
    {
        $facebook = get_option( 'wplb_sn_facebook' );
        ?>
        <input type="text"  
            name="wplb_sn_facebook" 
            id="<?php 
        echo  esc_attr( $args['label_for'] ) ;
        ?>"
            placeholder="<?php 
        echo  esc_attr( $args['placeholder'] ) ;
        ?>"
            value="<?php 
        echo  esc_attr( $facebook ) ;
        ?>" />
        <?php 
    }
    
    function wplb_fb_pixel_id_cb( $args )
    {
        // UA-134626752-1
        $fb_pixel_id = get_option( 'wplb_fb_pixel_id' );
        ?>
        <input type="text"  
            name="wplb_fb_pixel_id" 
            id="<?php 
        echo  esc_attr( $args['label_for'] ) ;
        ?>"
            placeholder="<?php 
        echo  esc_attr( $args['placeholder'] ) ;
        ?>"
            value="<?php 
        echo  esc_attr( $fb_pixel_id ) ;
        ?>" />
        <?php 
    }
    
    function wplb_ga_tracking_code_cb( $args )
    {
        // UA-134626752-1
        $ga_tracking_code = get_option( 'wplb_ga_tracking_code' );
        ?>
        <input type="text"  
            name="wplb_ga_tracking_code" 
            id="<?php 
        echo  esc_attr( $args['label_for'] ) ;
        ?>"
            placeholder="<?php 
        echo  esc_attr( $args['placeholder'] ) ;
        ?>"
            value="<?php 
        echo  esc_attr( $ga_tracking_code ) ;
        ?>" />
        <?php 
    }
    
    function wplb_gtm_code_cb( $args )
    {
        // GTM-N9VP4CK
        $gtm_code = get_option( 'wplb_gtm_code' );
        ?>
        <input type="text"  
            name="wplb_gtm_code" 
            id="<?php 
        echo  esc_attr( $args['label_for'] ) ;
        ?>"
            placeholder="<?php 
        echo  esc_attr( $args['placeholder'] ) ;
        ?>"
            value="<?php 
        echo  esc_attr( $gtm_code ) ;
        ?>" />
        <?php 
    }
    
    function wplb_options_page_html()
    {
        $screen = get_current_screen();
        if ( is_admin() && $screen->id !== 'wp-link-bio_page_wp-link-bio' ) {
            return;
        }
        if ( is_admin() && !current_user_can( 'manage_options' ) ) {
            return;
        }
        if ( isset( $_GET['settings-updated'] ) ) {
            add_settings_error(
                'wplb_messages',
                'wplb_message',
                __( 'Settings updated!', 'wp-link-bio' ),
                'updated'
            );
        }
        settings_errors( 'wplb_messages' );
        $logo_url = get_option( 'wplb_logo_url' );
        $template = get_option( 'wplb_template', 'link' );
        ?>
        <div class="wrap">
            <h1><?php 
        esc_html_e( get_admin_page_title() );
        ?></h1>
            <form class="wplb-form" action="options.php" method="post">
                <div class="col-1">
                <?php 
        settings_fields( 'wp-link-bio' );
        do_settings_sections( 'wp-link-bio' );
        submit_button( __( 'Update Settings', 'wp-link-bio' ) );
        ?>
                </div>
                <div class="col-2">
                    <div class="phone-emulator">
                        <div class="phone-screen">
                            <div class="screen-header">
                                <img src="<?php 
        echo  esc_attr( $logo_url ) ;
        ?>" height="30" class="logo" />
                            </div>

                            <div class="social-media display-location-before-content">
                                <ul class="social-media__links">
                                    <li class="social-media__links__item">
                                        <i class="fab fa-facebook-f" aria-hidden="true"></i>
                                    </li>
                                    <li class="social-media__links__item">
                                        <i class="fab fa-instagram" aria-hidden="true"></i>
                                    </li>   
                                    <li class="social-media__links__item">
                                        <i class="fab fa-twitter" aria-hidden="true"></i>
                                    </li>   
                                    <li class="social-media__links__item">
                                        <i class="fab fa-youtube" aria-hidden="true"></i>
                                    </li>  
                                    <li class="social-media__links__item">
                                        <i class="fab fa-linkedin-in" aria-hidden="true"></i>
                                    </li>  
                                    <li class="social-media__links__item">
                                        <i class="fab fa-whatsapp" aria-hidden="true"></i>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="screen-content">

                                <?php 
        
        if ( $template == 'link' ) {
            ?>

                                <ul>
                                    <li class="links">Link 1</li>
                                    <li class="links">Link 2</li>
                                    <li class="links">Link 3</li>
                                    <li class="links">Link 4</li>
                                    <li class="links">Link 5</li>
                                </ul>

                                <?php 
        } elseif ( $template == 'post' ) {
            ?>

                                <div class="posts">
                                    <div class="post-item">
                                        <img src="<?php 
            echo  plugins_url( '/images/thumb-01.jpg', __FILE__ ) ;
            ?>" />
                                    </div>
                                    <div class="post-item">
                                        <img src="<?php 
            echo  plugins_url( '/images/thumb-02.jpg', __FILE__ ) ;
            ?>" />
                                    </div>
                                    <div class="post-item">
                                        <img src="<?php 
            echo  plugins_url( '/images/thumb-03.jpg', __FILE__ ) ;
            ?>" />
                                    </div>
                                    <div class="post-item">
                                        <img src="<?php 
            echo  plugins_url( '/images/thumb-04.jpg', __FILE__ ) ;
            ?>" />
                                    </div>
                                </div>

                                <?php 
        } elseif ( $template == 'product' ) {
            ?>

                                <div class="posts">
                                    <div class="post-item">
                                        <img src="<?php 
            echo  plugins_url( '/images/product-01.jpg', __FILE__ ) ;
            ?>" />
                                    </div>
                                    <div class="post-item">
                                        <img src="<?php 
            echo  plugins_url( '/images/product-02.jpg', __FILE__ ) ;
            ?>" />
                                    </div>
                                    <div class="post-item">
                                        <img src="<?php 
            echo  plugins_url( '/images/product-03.jpg', __FILE__ ) ;
            ?>" />
                                    </div>
                                    <div class="post-item">
                                        <img src="<?php 
            echo  plugins_url( '/images/product-04.jpg', __FILE__ ) ;
            ?>" />
                                    </div>
                                </div>

                                <?php 
        }
        
        ?>
                                
                            </div>

                            <div class="social-media display-location-after-content">
                                <ul class="social-media__links">
                                    <li class="social-media__links__item">
                                        <i class="fab fa-facebook-f" aria-hidden="true"></i>
                                    </li>
                                    <li class="social-media__links__item">
                                        <i class="fab fa-instagram" aria-hidden="true"></i>
                                    </li>   
                                    <li class="social-media__links__item">
                                        <i class="fab fa-twitter" aria-hidden="true"></i>
                                    </li>   
                                    <li class="social-media__links__item">
                                        <i class="fab fa-youtube" aria-hidden="true"></i>
                                    </li>  
                                    <li class="social-media__links__item">
                                        <i class="fab fa-linkedin-in" aria-hidden="true"></i>
                                    </li>  
                                    <li class="social-media__links__item">
                                        <i class="fab fa-whatsapp" aria-hidden="true"></i>
                                    </li>
                                </ul>
                            </div>

                            <?php 
        $show_credits = get_option( 'wplb_show_credits' );
        $screen_footer_style = ( $show_credits != 1 ? 'style="display: none"' : '' );
        ?>
                            <div class="screen-footer" <?php 
        echo  $screen_footer_style ;
        ?>>Powered by WP Link Bio</div>

                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php 
    }
    
    function wplb_get_link_url( $value )
    {
        global  $post ;
        $link_url = get_post_meta( $post->ID, $value, true );
        if ( !empty($link_url) ) {
            return ( is_array( $link_url ) ? stripslashes_deep( $link_url ) : stripslashes( wp_kses_decode_entities( $link_url ) ) );
        }
        return false;
    }
    
    function wplb_register_link_url_metabox()
    {
        add_meta_box(
            'wplb-meta-box-for-links',
            __( 'Link Attributes', 'wp-link-bio' ),
            'wplb_meta_box_output_for_link_url',
            'wp-link-bio',
            'normal'
        );
        //show in custom post
    }
    
    add_action( 'add_meta_boxes', 'wplb_register_link_url_metabox' );
    // Metabox Output
    function wplb_meta_box_output_for_link_url( $post )
    {
        // create a wp nonce field
        wp_nonce_field( 'wplb_nonce_links', 'my_nonce_links' );
        $link_animation = get_post_meta( $post->ID, 'wplb_link_animation', true );
        $animations = [
            'none',
            'bounce',
            'flash',
            'pulse',
            'rubberBand',
            'shake',
            'swing',
            'tada',
            'wobble',
            'jello',
            'heartBeat'
        ];
        ?>
        
        <p>
            <label for="wplb-link-url"><?php 
        _e( 'Link URL', 'wp-link-bio' );
        ?>:</label>
            <input type="url" name="wplb_link_url" id="wplb-link-url" value="<?php 
        echo  wplb_get_link_url( 'wplb_link_url' ) ;
        ?>" size="100" />
        </p>
        
        <p>
            <label for="wplb-link-animation"><?php 
        _e( 'Animation', 'wp-link-bio' );
        ?>:</label><br />

            <?php 
        ?>
            
            <input type="checkbox" name="wplb_link_animation" id="wplb-link-animation" value="shake" <?php 
        checked( $link_animation, 'shake' );
        ?> /> <?php 
        _e( 'Yes', 'wp-link-bio' );
        ?>
            
            <?php 
        ?>
            
        </p>
        
        <?php 
    }
    
    // Save the Metabox values with Post ID
    function wplb_save_metabox_link_url( $post_id )
    {
        // Stop the script when doing autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        // Verify the nonce. If is not there, stop the script
        if ( !isset( $_POST['my_nonce_links'] ) || !wp_verify_nonce( $_POST['my_nonce_links'], 'wplb_nonce_links' ) ) {
            return;
        }
        // Stop the script if the user does not have edit permissions
        if ( !current_user_can( 'edit_post' ) ) {
            return;
        }
        // Save the Link URL field
        if ( isset( $_POST['wplb_link_url'] ) ) {
            update_post_meta( $post_id, 'wplb_link_url', sanitize_text_field( $_POST['wplb_link_url'] ) );
        }
        // Save the Link Animate field
        $link_animation = ( isset( $_POST['wplb_link_animation'] ) ? $_POST['wplb_link_animation'] : 'none' );
        if ( $link_animation ) {
            update_post_meta( $post_id, 'wplb_link_animation', sanitize_text_field( $link_animation ) );
        }
    }
    
    add_action( 'save_post', 'wplb_save_metabox_link_url' );
    function wplb_is_endpoint()
    {
        global  $wp_query ;
        $wplb_endpoint = get_option( 'wplb_endpoint' );
        if ( isset( $wp_query->query_vars['name'] ) ) {
            return $wplb_endpoint === $wp_query->query_vars['name'];
        }
        return false;
    }
    
    function wplb_template_include( $template )
    {
        if ( !wplb_is_endpoint() ) {
            return $template;
        }
        $wplb_template = get_option( 'wplb_template', 'link' );
        if ( $wplb_template === 'link' ) {
            $template = plugin_dir_path( __FILE__ ) . 'templates/template-links.php';
        }
        return $template;
    }
    
    add_filter( 'template_include', 'wplb_template_include' );
    function wplb_front_scripts()
    {
        if ( !wplb_is_endpoint() ) {
            return;
        }
        wp_enqueue_script( 'font-awesome', '//kit.fontawesome.com/59ad578af5.js' );
        wp_enqueue_style( 'google-fonts', '//fonts.googleapis.com/css?family=Roboto:400,700' );
        wp_enqueue_script(
            'wp-link-bio-front-js',
            plugin_dir_url( __FILE__ ) . 'templates/js/wp-link-bio-front.js',
            [],
            false,
            true
        );
        wp_enqueue_style( 'wp-link-bio-front-css', plugin_dir_url( __FILE__ ) . 'templates/css/wp-link-bio-front.css' );
        $body_bg_color = get_option( 'wplb_body_bg_color', '#fff' );
        $custom_style = "body { \n            background-color: {$body_bg_color}; \n        }";
        if ( $link_color = get_option( 'wplb_body_link_color' ) ) {
            $custom_style .= ".wrapper .links a,\n            .wrapper .social-media__links .social-media__links__item a { \n                border: 2px solid {$link_color}; \n                background-color: {$link_color};\n            }\n            .wrapper .links a:hover,\n            .wrapper .links a:focus,\n            .wrapper .social-media__links .social-media__links__item a:hover,\n            .wrapper .social-media__links .social-media__links__item a:focus {\n                background-color: transparent;\n                color: {$link_color};\n            }";
        }
        wp_add_inline_style( 'wp-link-bio-front-css', $custom_style );
        // Animate CSS
        wp_enqueue_style( 'animate-css', plugin_dir_url( __FILE__ ) . 'templates/css/animate.min.css' );
    }
    
    add_action( 'wp_enqueue_scripts', 'wplb_front_scripts' );
    function wplb_register_scripts()
    {
        return [ 'font-awesome', 'wp-link-bio-front-js' ];
    }
    
    function wplb_register_styles()
    {
        return [ 'google-fonts', 'wp-link-bio-front-css', 'animate-css' ];
    }
    
    function wplb_remove_all_scripts()
    {
        global  $wp_scripts ;
        $wp_scripts->queue = wplb_register_scripts();
    }
    
    function wplb_remove_all_styles()
    {
        global  $wp_styles ;
        $wp_styles->queue = wplb_register_styles();
    }
    
    // Clear head and remove admin toolbar
    // source: https://crunchify.com/how-to-clean-up-wordpress-header-section-without-any-plugin/
    function wplb_clear_head()
    {
        
        if ( true === wplb_is_endpoint() ) {
            remove_action( 'wp_head', 'rsd_link' );
            remove_action( 'wp_head', 'wlwmanifest_link' );
            remove_action( 'wp_head', 'wp_shortlink_wp_head' );
            remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
            remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );
            remove_action(
                'template_redirect',
                'rest_output_link_header',
                11,
                0
            );
            add_filter( 'the_generator', function () {
                return '';
            } );
            add_filter( 'show_admin_bar', '__return_false' );
            add_action( 'wp_print_scripts', 'wplb_remove_all_scripts', 100 );
            add_action( 'wp_print_styles', 'wplb_remove_all_styles', 100 );
        }
    
    }
    
    add_action( 'wp', 'wplb_clear_head' );
    function wplb_social_networks_icons()
    {
        return [
            'wplb_sn_facebook'  => 'facebook-f',
            'wplb_sn_instagram' => 'instagram',
            'wplb_sn_twitter'   => 'twitter',
            'wplb_sn_youtube'   => 'youtube',
            'wplb_sn_twitch'    => 'twitch',
            'wplb_sn_pinterest' => 'pinterest',
            'wplb_sn_linkedin'  => 'linkedin-in',
            'wplb_sn_whatsapp'  => 'whatsapp',
            'wplb_sn_messenger' => 'facebook-messenger',
            'wplb_sn_telegram'  => 'telegram',
        ];
    }
    
    add_action( 'wp', 'wplb_admin_notice' );
    function wplb_admin_notice()
    {
        $screen = get_current_screen();
        if ( $screen->id !== 'edit-wp-link-bio' ) {
            return;
        }
        $check_recommended_plugins = wplb_check_recommended_plugins();
        if ( $check_recommended_plugins ) {
            add_action( 'admin_notices', 'wplb_link_ordering_notice__info' );
        }
    }
    
    function wplb_check_recommended_plugins()
    {
        $is_admin = is_admin();
        $user_can_activate_plugins = current_user_can( 'activate_plugins' );
        $plugin_slug = 'simple-page-ordering/simple-page-ordering.php';
        $is_plugin_installed = wplb_is_plugin_installed( $plugin_slug );
        $is_plugin_active = is_plugin_active( $plugin_slug );
        return $is_admin && $user_can_activate_plugins && (!$is_plugin_installed || !$is_plugin_active);
    }
    
    function wplb_link_ordering_notice__info()
    {
        $plugin_slug = 'simple-page-ordering/simple-page-ordering.php';
        $slug = 'simple-page-ordering';
        $action = '';
        
        if ( !wplb_is_plugin_installed( $plugin_slug ) ) {
            $action = 'install-plugin';
            $action_text = 'Install Now';
            $plugin_action_url = wp_nonce_url( add_query_arg( [
                'action' => $action,
                'plugin' => $slug,
            ], admin_url( 'update.php' ) ), $action . '_' . $slug );
        } elseif ( !is_plugin_active( $plugin_slug ) ) {
            $action = 'activate';
            $action_text = 'Activate Now';
            $plugin_action_url = wplb_plugin_activation_url( $plugin_slug );
        }
        
        ?>
        <div class="notice notice-warning">
            <p>To order your links using drag & drop, we strongly recommend you <strong>Simple Page Ordering</strong> plugin:
            <a href="<?php 
        echo  $plugin_action_url ;
        ?>" aria-label="More information about Simple Page Ordering" data-title="Simple Page Ordering"><?php 
        echo  $action_text ;
        ?></a>
            </p>
        </div>
        <?php 
    }
    
    function wplb_is_plugin_installed( $plugin_slug )
    {
        $installed_plugins = get_plugins();
        return array_key_exists( $plugin_slug, $installed_plugins ) || in_array( $plugin_slug, $installed_plugins, true );
    }
    
    /**
     * Generate an activation URL for a plugin like the ones found in WordPress plugin administration screen.
     *
     * @param  string $plugin A plugin-folder/plugin-main-file.php path (e.g. "my-plugin/my-plugin.php")
     *
     * @return string         The plugin activation url
     */
    function wplb_plugin_activation_url( $plugin )
    {
        // the plugin might be located in the plugin folder directly
        if ( strpos( $plugin, '/' ) ) {
            $plugin = str_replace( '/', '%2F', $plugin );
        }
        // $activateUrl = sprintf(admin_url('plugins.php?action=activate&plugin=%s&plugin_status=all&paged=1&s'), $plugin);
        $activateUrl = admin_url( 'plugins.php' );
        // change the plugin request to the plugin to pass the nonce check
        // $_REQUEST['plugin'] = $plugin;
        // $activateUrl = wp_nonce_url($activateUrl, 'activate-plugin_' . $plugin);
        return $activateUrl;
    }

}

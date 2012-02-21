<?php
/*
Plugin Name:  Google Analytics Top Content Widget
Description: Widget and shortcode to display top content according to Google Analytics. ("Google Analytics Dashboard" plugin required)
Plugin URI: http://j.ustin.co/yWTtmy
Author: Jtsternberg
Author URI: http://about.me/jtsternberg
Donate link: http://j.ustin.co/rYL89n
Version: 1.0
*/



require_once dirname( __FILE__ ) . '/class-tgm-plugin-activation.php';

add_action( 'tgmpa_register', 'dsgnwrks_ga_register_required_plugins' );
/**
 * Register the required plugins for The "Google Analytics Top Content" plugin.
 *
 */
function dsgnwrks_ga_register_required_plugins() {

  $plugins = array(

    array(
      'name'    => 'Google Analytics Dashboard',
      'slug'    => 'google-analytics-dashboard',
      'required'  => true,
    ),

  );

  $plugin_text_domain = 'top-google-posts';

  $widgets_url = '<a href="' . get_admin_url( '', 'widgets.php' ) . '" title="' . __( 'Setup Widget', $plugin_text_domain ) . '">' . __( 'Setup Widget', $plugin_text_domain ) . '</a>';


  $config = array(
    'domain'          => $plugin_text_domain,
    'default_path'    => '', 
    'parent_menu_slug'  => 'plugins.php',
    'parent_url_slug'   => 'plugins.php',
    'menu'            => 'install-required-plugins',
    'has_notices'       => true,
    'is_automatic'      => true,
    'message'       => '',
    'strings'         => array(
      'page_title'                            => __( 'Install Required Plugins', $plugin_text_domain ),
      'menu_title'                            => __( 'Install Plugins', $plugin_text_domain ),
      'installing'                            => __( 'Installing Plugin: %s', $plugin_text_domain ), // %1$s = plugin name
      'oops'                                  => __( 'Something went wrong with the plugin API.', $plugin_text_domain ),
      'notice_can_install_required'           => _n_noop( 'The "Google Analytics Top Content" plugin requires the following plugin: %1$s.', 'This plugin requires the following plugins: %1$s.' ), // %1$s = plugin name(s)
      'notice_can_install_recommended'      => _n_noop( 'This plugin recommends the following plugin: %1$s.', 'This plugin recommends the following plugins: %1$s.' ), // %1$s = plugin name(s)
      'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.' ), // %1$s = plugin name(s)
      'notice_can_activate_required'          => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s)
      'notice_can_activate_recommended'     => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s)
      'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.' ), // %1$s = plugin name(s)
      'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this plugin: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this plugin: %1$s.' ), // %1$s = plugin name(s)
      'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.' ), // %1$s = plugin name(s)
      'install_link'                  => _n_noop( 'Begin installing plugin', 'Begin installing plugins' ),
      'activate_link'                 => _n_noop( 'Activate installed plugin', 'Activate installed plugins' ),
      'return'                                => __( 'Return to Required Plugins Installer', $plugin_text_domain ),
      'plugin_activated'                      => __( 'Plugin activated successfully.', $plugin_text_domain ),
      'complete'                  => __( 'All plugins installed and activated successfully. %s', $plugin_text_domain ) // %1$s = dashboard link
    )
  );

  tgmpa( $plugins, $config );

}

/**
* Register Top Content widgets
*/
add_action( 'widgets_init', 'dsgnwrks_register_google_top_posts_widgets' );
function dsgnwrks_register_google_top_posts_widgets() {
  register_widget( 'dsgnwrks_google_top_posts_widgets' );
}

/**
 * Top Content widget
 */
class dsgnwrks_google_top_posts_widgets extends WP_Widget {

    //process the new widget
    function dsgnwrks_google_top_posts_widgets() {
        $widget_ops = array(
      'classname' => 'google_top_posts',
      'description' => 'Show top posts from Google Analytics'
      );
        $this->WP_Widget( 'dsgnwrks_google_top_posts_widgets', 'Google Analytics Top Content', $widget_ops );
    }

     //build the widget settings form
    function form($instance) {

        $gad_auth_token = get_option('gad_auth_token');
        if ( isset( $gad_auth_token ) && $gad_auth_token != '' && class_exists( 'GADWidgetData' ) ) {
          $one_month_ago = ( time() - ( 60 * 60 * 24 * 30 ) );
          $default_yy = date( 'Y', ( $one_month_ago ) );
          $default_mm = date( 'm', ( $one_month_ago ) );
          $default_dd = date( 'd', ( $one_month_ago ) );

          $defaults = array( 'title' => 'Top Viewed Content', 'pageviews' => 20, 'number' => 5, 'showhome' => 0, 'yy' => $default_yy, 'mm' => $default_mm, 'dd' => $default_dd );
          $instance = wp_parse_args( (array) $instance, $defaults );
          $title = $instance['title'];
          $pageviews = absint( $instance['pageviews'] );
          $number = absint( $instance['number'] );
          $yy = $instance['yy'];
          $mm = $instance['mm'];
          $dd = $instance['dd'];
          $showhome = $instance['showhome'];
          if ( $mm && $dd && $yy ) $complete = true;
          else $complete = false;

          ?>
              <p>Title: <input class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>"  type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
              <p>Show pages with at least __ number of page views: <input class="widefat" name="<?php echo $this->get_field_name( 'pageviews' ); ?>"  type="text" value="<?php echo absint( $pageviews ); ?>" /></p>
              <p>Number to Show: <input class="widefat" name="<?php echo $this->get_field_name( 'number' ); ?>"  type="text" value="<?php echo absint( $number ); ?>" /></p>

              <p>Remove home page from list: (usually "<i>yoursite.com</i>" is the highest viewed page)<br/><input id="<?php echo $this->get_field_id('showhome'); ?>" type="checkbox" name="<?php echo $this->get_field_name('showhome'); ?>" value="1" <?php checked(1, $showhome); ?>/></p>

              <p>Select the earliest date you would like analytics to pull from:</p>
              <div>
                  <?php 
                  global $wp_locale;

                  if ( $mm || $dd || $yy ) {
                      if ( $complete ) $date = '<strong>'. $wp_locale->get_month( $mm ) .' '. $dd .', '. $yy .'</strong>';
                      else $date = '<span style="color: red;">Please select full date</span>';
                  }
                  else $date = 'No date selected';
                  $date = '<p style="padding-bottom: 2px; margin-bottom: 2px;" id="timestamp"> '. $date .'</p>';

                  $month = '<select id="'. $this->get_field_name( 'mm' ) .'" name="'. $this->get_field_name( 'mm' ) .'">\n';
                  $month .= '<option value="">Month</option>';
                  for ( $i = 1; $i < 13; $i = $i +1 ) {
                      $monthnum = zeroise($i, 2);
                      $month .= "\t\t\t" . '<option value="' . $monthnum . '"';
                      if ( $i == $mm )
                          $month .= ' selected="selected"';
                      $month .= '>' . $monthnum . '-' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . "</option>\n";
                  }
                  $month .= '</select>';

                  $day = '<select style="width: 5em;" id="'. $this->get_field_name( 'dd' ) .'" name="'. $this->get_field_name( 'dd' ) .'">\n';
                  $day .= '<option value="">Day</option>';
                  for ( $i = 1; $i < 32; $i = $i +1 ) {
                      $daynum = zeroise($i, 2);
                      $day .= "\t\t\t" . '<option value="' . $daynum . '"';
                      if ( $i == $dd )
                          $day .= ' selected="selected"';
                      $day .= '>' . $daynum;
                  }
                  $day .= '</select>';

                  $year = '<select style="width: 5em;" id="'. $this->get_field_name( 'yy' ) .'" name="'. $this->get_field_name( 'yy' ) .'">\n';
                  $year .= '<option value="">Year</option>';
                  for ( $i = date( 'Y' ); $i >= 2010; $i = $i -1 ) {
                      $yearnum = zeroise($i, 4);
                      $year .= "\t\t\t" . '<option value="' . $yearnum . '"';
                      if ( $i == $yy )
                          $year .= ' selected="selected"';
                      $year .= '>' . $yearnum;
                  }
                  $year .= '</select>';

                  echo '<div class="timestamp-wrap">';
                    /* translators: 1: month input, 2: day input, 3: year input, 4: hour input, 5: minute input */
                    printf(__('%1$s %2$s %3$s %4$s'), $date, $month, $day, $year );
                  echo '</div>';
                  ?>
              </div>
          <?php

        } elseif ( isset( $gad_auth_token ) && $gad_auth_token != '' && !class_exists( 'GADWidgetData' ) ) {
            echo dsgnwrks_gtc_widget_message_one();
            echo '<style type="text/css"> #widget-'. $this->id .'-savewidget { display: none !important; } </style>';
        } elseif ( class_exists( 'GADWidgetData' ) ) {
            if ( !isset( $gad_auth_token ) || $gad_auth_token == '' ) {
                echo dsgnwrks_gtc_widget_message_two();
                echo '<style type="text/css"> #widget-'. $this->id .'-savewidget { display: none !important; } </style>';
            }
        } else {
            echo dsgnwrks_gtc_widget_message_one();
            echo '<style type="text/css"> #widget-'. $this->id .'-savewidget { display: none !important; } </style>';
        }

    }

    //save the widget settings
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = esc_attr( $new_instance['title'] );
        $instance['pageviews'] = absint( $new_instance['pageviews'] );
        $instance['number'] = absint( $new_instance['number'] );
        $instance['showhome'] = absint( $new_instance['showhome'] );
        $instance['yy'] = esc_attr( $new_instance['yy'] );
        $instance['mm'] = esc_attr( $new_instance['mm'] );
        $instance['dd'] = esc_attr( $new_instance['dd'] );

        return $instance;
    }

    //display the widget
    function widget($args, $instance) {

        extract($args);

        echo $before_widget;
        $title = apply_filters( 'widget_title', $instance['title'] );
        if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };

        echo dsgnwrks_gtc_top_content_shortcode( $instance );

        echo $after_widget;

    }

}

function dsgnwrks_gtc_widget_message_one() {
  return '<p><strong>The "Google Analytics Top Content" widget requires the plugin, <em>"Google Analytics Dashboard"</em>, to be installed and activated.</strong></p><p><a href="'. admin_url( 'plugins.php?page=install-required-plugins' ) .'" class="thickbox" title="Install Google Analytics Dashboard">Install plugin</a> | <a href="'. admin_url( 'plugins.php' ) .'" class="thickbox" title="Activate Google Analytics Dashboard">Activate plugin</a>.</p>';
}

function dsgnwrks_gtc_widget_message_two() {
  return '<p>You must first login to Google Analytics in the "Google Analytics Dashboard" settings for this widget to work.</p><p><a href="'. admin_url( 'options-general.php?page=google-analytics-dashboard/gad-admin-options.php' ) .'">Go to plugin settings</a>.</p>';
}

add_filter( 'tgmpa_complete_link_text', 'dsgnwrks_change_link_text' );
function dsgnwrks_change_link_text( $complete_link_text ) {
  return 'Go to "Google Analytics Dashboard" plugin settings';
}

add_filter( 'tgmpa_complete_link_url', 'dsgnwrks_change_link_url' );
function dsgnwrks_change_link_url( $complete_link_url ) {
  return admin_url( 'options-general.php?page=google-analytics-dashboard/gad-admin-options.php' );
}

// Writing Prompts Calendar Shortcode
add_shortcode( 'google_top_content', 'dsgnwrks_gtc_top_content_shortcode' );
function dsgnwrks_gtc_top_content_shortcode( $atts ) {

  $one_month_ago = ( time() - ( 60 * 60 * 24 * 30 ) );
  $default_yy = date( 'Y', ( $one_month_ago ) );
  $default_mm = date( 'm', ( $one_month_ago ) );
  $default_dd = date( 'd', ( $one_month_ago ) );

  $defaults = array(
    'pageviews' => 20,
    'number' => 5,
    'showhome' => 0,
    'yy' => $default_yy,
    'mm' => $default_mm,
    'dd' => $default_dd,
  );
  $atts = shortcode_atts( $defaults, $atts );

  $gad_auth_token = get_option('gad_auth_token');
  if ( isset( $gad_auth_token ) && $gad_auth_token != '' && class_exists( 'GADWidgetData' ) ) {

   
      $login = new GADWidgetData();
      $ga = new GALib( $login->auth_type, NULL, $login->oauth_token, $login->oauth_secret, $login->account_id);

      $pages = $ga->complex_report_query( 
          $atts['yy'] .'-'. $atts['mm'] .'-'. $atts['dd'], 
          date( 'Y-m-d' ), 
          array( 'ga:pagePath', 'ga:pageTitle' ), 
          array( 'ga:pageviews' ), 
          array( '-ga:pageviews' ), 
          array( 'ga:pageviews>' . $atts['pageviews'] ) 
        );
      $list = '<ol>';
      $counter = 1;
      foreach( $pages as $page ) {
        $url = $page['value'];
        if ( $url == '/' && $atts['showhome'] != '0' ) {
          continue;
        }
        $url = apply_filters( 'gtc_page_url', $url );
        $title = apply_filters( 'gtc_page_title', $page['children']['value'] );

        $list .= '<li><a href="' . $url . '">' . $title . '</a></li>';
        $counter++;
        if ( $counter > $atts['number'] ) break;
      }
      $list .= '</ol>';

  } elseif ( isset( $gad_auth_token ) && $gad_auth_token != '' && !class_exists( 'GADWidgetData' ) ) {
      $list = dsgnwrks_gtc_widget_message_one();
  } elseif ( class_exists( 'GADWidgetData' ) ) {
      if ( !isset( $gad_auth_token ) || $gad_auth_token == '' ) {
          $list = dsgnwrks_gtc_widget_message_two();
      }
  } else {
      $list = dsgnwrks_gtc_widget_message_one();
  }

  return $list;

}

?>
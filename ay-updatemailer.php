<?php
/*
Plugin Name: Ay Update mailer
Plugin URI:  http://ayctor.com
Description: Send an email each time a plugin ord WordPress is updated
Version:     0.1
Author:      Erwan Guillon
Author URI:  http://ayctor.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class AyUpdateMailer{

  public function __construct(){
    add_action('upgrader_process_complete', array($this, 'upgrade_action'), 10, 2);
  }

  public function upgrade_action($upgrader_subject, $options){
    global $wp_version;
    if($options['action'] == 'update' AND $options['type'] == 'plugin'){

      foreach($options['plugins'] as $plugin){
        $plugin_data = get_plugin_data(dirname(__FILE__) . '/../' . $plugin);

        $subject = 'Maintenance - Le plugin ' . $plugin_data['Name'] . ' vient d\'être mis à jour';
        $title = 'Plugin mis à jour';
        $message = 'Bonjour,
        <br/><br/>Le plugin ' . $plugin_data['Name'] . ' vient d\'être mis à jour vers la version ' . $plugin_data['Version'] . '.
        <br/><br/>Cordialement.
        <br/><br/>Ayctor';
        $this->send_mail($subject, $title, $message);
      }

    }
    if($options['action'] == 'update' AND $options['type'] == 'core'){

      $subject = 'Maintenance - Votre site WordPress a été mis à jour à la version ' . $wp_version;
      $title = 'Mise à jour de WordPess';
      $message = 'Bonjour,
      <br/><br/>Votre site WordPress vient d\'être mis à jour à la version ' . $wp_version . '.
      <br/><br/>Cordialement.
      <br/><br/>Ayctor';
      $this->send_mail($subject, $title, $message);
    }
  }

  public function send_mail($subject, $title, $message){
    $email = '';

    ob_start();
    include(dirname(__FILE__) . '/mail.php');
    $email = ob_get_contents();

    ob_end_clean();

    add_filter( 'wp_mail_content_type', function( $content_type ) {
      return 'text/html';
    });

    wp_mail('erwan@ayctor.com', $subject, $email);
    
    add_filter( 'wp_mail_content_type', function( $content_type ) {
      return 'text/plain';
    });
  }

}

$ayupdatemailer = new AyUpdateMailer();

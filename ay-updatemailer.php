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
    add_action('admin_menu', array($this, 'menu'));
    add_action('upgrader_process_complete', array($this, 'upgrade_action'), 10, 2);
  }

  public function menu(){
    add_options_page('Update Mailer', 'Update Mailer', 'manage_options', 'updatemailer', array($this, 'settings_page'));
  }

  public function settings_page(){
    if(isset($_POST['submit'])){
      if(isset($_POST['um_emails'])){
        update_option('um_emails', filter_input(INPUT_POST, 'um_emails', FILTER_SANITIZE_SPECIAL_CHARS));
      }
    }
    ?>
    <div class="wrap">
      <h2>Update Mailer</h2>
      <form method="post" action="options-general.php?page=updatemailer">
        <table class="form-table">
          <tr>
            <th scope="row"><label for="um_emails">Emails</label></th>
            <td>
              <input name="um_emails" type="text" id="um_emails" value="<?php echo get_option('um_emails', ''); ?>" class="regular-text">
              <p class="description">Enter the emails (comma separated) to receive every update report.</p>
            </td>
          </tr>
        </table>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
      </form>
    </div>
    <?php
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
      $title = 'Mise à jour de WordPress';
      $message = 'Bonjour,
      <br/><br/>Votre site WordPress vient d\'être mis à jour à la version ' . $wp_version . '.
      <br/><br/>Cordialement.
      <br/><br/>Ayctor';
      $this->send_mail($subject, $title, $message);
    }
  }

  public function send_mail($subject, $title, $message){
    $emails = get_option('um_emails', '');
    if($emails != ''){
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

}

$ayupdatemailer = new AyUpdateMailer();

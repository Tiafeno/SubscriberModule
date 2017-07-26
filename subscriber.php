<?php

/*
Plugin Name: FALI. Subscriber
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: Subscribe is a simple but powerful subscription plugin which supports Contact Form 7 .
Version: 1.0
Author: Tiafeno Finel
Author URI: http://falicrea.com
License: A "Slug" license name e.g. GPL2
*/
include_once plugin_dir_path(__FILE__) . '/entity/model/SubscriberModel.php';
include_once plugin_dir_path(__FILE__) . '/shortcode.php';

class FALI_subscriber
{
    public function __construct(){
        add_action('init', array($this, '_init_'));
        

        add_action('admin_menu', function () {
            $this->setMenu();
        });

        add_action('wp_loaded', function () {
            $this->action_atom_mail_save();
            $this->action_get_delete_mail();
        });

        register_activation_hook(__FILE__, array('SubscriberModel', 'install'));
        register_uninstall_hook(__FILE__, array('SubscriberModel', 'uninstall'));

    }

    public function setMenu(){
        add_menu_page('Subscriber', 'Subscriber', 'manage_options', 'subscrib',
            array($this, 'Subscribe_admin_template'), 'dashicons-admin-settings');
    }

    /*
    *
    * Ajax function nopriv getTermsCategory, return json
    *
    */
    public function getTermsCategory(){
      $ctgs = array();
      $parent_terms = get_terms('category', array( 'parent' => 0, 'orderby' => 'slug', 'hide_empty' => false ) );
      foreach ( $parent_terms as $pterm ) {
        //Get the Child terms
        $terms = get_terms( 'category', array( 'parent' => $pterm->term_id, 'orderby' => 'slug', 'hide_empty' => false ) );
        if (!$terms || is_wp_error($terms))
            $terms = array();

        foreach ( $terms as $term ) {
            $ctgs[] = $term;
        }
      }

        wp_send_json($ctgs);
    }

    public function _init_(){
        add_action('wp_ajax_getTermsCategory', array($this, 'getTermsCategory'));
        add_action('wp_ajax_nopriv_getTermsCategory', array($this, 'getTermsCategory'));

        add_action('wp_ajax_action_atom_mail_save', array($this, 'action_atom_mail_save'));
        add_action('wp_ajax_nopriv_action_atom_mail_save', array($this, 'action_atom_mail_save'));

        add_action('wp_ajax_action_ajax_check_mail_exist', array($this, 'action_ajax_check_mail_exist'));
        add_action('wp_ajax_nopriv_action_ajax_check_mail_exist', array($this, 'action_ajax_check_mail_exist'));

        //add_action('publish_post', array($this, 'action_atom_subscrib_send'));

        add_action( 'transition_post_status', function ( $new_status, $old_status, $post ){
            if( 'publish' == $new_status && 'publish' != $old_status && $post->post_type == 'post' ) {
                //DO SOMETHING IF NEW POST IN POST TYPE IS PUBLISHED
                $this->action_atom_subscrib_send($post->ID, $post);

            }
        }, 10, 3 );

        add_shortcode('atom_subscribe', array('FALI_shortcode_subscribe', 'render') );
    }

    public function action_atom_subscrib_send($post_id, $post)
    {
        global $wpdb;

        $ctgs = get_the_terms($post_id, 'category');
        if (!$ctgs || is_wp_error($ctgs)) {
            $ctgs = array();
        }
        $ctgs = array_values($ctgs);
        $all = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}atom_subscriber WHERE city_slug LIKE '%all%' ");
        if ($wpdb->num_rows):
            foreach ($all as $mail) {
                if ($mail->deleted == 1) {
                    continue;
                }
                $this->sendMail($mail->email, $post_id);
                    
            }
        endif;

        foreach ($ctgs as $ctg) {
            $SLUG = esc_sql($ctg->slug);
            $Subscribers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}atom_subscriber WHERE city_slug LIKE '%{$SLUG}%' ");
            if ($wpdb->num_rows):
                foreach ($Subscribers as $Subscriber) {
                    if ($Subscriber->deleted == 1) {
                        continue;
                    }
                    $this->sendMail($Subscriber->email, $post_id);
                        
                }
            endif;
            unset($SLUG);
        }

    }

    private function action_get_delete_mail(){
        global $wpdb;

        if(!is_admin()){
            return false;
        }
        if ((defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) ||
            (defined('DOING_AJAX') && DOING_AJAX)
        )
            return false;

        if(!isset($_GET['id'])) return false;
        if (!is_int((int)$_GET['id'])) { return false; }

        $id = (int)$_GET['id'];
        if(isset($_GET['action'])):
            switch ($_GET['action']){
                case 'delete':
                    $wpdb->delete($wpdb->prefix.'atom_subscriber',
                        array('id' => esc_sql($id)),
                        array('%d')
                      );
                    break;

                default:
                    break;
            }
        endif;

    }



    public function action_ajax_check_mail_exist(){
        if(!isset($_REQUEST['email'])) return false;

        $email = sanitize_email($_REQUEST['email']);
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}atom_subscriber WHERE email = %s AND deleted = 0",
                                  array(esc_sql($email))));
        if(!is_null($row)): echo 1; else: echo 0; endif;
        die();


    }


    public function action_atom_mail_save(){
        /* @var $_REQUEST string */
        if (!isset($_REQUEST['subscrib_key'])) {
            return false;
        }

        if ('subscrib' == esc_attr($_REQUEST['subscrib_key'])) {
            $email = sanitize_email($_REQUEST['email']);

            $city_stringify = json_decode(stripslashes($_REQUEST['city']));

            global $wpdb;
            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}atom_subscriber WHERE email = %s",
                                  array(esc_sql($email))));
            if (is_null($row)):
                $insert = $wpdb->insert($wpdb->prefix."atom_subscriber",
                  array(
                    'email' => esc_sql($email),
                    'name' => esc_sql($_REQUEST['name']),
                    'city_slug' => json_encode($city_stringify),
                    "deleted" => 0
                  ),
                  array(
                    "%s", "%s", "%s", "%d"
                  ));
                  if($insert){
                      wp_send_json(array('type' => "Insert", "message" => 'success'));  
                  } else {wp_send_json(array('type' => "Insert", "message" => 'Error')); }
                
            else:
                $update = $wpdb->update($wpdb->prefix.'atom_subscriber', array(
                    'city_slug' => json_encode($city_stringify)
                ), array(
                    'email' => esc_sql( $email )
                ));
                if($update):
                    wp_send_json(array('type' => "Update", "message" => "success"));
                else: wp_send_json(array('type' => "Update", "message" => "error"));
                endif;
            endif;
        }
    }

    public function action_atom_mail_failed(){
        //$this->action_wpcf7_mail_sent($contact_form);
        return true;
    }

    public function action_atom_before_send_mail(){
        return true;
    }

    

    private function sendMail($mail, $post_id)
    {
        if(!is_int($post_id))
            return false;

        $to = $mail;
        $post = get_post($post_id);
        $subject = $post->post_title;
        $content = apply_filters('the_content','<h1 style="text-align:center">'.$post->post_title.'</h1> <br> '.$post->post_content . ' <div> </div> <div><em>Suivre le lien: <a href="'.get_permalink($post).'">'.$post->post_title.'</a></em></div>' );
        if (wp_mail($to, $subject, $content)) {
            return true;
        }
    }

    public function Subscribe_admin_template()
    {

        global $wpdb;
        wp_enqueue_style('global-style', plugins_url('/assets/css/global.css', __FILE__), array());
        $Subscribers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}atom_subscriber ");

        ?>
        <div class="wrap theme-options-page">
            <h2> <?= get_admin_page_title() ?></h2>
        </div>
        <div class="wp-box postbox-container" style="width:512px">
            <table class="acf_input widefat">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Adress E-mail</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php
                if ($wpdb->num_rows):
                    foreach ($Subscribers as $Subscriber) {
                        if($Subscriber->deleted == 1)
                            continue;
                        ?>
                        <tr>
                            <th scope="row"><?= $Subscriber->id ?></th>
                            <td><b><?= $Subscriber->email ?></b></td>
                            <td>
                                <a href="<?= admin_url('admin.php') ?>?page=subscrib&action=delete&id=<?= $Subscriber->id ?>"
                                   class="">Supprimer</a></td>
                        </tr>

                    <?php }
                endif; ?>
                </tbody>
            </table>
        </div>

        <?php
    }


}

new FALI_subscriber();
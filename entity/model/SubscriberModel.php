
<?php

class SubscriberModel{
  public function __construct(){

  }

  public static function install(){
    global $wpdb;
    $wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}atom_subscriber"
        . "(id INT AUTO_INCREMENT PRIMARY KEY,"
        ."name VARCHAR(50) NULL DEFAULT NULL,"
        ."email VARCHAR(255) NOT NULL,"
        ."newsletter_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,"
        ."city_slug LONGTEXT NOT NULL , "
        ."deleted INT NOT NULL DEFAULT 0"
        .");");
    // add categories
    $param = array();
    $param[] = array('cat_name' => 'Madagascar','category_nicename' => 'madagascar');
    $param[] = array('cat_name' => 'Burkina Faso', 'category_nicename' => "burkina_faso");
    $param[] = array('cat_name' => 'Sénégal', 'category_nicename' => 'senegal');
    $param[] = array('cat_name' => 'Kenya', 'category_nicename' => 'kenya');
    $param[] = array('cat_name' => 'Nicaragua', 'category_nicename' => 'nicaragua');
    $param[] = array('cat_name' => 'Gambodge', 'category_nicename' => 'gambodge');
    $param[] = array('cat_name' => 'Népal', 'category_nicename' => 'nepal');
    $param[] = array('cat_name' => 'Cameroun', 'category_nicename' => 'cameroun');
	  $param[] = array('cat_name' => 'Haiti', 'category_nicename' => 'haiti');
    $param[] = array('cat_name' => 'Tous les Pays', 'category_nicename' => 'all');

    foreach ($param as $m) {
      $t = term_exists($m['category_nicename'], 'category');
      if(is_null($t)){
        wp_insert_category($m);
      }
    }


  }

  public static function uninstall(){
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}atom_subscriber;");
  }
}

 ?>

<?php

/**
 *  Classe d'administration du module
 */

class WPDoliAdmin {
	public $dolibarr ;
	public $testlabel ='';
	function __construct($dolibarr){
            $this->dolibarr = $dolibarr;
            add_action('admin_menu', array($this, 'add_admin_menu'), 20);
            add_action('admin_init', array($this, 'register_settings'));
	}
	
	public function add_admin_menu()
	{
            $hook = add_submenu_page('WPDoli', 'Intégration Dolibarr', 'Intégration à Dolibarr', 'manage_options', 'WPDoliAdmin', array($this, 'menu_html'));
            add_action('load-'.$hook, array($this, 'testwebservice_action'));
	}
	
	// Définition de la fonction d'affichage du menu
	public function menu_html()
	{
            echo '<h1>'.get_admin_page_title().'</h1>';
            ?>
            <form method="post" action="options.php">
            <?php settings_fields('wpdoli_settings') ?>
            <?php do_settings_sections('wpdoli_settings') ?>
            <?php submit_button(); ?>
            </form>	
            <form method="post" action="">
            <input type="hidden" name="wpdoli_settings_test_webservice" value="1"/>
            <?php if($this->testlabel):?>
            <span>Test de dolibarr: <?=$this->testlabel?></span>
            <?php endif;?>
            <?php submit_button('Testez le webservice') ?>
            </form>
            <?php
        }
        // Enregistrement des champs
        public function register_settings()
        {
            register_setting('wpdoli_settings', 'wpdoli_settings_url');
            register_setting('wpdoli_settings', 'wpdoli_settings_user');
            register_setting('wpdoli_settings', 'wpdoli_settings_password');
            register_setting('wpdoli_settings', 'wpdoli_settings_ws_key');
                    
            add_settings_section('wpdoli_settings_section', 'Paramètres du plugin WPDoli', array($this, 'section_html'), 'wpdoli_settings');
            add_settings_field('wpdoli_settings_url', 'Url du Web Service Dolibarr', array($this, 'url_html'), 'wpdoli_settings', 'wpdoli_settings_section');
            add_settings_field('wpdoli_settings_user', 'Web Service User', array($this, 'user_html'), 'wpdoli_settings', 'wpdoli_settings_section');
            add_settings_field('wpdoli_settings_password', 'Password', array($this, 'password_html'), 'wpdoli_settings', 'wpdoli_settings_section');
            add_settings_field('wpdoli_settings_ws_key', 'Clé du Web Service de Dolibarr', array($this, 'Webservice_key_html'), 'wpdoli_settings', 'wpdoli_settings_section');
        }

        // Définition des rendus
        public function url_html()
        {?>
            <input type="text" name="wpdoli_settings_url" value="<?php echo get_option('wpdoli_settings_url')?>"/>
            <?php
        }
        
        public function user_html()
        {?>
            <input type="text" name="wpdoli_settings_user" value="<?php echo get_option('wpdoli_settings_user')?>"/>
            <?php
        }
        
        public function password_html()
        {?>
            <input type='password' name="wpdoli_settings_password" value="<?php echo get_option('wpdoli_settings_password')?>"/>
            <?php
        }
        public function Webservice_key_html()
        {?>
            <input type="text" name="wpdoli_settings_ws_key" value="<?php echo get_option('wpdoli_settings_ws_key')?>"/>
            <?php
        }
        public function section_html()
        {
            echo 'Renseignez ici les paramètres de Dolibarr.';
        }
        public function testwebservice_action()
        {
            if (isset($_POST['wpdoli_settings_test_webservice'])) {
                $re = $this->dolibarr->test_webservice();
                //$re = $this->dolibarr->dolibarr_getProduit();
                //var_dump($re ,'ff');exit;
                //add_action('admin_init', array($this, 'test_webservice'));
                //var_dump(get_option('wpdoli_settings_url'));exit;
                //$this->send_newsletter();
                //var_dump($re);exit;
                if($re > 0) $this->testlabel = ' Web Service fonctionnel et correctement configuré.';
                else {
                    $this->testlabel .= ' <em>problème avec le Web Service</em> :<br>';
                    foreach($this->dolibarr->errors as $err) {
                        $this->testlabel .= $err;
                    }
                }
            }
        }
        public function test_webservice(){
                //echo '<div>hhhhhh</div>';
        }
}

<?php
/**
 * Plugin Name: CF7 Popup
 * Description: Displays a custom popup after Contact Form 7 submission.
 * Version: 1.0
 * Author: Melazhari
 */

if (!defined('ABSPATH')) {
    exit;
}

class CF7_Popup
{

    private $option_name = 'cf7_popup_settings';

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'render_popup_html'));
    }

    public function add_admin_menu()
    {
        add_menu_page(
            'CF7 Popups',
            'CF7 Popups',
            'manage_options',
            'cf7-popups',
            array($this, 'settings_page_html'),
            'dashicons-format-chat'
        );
    }

    public function register_settings()
    {
        register_setting('cf7_popup_group', $this->option_name);
    }

    public function settings_page_html()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $forms = get_posts(array(
            'post_type' => 'wpcf7_contact_form',
            'posts_per_page' => -1,
        ));

        $settings = get_option($this->option_name, array());

        ?>
        <div class="wrap">
            <h1>
                <?php echo esc_html(get_admin_page_title()); ?>
            </h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('cf7_popup_group');
                ?>
                <div class="cf7-popup-settings-container">
                    <?php foreach ($forms as $form):
                        $id = $form->ID;
                        $enabled = isset($settings[$id]['enabled']) ? $settings[$id]['enabled'] : '';
                        $title = isset($settings[$id]['title']) ? $settings[$id]['title'] : '';
                        $btn_text = isset($settings[$id]['btn_text']) ? $settings[$id]['btn_text'] : 'OK';
                        $success = isset($settings[$id]['success']) ? $settings[$id]['success'] : '';
                        $error = isset($settings[$id]['error']) ? $settings[$id]['error'] : '';
                        ?>
                        <div class="postbox" style="margin-bottom: 20px;">
                            <div class="postbox-header" style="padding:0 15px">
                                <h2 class="hndle ui-sortable-handle"><?php echo esc_html($form->post_title); ?> (ID:
                                    <?php echo $id; ?>)
                                </h2>
                            </div>
                            <div class="inside">
                                <p>
                                    <label>
                                        <input type="checkbox" class="cf7-toggle-settings"
                                            data-target="#settings-<?php echo $id; ?>"
                                            name="<?php echo $this->option_name; ?>[<?php echo $id; ?>][enabled]" value="1" <?php checked(1, $enabled); ?> />
                                        <strong><?php _e('Activer la Popup pour ce formulaire', 'cf7-popup'); ?></strong>
                                    </label>
                                </p>
                                <div id="settings-<?php echo $id; ?>" style="display: <?php echo $enabled ? 'block' : 'none'; ?>;">
                                    <p>
                                        <label><strong><?php _e('Titre de la popup', 'cf7-popup'); ?>:</strong></label><br>
                                        <input type="text" name="<?php echo $this->option_name; ?>[<?php echo $id; ?>][title]"
                                            value="<?php echo esc_attr($title); ?>" class="regular-text" style="width: 100%;" />
                                    </p>
                                    <p>
                                        <label><strong><?php _e('Texte du bouton', 'cf7-popup'); ?>:</strong></label><br>
                                        <input type="text" name="<?php echo $this->option_name; ?>[<?php echo $id; ?>][btn_text]"
                                            value="<?php echo esc_attr($btn_text); ?>" class="regular-text" style="width: 100%;" />
                                    </p>
                                    <p>
                                        <label><strong><?php _e('Message de succès', 'cf7-popup'); ?>:</strong></label><br>
                                        <textarea name="<?php echo $this->option_name; ?>[<?php echo $id; ?>][success]" rows="3"
                                            class="large-text" style="width: 100%;"><?php echo esc_textarea($success); ?></textarea>
                                    </p>
                                    <p>
                                        <label><strong><?php _e('Message d\'erreur', 'cf7-popup'); ?>:</strong></label><br>
                                        <textarea name="<?php echo $this->option_name; ?>[<?php echo $id; ?>][error]" rows="3"
                                            class="large-text" style="width: 100%;"><?php echo esc_textarea($error); ?></textarea>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <script>
                    jQuery(document).ready(function ($) {
                        $('.cf7-toggle-settings').change(function () {
                            var target = $(this).data('target');
                            if ($(this).is(':checked')) {
                                $(target).slideDown();
                            } else {
                                $(target).slideUp();
                            }
                        });
                    });
                </script>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function enqueue_scripts()
    {
        wp_enqueue_style('cf7-popup-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), '1.0.0');
        wp_enqueue_script('cf7-popup-script', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), '1.0.0', true);

        $settings = get_option($this->option_name, array());
        wp_localize_script('cf7-popup-script', 'cf7PopupSettings', $settings);
    }

    public function render_popup_html()
    {
        ?>
        <div id="cf7-popup-overlay" style="display:none;">
            <div id="cf7-popup-content">
                <span id="cf7-popup-close">&times;</span>
                <div class="icon-container">
                    <div class="success-icon" style="display:none;">
                        <div class="success-ring"></div>
                        <div class="success-fix"></div>
                        <div class="success-line-tip"></div>
                        <div class="success-line-long"></div>
                    </div>
                    <div class="error-icon" style="display:none;">
                        <span class="x-mark">
                            <span class="x-mark-line-left"></span>
                            <span class="x-mark-line-right"></span>
                        </span>
                    </div>
                </div>
                <h3 id="cf7-popup-title"></h3>
                <div id="cf7-popup-message"></div>
                <button id="cf7-popup-btn"></button>
            </div>
        </div>
        <?php
    }
}

new CF7_Popup();

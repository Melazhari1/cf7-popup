<?php
/**
 * Plugin Name: CF7 Popup
 * Description: Displays a custom popup after Contact Form 7 submission.
 * Version: 1.0
 * Author: Mohamed Elazhari
 * Text Domain: cf7-popup
 * Author URI: https://melazhari.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
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
        add_action("update_option_{$this->option_name}", array($this, 'register_translatable_strings'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
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
        register_setting('cf7_popup_group', $this->option_name, array(
            'sanitize_callback' => array($this, 'sanitize_settings')
        ));
    }

    public function sanitize_settings($settings)
    {
        if (!is_array($settings)) {
            return array();
        }

        $sanitized = array();
        foreach ($settings as $form_id => $form_settings) {
            if (!is_array($form_settings)) {
                continue;
            }
            $sanitized[$form_id] = array(
                'enabled' => isset($form_settings['enabled']) ? 1 : 0,
                'title' => isset($form_settings['title']) ? sanitize_text_field($form_settings['title']) : '',
                'btn_text' => isset($form_settings['btn_text']) ? sanitize_text_field($form_settings['btn_text']) : 'OK',
                'success' => isset($form_settings['success']) ? wp_kses_post($form_settings['success']) : '',
                'error' => isset($form_settings['error']) ? wp_kses_post($form_settings['error']) : ''
            );
        }
        return $sanitized;
    }

    public function register_translatable_strings($old_value, $value)
    {
        if (!is_array($value)) {
            return;
        }

        foreach ($value as $form_id => $form_settings) {
            $fields = ['title', 'btn_text', 'success', 'error'];
            foreach ($fields as $field) {
                if (!empty($form_settings[$field])) {
                    $string_name = "cf7_popup_{$form_id}_{$field}";
                    $string_value = $form_settings[$field];

                    // WPML
                    do_action('wpml_register_single_string', 'CF7 Popup', $string_name, $string_value);

                    // Polylang
                    if (function_exists('pll_register_string')) {
                        pll_register_string($string_name, $string_value, 'CF7 Popup', true);
                    }
                }
            }
        }
    }



    public function enqueue_admin_scripts($hook)
    {
        if ($hook !== 'toplevel_page_cf7-popups') {
            return;
        }
        wp_enqueue_style('cf7-popup-admin-style', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css', array(), '1.1.0');
        wp_enqueue_script('cf7-popup-admin-script', plugin_dir_url(__FILE__) . 'assets/js/admin-script.js', array('jquery'), '1.1.0', true);
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
        <div class="cf7p-wrap">

            <!-- Header Banner -->
            <div class="cf7p-header">
                <div class="cf7p-header-icon">
                    <span class="dashicons dashicons-format-chat"></span>
                </div>
                <div class="cf7p-header-text">
                    <h1><?php esc_html_e('CF7 Popup', 'cf7-popup'); ?></h1>
                    <p><?php esc_html_e('Configure custom popups for each of your Contact Form 7 forms.', 'cf7-popup'); ?>
                    </p>
                </div>
            </div>

            <form action="options.php" method="post">
                <?php settings_fields('cf7_popup_group'); ?>
                <?php wp_nonce_field('cf7_popup_save', 'cf7_popup_nonce'); ?>

                <?php if (empty($forms)): ?>
                    <div class="cf7p-empty">
                        <div class="cf7p-empty-icon">📭</div>
                        <h3><?php esc_html_e('No forms found', 'cf7-popup'); ?></h3>
                        <p><?php esc_html_e('Create a Contact Form 7 form to get started.', 'cf7-popup'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="cf7p-cards">
                        <?php foreach ($forms as $form):
                            $id = $form->ID;
                            $enabled = isset($settings[$id]['enabled']) ? $settings[$id]['enabled'] : '';
                            $title = isset($settings[$id]['title']) ? $settings[$id]['title'] : '';
                            $btn_text = isset($settings[$id]['btn_text']) ? $settings[$id]['btn_text'] : 'OK';
                            $success = isset($settings[$id]['success']) ? $settings[$id]['success'] : '';
                            $error = isset($settings[$id]['error']) ? $settings[$id]['error'] : '';
                            ?>
                            <div class="cf7p-card">
                                <div class="cf7p-card-header">
                                    <div class="cf7p-card-title-wrap">
                                        <h3 class="cf7p-card-title"><?php echo esc_html($form->post_title); ?></h3>
                                        <span class="cf7p-card-id">ID: <?php echo esc_html( $id ); ?></span>
                                        <span
                                            class="cf7p-badge <?php echo $enabled ? 'cf7p-badge--active' : 'cf7p-badge--inactive'; ?>">
                                            <?php echo $enabled ? esc_html__('Active', 'cf7-popup') : esc_html__('Inactive', 'cf7-popup'); ?>
                                        </span>
                                    </div>
                                    <label class="cf7p-toggle">
                                        <input type="checkbox" name="<?php echo esc_attr( $this->option_name . "[{$id}][enabled]" ); ?>"
                                            value="1" <?php checked(1, $enabled); ?> />
                                        <span class="cf7p-toggle-track"></span>
                                        <span class="cf7p-toggle-label"><?php esc_html_e('Popup', 'cf7-popup'); ?></span>
                                    </label>
                                </div>

                                <div class="cf7p-card-body <?php echo !$enabled ? 'collapsed' : ''; ?>">
                                    <div class="cf7p-field">
                                        <label><?php esc_html_e('Popup Title', 'cf7-popup'); ?></label>
                                        <input type="text" name="<?php echo esc_attr( $this->option_name . "[{$id}][title]" ); ?>"
                                            value="<?php echo esc_attr($title); ?>"
                                            placeholder="<?php esc_attr_e('Ex: Thank you!', 'cf7-popup'); ?>" />
                                    </div>
                                    <div class="cf7p-field">
                                        <label><?php esc_html_e('Button Text', 'cf7-popup'); ?></label>
                                        <input type="text" name="<?php echo esc_attr( $this->option_name . "[{$id}][btn_text]" ); ?>"
                                            value="<?php echo esc_attr($btn_text); ?>" placeholder="<?php esc_attr_e('OK', 'cf7-popup'); ?>" />
                                    </div>
                                    <div class="cf7p-field">
                                        <label>
                                            <?php esc_html_e('Success Message', 'cf7-popup'); ?>
                                            <span class="cf7p-field-hint">(HTML <?php esc_html_e('allowed', 'cf7-popup'); ?>)</span>
                                        </label>
                                        <textarea name="<?php echo esc_attr( $this->option_name . "[{$id}][success]" ); ?>" rows="3"
                                            placeholder="<?php esc_attr_e('Your message has been sent successfully.', 'cf7-popup'); ?>"><?php echo esc_textarea($success); ?></textarea>
                                    </div>
                                    <div class="cf7p-field">
                                        <label>
                                            <?php esc_html_e('Error Message', 'cf7-popup'); ?>
                                            <span class="cf7p-field-hint">(HTML <?php esc_html_e('allowed', 'cf7-popup'); ?>)</span>
                                        </label>
                                        <textarea name="<?php echo esc_attr( $this->option_name . "[{$id}][error]" ); ?>" rows="3"
                                            placeholder="<?php esc_attr_e('An error occurred. Please try again.', 'cf7-popup'); ?>"><?php echo esc_textarea($error); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($forms))
                    submit_button(esc_html__('Save Settings', 'cf7-popup')); ?>
            </form>
        </div>
        <?php
    }

    public function enqueue_scripts()
    {
        wp_enqueue_style('cf7-popup-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), '1.0.0');
        wp_enqueue_script('cf7-popup-script', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), '1.0.0', true);

        $settings = get_option($this->option_name, array());

        if (is_array($settings)) {
            foreach ($settings as $form_id => &$form_settings) {
                if (empty($form_settings['enabled'])) {
                    continue;
                }

                $fields = ['title', 'btn_text', 'success', 'error'];
                foreach ($fields as $field) {
                    if (!empty($form_settings[$field])) {
                        $string_name = "cf7_popup_{$form_id}_{$field}";
                        $string_value = $form_settings[$field];

                        // WPML Translation
                        $translated = apply_filters('wpml_translate_single_string', $string_value, 'CF7 Popup', $string_name);

                        // Polylang Translation (Fallback if WPML is not active/translation didn't change)
                        if (function_exists('pll__') && $translated === $string_value) {
                            $translated = pll__($string_value);
                        }

                        $form_settings[$field] = $translated;
                    }
                }
            }
        }

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

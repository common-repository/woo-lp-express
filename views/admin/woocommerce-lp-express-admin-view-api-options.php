<?php
/**
 * API options tab
 */
/**
 * Don't allow to call this file directly
 * TODO: Add authentification data test
 **/
if(!defined('ABSPATH')) {
    die;
}
?>
<?php $page_id = sanitize_text_field($_POST['id']); ?>
<?php if($page_id == 'api-options'): ?>
    <form method="post" action="options.php" id="wc-lp-form">
        <?php
            settings_fields('wc_api_options');
                do_settings_sections($page_id);
            submit_button();
        ?>
    </form>
<?php endif; ?>
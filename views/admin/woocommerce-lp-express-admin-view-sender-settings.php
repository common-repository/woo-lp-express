<?php
/**
 * Sender settings tab
 */
/**
 * Don't allow to call this file directly
 **/
if(!defined('ABSPATH')) {
    die;
}
?>
<?php $page_id = sanitize_text_field($_POST['id']); ?>
<?php if($page_id == 'sender-settings'): ?>
    <form method="post" action="options.php" id="wc-lp-form">
        <?php
            settings_fields('wc_sender_settings');
                do_settings_sections($page_id);
        ?>
        <div id="wclp-save-container">
            <?php submit_button(); ?>
            <div class="loading-bar" style="display: none;">
                <div class="md-preloader"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" height="75" width="75" viewBox="0 0 75 75"><circle cx="37.5" cy="37.5" r="33.5" stroke-width="8"></circle></svg></div>
            </div>
        </div>
    </form>
<?php endif; ?>
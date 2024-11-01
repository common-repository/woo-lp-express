<?php
/**
 * Sender info tab
 */
/**
 * Don't allow to call this file directly
 **/
if(!defined('ABSPATH')) {
    die;
}
?>
<?php $page_id = sanitize_text_field($_POST['id']); ?>
<?php if($page_id == 'sender-info'): ?>
    <form method="post" action="options.php" id="wc-lp-form">
        <?php
            settings_fields('wc_sender_info');
                do_settings_sections($page_id);
            submit_button();
        ?>
    </form>
    <form action="<?php echo admin_url( 'admin-ajax.php' ); ?>" method="post" id="wc-test-auth">
        <input type="submit" id="submit" value="<?php echo __('Testuoti duomenis','lp_express'); ?>" class="button button-primary test-auth-button" />
        <p><?php echo __('Testuojami tik tie duomenys, kurie yra išsaugoti. Nepamirškite prieš testuojant atnaujinti siuntėjo informacijos.','lp_express'); ?></p>
    </form>
<?php endif; ?>
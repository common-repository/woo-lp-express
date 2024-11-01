<?php
/**
 * Don't allow to call this file directly
 **/
if(!defined('ABSPATH')) {
    die;
}
/**
* Admin settings page view contains admin page html and little bit of php
* @package woocomerce-lp-express
* @subpackage woocomerce-lp-express/includes/templates/admin
**/
require_once WCLP_PLUGIN_DIR . '/includes/templates/admin/woocommerce-lp-express-admin-page-display.php';
?>

<h1><?php echo $title; ?></h1>
<body>
<nav class="lp-navigation nav-tab-wrapper woo-nav-tab-wrapper">
    <?php foreach($tabs as $tab):?>
        <?php if($tab->getPage() == $_GET["page"]): ?>
            <a class="nav-tab" data-id="<?php echo $tab->getID(); ?>" data-url="<?php echo $tab->getLocation(); ?>" href="#"><?php echo $tab->getName(); ?></a>
        <?php endif; ?>
   <?php endforeach; ?>
</nav>
<div class="loading-bar">
    <div class="md-preloader"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" height="75" width="75" viewbox="0 0 75 75"><circle cx="37.5" cy="37.5" r="33.5" stroke-width="8"/></svg></div>
</div>
<div class="lpe-content">
    
</div>
</body>

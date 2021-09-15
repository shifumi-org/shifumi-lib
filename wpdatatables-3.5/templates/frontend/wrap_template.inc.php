<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
/** @var string $tableContent */
/** @var WPDataTable $this */
?>
<div class="wpdt-c <?php echo 'wdt-skin-' . $this->getTableSkin()?>">
    <?php echo $tableContent; ?>
</div>
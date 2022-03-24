<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class='wdt-column-<?php echo $function ?>' data-table-id='<?php echo $attributes['table_id'] ?>'
     data-column-id='<?php echo $attributes['col_id'] ?>'
     data-column-orig-header='<?php echo $column['orig_header'] ?>'>
    <strong><?php echo $attributes['label'] ?> </strong>
    <span class='wdt-column-<?php echo $function ?>-value'><?php echo $funcResult ?></span>
</div>
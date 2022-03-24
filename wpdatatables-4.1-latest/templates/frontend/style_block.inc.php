<?php defined('ABSPATH') or die('Access denied.'); ?>
<?php /** @var WPDataTable $wpDataTable */ ?>
<style>
<?php if(!empty($wdtFontColorSettings['wdtTableFontColor'])){ ?>
/* table font color */
.wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable thead th,
.wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable tbody td,
.wpdt-c .wpDataTablesWrapper table.wpDataTable thead th,
.wpdt-c .wpDataTablesWrapper table.wpDataTable tbody td,
.wpdt-c .wpDataTablesWrapper table.wpDataTable tfoot td {
	color: <?php echo $wdtFontColorSettings['wdtTableFontColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtHeaderBaseColor'])){ ?>
/* th background color */
.wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable.bt[data-has-header='1'] td.wpdt-header-classes,
.wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable thead th,
.wpdt-c .wpDataTablesWrapper table.wpDataTable thead th,
.wpdt-c .wpDataTablesWrapper table.wpDataTable thead th.sorting {
	background-color: <?php echo $wdtFontColorSettings['wdtHeaderBaseColor'] ?> !important;
    background-image: none !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtHeaderBorderColor'])){ ?>
/* th border color */
.wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable thead tr > th{
    border-bottom-color: <?php echo $wdtFontColorSettings['wdtHeaderBorderColor'] ?> !important;
    border-right-color: <?php echo $wdtFontColorSettings['wdtHeaderBorderColor'] ?> !important;
    border-top-color: <?php echo $wdtFontColorSettings['wdtHeaderBorderColor'] ?> !important;
    border-left-color: <?php echo $wdtFontColorSettings['wdtHeaderBorderColor'] ?> !important;
}
.wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable thead th,
.wpdt-c .wpDataTablesWrapper table.wpDataTable thead th,
.wpdt-c .wpDataTablesWrapper table.wpDataTable thead th.sorting {
	border-color: <?php echo $wdtFontColorSettings['wdtHeaderBorderColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtHeaderFontColor'])){ ?>
/* th font color */
.wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable.bt[data-has-header='1'] td.wpdt-header-classes,
.wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable thead th,
.wpdt-c .wpDataTablesWrapper table.wpDataTable thead th {
	color: <?php echo $wdtFontColorSettings['wdtHeaderFontColor'] ?> !important;
}
.wpdt-c .wpDataTablesWrapper table.wpDataTable thead th.sorting:after,
.wpdt-c .wpDataTablesWrapper table.wpDataTable thead th.sorting_asc:after {
	border-bottom-color: <?php echo $wdtFontColorSettings['wdtHeaderFontColor'] ?> !important;
}
.wpdt-c .wpDataTablesWrapper table.wpDataTable thead th.sorting_desc:after {
	border-top-color: <?php echo $wdtFontColorSettings['wdtHeaderFontColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtHeaderActiveColor'])){ ?>
/* th active/hover background color */
.wpdt-c .wpDataTablesWrapper table.wpDataTable thead th.sorting_asc,
.wpdt-c .wpDataTablesWrapper table.wpDataTable thead th.sorting_desc,
.wpdt-c .wpDataTablesWrapper table.wpDataTable thead th.sorting:hover,
.wpdt-c .wpDataTablesWrapper table.wpDataTable.wpdtSimpleTable thead th:hover,
.wpdt-c.wpDataTablesWrapper table.wpDataTable.wpdtSimpleTable thead th:hover {
	background-color: <?php echo $wdtFontColorSettings['wdtHeaderActiveColor'] ?> !important;
    background-image: none !important;
}
<?php } ?>

<?php if(!empty($wdtFontColorSettings['wdtTableInnerBorderColor'])){ ?>
/* td inner border color */
.wpdt-c.wpDataTablesWrapper table.wpDataTable.wpdtSimpleTable td,
.wpdt-c .wpDataTablesWrapper table.wpDataTable td {
	border-color: <?php echo $wdtFontColorSettings['wdtTableInnerBorderColor'] ?> !important;
}
    <?php if ($wpDataTable->simpleHeader) {?>
        <?php if(empty($wdtFontColorSettings['wdtHeaderBorderColor'])){ ?>
            .wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable thead tr th {
                border-bottom-color: <?php echo $wdtFontColorSettings['wdtTableInnerBorderColor'] ?> !important;
                border-right-color: <?php echo $wdtFontColorSettings['wdtTableInnerBorderColor'] ?> !important;
            }
            .wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable thead tr th:last-child {
                border-right-color: inherit !important;
            }
        <?php } ?>
    <?php } ?>
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtTableOuterBorderColor'])){ ?>
/* table outer border color */
.wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable tr:last-child td,
.wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable tr:last-child td.wpdt-merged-cell:last-child,
.wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable tr:last-child td.wpdt-merged-cell,
.wpdt-c .wpDataTablesWrapper table.wpDataTable tr:last-child td {
	border-bottom-color: <?php echo $wdtFontColorSettings['wdtTableOuterBorderColor'] ?> !important;
}
.wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable tr td:first-child,
.wpdt-c .wpDataTablesWrapper table.wpDataTable tr td:first-child {
	border-left-color: <?php echo $wdtFontColorSettings['wdtTableOuterBorderColor'] ?> !important;
}
.wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable tr td:last-child,
.wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable tr td.wpdt-merged-cell:last-child,
.wpdt-c .wpDataTablesWrapper table.wpDataTable tr td:last-child {
	border-right-color: <?php echo $wdtFontColorSettings['wdtTableOuterBorderColor'] ?> !important;
}
    <?php if (!$wpDataTable->simpleHeader) {?>
        <?php if(empty($wdtFontColorSettings['wdtHeaderBorderColor'])){ ?>
            .wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable tr:first-child td {
                border-top-color: <?php echo $wdtFontColorSettings['wdtTableOuterBorderColor'] ?> !important;
            }
        <?php } ?>
    <?php } else { ?>
        <?php if(empty($wdtFontColorSettings['wdtHeaderBorderColor'])){ ?>
            .wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable thead tr th:last-child {
                border-right-color: <?php echo $wdtFontColorSettings['wdtTableOuterBorderColor'] ?> !important;
            }
            .wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable thead tr th:first-child {
                border-left-color: <?php echo $wdtFontColorSettings['wdtTableOuterBorderColor'] ?> !important;
            }
            .wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable thead tr th {
                border-top-color: <?php echo $wdtFontColorSettings['wdtTableOuterBorderColor'] ?> !important;
            }
        <?php } ?>
    <?php } ?>
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtOddRowColor'])){ ?>
/* odd rows background color */
.wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable tr.odd td,
.wpdt-c .wpDataTablesWrapper table.wpDataTable tr.odd td {
	background-color: <?php echo $wdtFontColorSettings['wdtOddRowColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtEvenRowColor'])){ ?>
/* even rows background color */
.wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable tr.even td,
.wpdt-c .wpDataTablesWrapper table.wpDataTable tr.even td,
.wpdt-c .wpDataTablesWrapper table.has-columns-hidden tr.row-detail > td {
	background-color: <?php echo $wdtFontColorSettings['wdtEvenRowColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtActiveOddCellColor'])){ ?>
/* odd rows active background color */
.wpdt-c .wpDataTablesWrapper table.wpDataTable tr.odd td.sorting_1 {
	background-color: <?php echo $wdtFontColorSettings['wdtActiveOddCellColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtActiveEvenCellColor'])){ ?>
/* even rows active background color */
.wpdt-c .wpDataTablesWrapper table.wpDataTable tr.even td.sorting_1 {
	background-color: <?php echo $wdtFontColorSettings['wdtActiveEvenCellColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtHoverRowColor'])){ ?>
/* rows hover background color */
.wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable tr.odd:hover > td,
.wpdt-c .wpDataTablesWrapper table.wpDataTable tr.odd:hover > td,
.wpdt-c .wpDataTablesWrapper table.wpDataTable tr.odd:hover > td.sorting_1,
.wpdt-c .wpDataTablesWrapper table.wpDataTable tr.even:hover > td,
.wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable tr.even:hover > td,
.wpdt-c .wpDataTablesWrapper table.wpDataTable tr.even:hover > td.sorting_1 {
	background-color: <?php echo $wdtFontColorSettings['wdtHoverRowColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtSelectedRowColor'])){ ?>
/* selected rows background color */
.wpdt-c .wpDataTablesWrapper table.wpDataTable tr.odd.selected > td,
.wpdt-c .wpDataTablesWrapper table.wpDataTable tr.odd.selected > td.sorting_1,
.wpdt-c .wpDataTablesWrapper table.wpDataTable tr.even.selected > td,
.wpdt-c .wpDataTablesWrapper table.wpDataTable tr.even.selected > td.sorting_1 {
	background-color: <?php echo $wdtFontColorSettings['wdtSelectedRowColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtButtonColor'])){ ?>
/* buttons background color */
.wpDataTables .wdt-checkbox-filter.btn,
.wdt-frontend-modal .btn,
div.dt-button-collection a.dt-button.active:not(.disabled) {
	background-color: <?php echo $wdtFontColorSettings['wdtButtonColor'] ?> !important;
    background-image: none !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtButtonBorderColor'])){ ?>
/* buttons border color */
.wpDataTables .wdt-checkbox-filter.btn,
.wdt-frontend-modal .btn:not(.dropdown-toggle),
div.dt-button-collection a.dt-button.active:not(.disabled) {
    <?php if ($wdtFontColorSettings['wdtButtonBorderColor']) { ?>
    border: 1px solid;
	border-color: <?php echo $wdtFontColorSettings['wdtButtonBorderColor'] ?> !important;
    <?php } ?>
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtButtonFontColor'])){ ?>
/* buttons font color */
.wpDataTables .wdt-checkbox-filter.btn,
.wpDataTables .selecter .selecter-selected,
.wdt-frontend-modal .btn:not(.dropdown-toggle),
div.dt-button-collection a.dt-button.active:not(.disabled) {
	color: <?php echo $wdtFontColorSettings['wdtButtonFontColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtBorderRadius'])){ ?>
<?php $wdtBorderRadius = (int)$wdtFontColorSettings['wdtBorderRadius']; ?>
/* buttons and inputs border radius */
.wpDataTables .wdt-checkbox-filter.btn,
.wdt-frontend-modal .btn:not(.dropdown-toggle),
div.dt-button-collection a.dt-button.active:not(.disabled) {
	border-radius: <?php echo $wdtBorderRadius ?>px !important;
}
.wpDataTables input {
    border-radius: <?php echo $wdtBorderRadius ?>px !important;
}
<?php echo $wdtSelecterRadius = $wdtBorderRadius-1 > 0 ? $wdtBorderRadius-1 : 0; ?>
.wpDataTables .selecter .selecter-item:last-child {
	border-radius: 0px 0px <?php echo $wdtSelecterRadius ?>px <?php echo $wdtSelecterRadius ?>px !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtButtonBackgroundHoverColor'])){ ?>
/** buttons background hover color */
.wpDataTables .wdt-checkbox-filter.btn:hover,
.wdt-frontend-modal .btn:not(.dropdown-toggle).btn:hover,
div.dt-button-collection a.dt-button.active:not(.disabled):hover {
	background-color: <?php echo $wdtFontColorSettings['wdtButtonBackgroundHoverColor'] ?> !important;
    background-image: none !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtButtonBorderHoverColor'])){ ?>
/** buttons hover border color */
.wpDataTables .wdt-checkbox-filter.btn:hover,
.wdt-frontend-modal .btn:not(.dropdown-toggle).btn:hover,
div.dt-button-collection a.dt-button.active:not(.disabled):hover {
	border-color: <?php echo $wdtFontColorSettings['wdtButtonBorderHoverColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtButtonFontHoverColor'])){ ?>
/** buttons hover font color */
.wpDataTables .wdt-checkbox-filter.btn:hover,
.wdt-frontend-modal .btn:not(.dropdown-toggle).btn:hover,
div.dt-button-collection a.dt-button.active:not(.disabled):hover {
	color: <?php echo $wdtFontColorSettings['wdtButtonFontHoverColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtButtonFontHoverColor'])){ ?>
/** buttons hover font color */
.wpDataTables .wdt-checkbox-filter.btn:hover,
.wdt-frontend-modal .btn:not(.dropdown-toggle).btn:hover,
div.dt-button-collection a.dt-button.active:not(.disabled):hover {
	color: <?php echo $wdtFontColorSettings['wdtButtonFontHoverColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtModalFontColor'])){ ?>
/** modals font color */
.wpDataTables .picker .picker-handle,
.wpDataTables .picker.focus .picker-handle {
	border-color: <?php echo $wdtFontColorSettings['wdtModalFontColor'] ?> !important;
}
.wpDataTables .picker.picker-checkbox .picker-flag,
.wpDataTables .picker .picker-label,
.wdt-frontend-modal .modal-dialog .modal-content,
.wpDataTables .picker__box,
.wpDataTables .picker__weekday {
	color: <?php echo $wdtFontColorSettings['wdtModalFontColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtModalBackgroundColor'])){ ?>
/** modals background color */
.wdt-frontend-modal .modal-dialog .modal-content,
.wpDataTables .picker__box,
.picker__list-item {
	background-color: <?php echo $wdtFontColorSettings['wdtModalBackgroundColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtOverlayColor'])){ ?>
/** overlays background color */
.modal-backdrop.in,
.wpdt-c .wpDataTablesWrapper .picker--opened .picker__holder {
    background-color: <?php echo $wdtFontColorSettings['wdtOverlayColor'] ?> !important;
}
<?php } ?>
<?php if( get_option('wdtRenderFilter') == 'header')  { ?>
.wpdt-c .wpDataTablesWrapper table.wpDataTable thead tr:nth-child(2) th {
	overflow: visible;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtTableFont'])){ ?>
/* table font color */
.wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable,
.wpdt-c .wpDataTablesWrapper table.wpDataTable {
    font-family: <?php echo $wdtFontColorSettings['wdtTableFont'] ?> !important;
}
<?php } ?>
<?php if( !empty($wdtFontColorSettings['wdtFontSize'] ) ) { ?>
/* table font size */
.wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable,
.wpdt-c .wpDataTablesWrapper table.wpDataTable {
    font-size:<?php echo $wdtFontColorSettings['wdtFontSize'] ?>px !important;
}
 <?php } ?>
<?php if( !empty($wdtFontColorSettings['wdtPaginationCurrentBackgroundColor'] ) ) { ?>
/* pagination current page background color */
.wpDataTablesWrapper .dataTables_paginate .paginate_button.current,
.wpDataTablesWrapper .dataTables_paginate .paginate_button.current:hover{
    background-color:<?php echo $wdtFontColorSettings['wdtPaginationCurrentBackgroundColor'] ?> !important;
}
<?php } ?>
<?php if( !empty($wdtFontColorSettings['wdtPaginationCurrentColor'] ) ) { ?>
/*pagination current page color */
.wpDataTablesWrapper .dataTables_paginate .paginate_button.current,
.wpDataTablesWrapper .dataTables_paginate .paginate_button.current:hover{
    color:<?php echo $wdtFontColorSettings['wdtPaginationCurrentColor'] ?> !important;
}
<?php } ?>

<?php if( !empty($wdtFontColorSettings['wdtPaginationHoverBackgroundColor'] ) ) { ?>
/* pagination other pages hover background color */
.wpDataTablesWrapper .dataTables_paginate .paginate_button:hover:not(.disabled):not(.current){
    background-color:<?php echo $wdtFontColorSettings['wdtPaginationHoverBackgroundColor'] ?> !important;
}
<?php } ?>
<?php if( !empty($wdtFontColorSettings['wdtPaginationHoverColor'] ) ) { ?>
/* pagination other pages hover color */
.wpDataTablesWrapper .dataTables_paginate .paginate_button:hover:not(.disabled):not(.current),
.wpDataTablesWrapper .paginate_button.previous:hover:before,
.wpDataTablesWrapper .paginate_button.first:hover:before,
.wpDataTablesWrapper .paginate_button.next:hover:before,
.wpDataTablesWrapper .paginate_button.last:hover:before{
    color:<?php echo $wdtFontColorSettings['wdtPaginationHoverColor'] ?> !important;
}
<?php } ?>
<?php if( !empty($wdtFontColorSettings['wdtPaginationBackgroundColor'] ) ) { ?>
/* pagination background color */
.wpDataTablesWrapper .dataTables_paginate {
    background-color:<?php echo $wdtFontColorSettings['wdtPaginationBackgroundColor'] ?> !important;
}
<?php } ?>
<?php if( !empty($wdtFontColorSettings['wdtPaginationColor'] ) ) { ?>
/* pagination color  */
.wpDataTablesWrapper .dataTables_paginate .paginate_button,
.wpDataTablesWrapper .dataTables_paginate .paginate_button.disabled:before,
.wpDataTablesWrapper .dataTables_paginate .paginate_button.disabled:hover:before,
.wpDataTablesWrapper .dataTables_paginate .ellipsis,
.wpDataTablesWrapper .paginate_button.previous:before,
.wpDataTablesWrapper .paginate_button.first:before,
.wpDataTablesWrapper .paginate_button.next:before,
.wpDataTablesWrapper .paginate_button.last:before{
    color:<?php echo $wdtFontColorSettings['wdtPaginationColor'] ?> !important;
}
<?php } ?>
</style>

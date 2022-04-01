<?php defined('ABSPATH') or die('Access denied.'); ?>
<?php  /** @var WPDataTable $obj */ ?>
<?php $tableID = $obj->getWpId(); ?>
<?php $filterINForm = $obj->getFilteringForm(); ?>
<?php $renderFilter = get_option('wdtRenderFilter') ?>
<style>
    <?php if(!empty($wdtTableFontColorSettings->wdtTableFontColor)){ ?>
    /* table font color */
    .wpdt-c.wpDataTablesWrapper table#wpdtSimpleTable-<?php echo $tableID ?> thead th,
    .wpdt-c.wpDataTablesWrapper table#wpdtSimpleTable-<?php echo $tableID ?> tbody td,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> thead th,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tbody td,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tfoot td {
        color: <?php echo $wdtTableFontColorSettings->wdtTableFontColor ?> !important;
    }
    <?php } ?>
    <?php if(!empty($wdtTableFontColorSettings->wdtTableHeaderBaseColor)){ ?>
    /* th background color */
    .wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable.bt[data-has-header='1'] td.wpdt-header-classes,
    .wpdt-c.wpDataTablesWrapper table#wpdtSimpleTable-<?php echo $tableID ?> thead th,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> > thead > tr > th,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> thead th,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> thead th.sorting {
        background-color: <?php echo $wdtTableFontColorSettings->wdtTableHeaderBaseColor ?> !important;
        background-image: none !important;
    }
    <?php } ?>
    <?php if(!empty($wdtTableFontColorSettings->wdtTableHeaderBorderColor)){ ?>
    /* th border color */
    .wpdt-c.wpDataTablesWrapper table#wpdtSimpleTable-<?php echo $tableID ?> thead th,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> thead th,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> thead th.sorting {
            border: solid <?php echo $wdtTableFontColorSettings->wdtTableHeaderBorderColor ?> !important;
            border-width: initial;
    }
    <?php } ?>
    <?php if(!empty($wdtTableFontColorSettings->wdtTableHeaderFontColor)){ ?>
    /* th font color */
    .wpdt-c.wpDataTablesWrapper table.wpdtSimpleTable.bt[data-has-header='1'] td.wpdt-header-classes,
    .wpdt-c.wpDataTablesWrapper table#wpdtSimpleTable-<?php echo $tableID ?> thead th,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> thead th {
        color: <?php echo $wdtTableFontColorSettings->wdtTableHeaderFontColor ?> !important;
    }
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> thead th.sorting:after,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> thead th.sorting_asc:after {
        border-bottom-color: <?php echo $wdtTableFontColorSettings->wdtTableHeaderFontColor ?> !important;
    }
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> thead th.sorting_desc:after {
        border-top-color: <?php echo $wdtTableFontColorSettings->wdtTableHeaderFontColor ?> !important;
    }
    <?php } ?>
    <?php if(!empty($wdtTableFontColorSettings->wdtTableHeaderActiveColor)){ ?>
    /* th active/hover background color */
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> thead th.sorting_asc,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> thead th.sorting_desc,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> thead th.sorting:hover,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> thead > tr > th:hover,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable#wpdtSimpleTable-<?php echo $tableID ?> thead th:hover,
    .wpdt-c.wpDataTablesWrapper table.wpDataTable#wpdtSimpleTable-<?php echo $tableID ?> thead th:hover {
        background-color: <?php echo $wdtTableFontColorSettings->wdtTableHeaderActiveColor ?> !important;
        background-image: none !important;
    }
    <?php } ?>
    <?php if(!empty($wdtTableFontColorSettings->wdtTableInnerBorderColor)){ ?>
    /* td inner border color */
        <?php if(empty($wdtTableFontColorSettings->wdtTableHeaderBorderColor)){ ?>
        .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> thead tr:nth-child(2) th,
        <?php } ?>
        .wpdt-c.wpDataTablesWrapper table.wpDataTable#wpdtSimpleTable-<?php echo $tableID ?> td,
        .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> td,
        .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tr.odd td,
        .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tr.even td{
            border-color: <?php echo $wdtTableFontColorSettings->wdtTableInnerBorderColor ?> !important;
        }
        <?php if(empty($wdtTableFontColorSettings->wdtTableHeaderBorderColor)){ ?>
        .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> thead tr:first-child th:not(:first-child):not(:last-child){
            border-left-color: <?php echo $wdtTableFontColorSettings->wdtTableInnerBorderColor ?> !important;
            border-right-color: <?php echo $wdtTableFontColorSettings->wdtTableInnerBorderColor ?> !important;
        }
        .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> thead tr th:first-child {
            border-right-color: <?php echo $wdtTableFontColorSettings->wdtTableInnerBorderColor ?> !important;
            border-bottom-color: <?php echo $wdtTableFontColorSettings->wdtTableInnerBorderColor ?> !important;
        }
        .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> thead tr th:last-child {
            border-left-color: <?php echo $wdtTableFontColorSettings->wdtTableInnerBorderColor ?> !important;
            border-bottom-color: <?php echo $wdtTableFontColorSettings->wdtTableInnerBorderColor ?> !important;
        }
        .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> thead tr:nth-child(1) th {
            border-bottom-color: <?php echo $wdtTableFontColorSettings->wdtTableInnerBorderColor ?> !important;
        }
        <?php } ?>
    <?php } ?>
    <?php if(!empty($wdtTableFontColorSettings->wdtTableOuterBorderColor)){ ?>
    /* table outer border color */
        .wpdt-c.wpDataTablesWrapper table#wpdtSimpleTable-<?php echo $tableID ?> tr:last-child td,
        <?php if ($renderFilter == 'header') { ?>
        .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tr:last-child td{
            border-bottom-color: <?php echo $wdtTableFontColorSettings->wdtTableOuterBorderColor ?> !important;
        }
        <?php } else if ($filterINForm && $renderFilter == 'footer' ){ ?>
        .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tr:last-child td{
            border-bottom-color: <?php echo $wdtTableFontColorSettings->wdtTableOuterBorderColor ?> !important;
        }
        <?php } else { ?>
        .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tfoot tr td{
            border-bottom-color: <?php echo $wdtTableFontColorSettings->wdtTableOuterBorderColor ?> !important;
        }
        <?php }  ?>
            <?php if(empty($wdtTableFontColorSettings->wdtTableHeaderBorderColor)){ ?>
                .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> thead tr th:first-child,
            <?php }  ?>
                .wpdt-c.wpDataTablesWrapper table#wpdtSimpleTable-<?php echo $tableID ?> tr td:first-child,
                .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tr td:first-child{
                    border-left-color: <?php echo $wdtTableFontColorSettings->wdtTableOuterBorderColor ?> !important;
                }
            <?php if(empty($wdtTableFontColorSettings->wdtTableHeaderBorderColor)){ ?>
                .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> thead tr th:last-child,
            <?php }  ?>
                .wpdt-c.wpDataTablesWrapper table#wpdtSimpleTable-<?php echo $tableID ?> tr td:last-child,
                .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tr td:last-child{
                    border-right-color: <?php echo $wdtTableFontColorSettings->wdtTableOuterBorderColor ?> !important;
                }
        <?php if ($renderFilter == 'header') { ?>
            <?php if(empty($wdtTableFontColorSettings->wdtTableHeaderBorderColor)){ ?>
                .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tr:first-child th {
                    border-top-color: <?php echo $wdtTableFontColorSettings->wdtTableOuterBorderColor ?> !important;
                }
            <?php }  ?>
        <?php } else{ ?>
        .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tr th {
            border-top-color: <?php echo $wdtTableFontColorSettings->wdtTableOuterBorderColor ?> !important;
        }
        <?php } ?>

    <?php } ?>

    <?php if(!empty($wdtTableFontColorSettings->wdtTableOddRowColor)){ ?>
    /* odd rows background color */
    .wpdt-c.wpDataTablesWrapper table#wpdtSimpleTable-<?php echo $tableID ?> tr.odd td,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tr.odd td {
        background-color: <?php echo $wdtTableFontColorSettings->wdtTableOddRowColor ?> !important;
    }
    <?php } ?>
    <?php if(!empty($wdtTableFontColorSettings->wdtTableEvenRowColor)){ ?>
    /* even rows background color */
    .wpdt-c.wpDataTablesWrapper table#wpdtSimpleTable-<?php echo $tableID ?> tr.even td,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tr.even td,
    .wpdt-c .wpDataTablesWrapper table.has-columns-hidden.wpDataTableID-<?php echo $tableID ?> tr.row-detail > td {
        background-color: <?php echo $wdtTableFontColorSettings->wdtTableEvenRowColor ?> !important;
    }
    <?php } ?>
    <?php if(!empty($wdtTableFontColorSettings->wdtTableActiveOddCellColor)){ ?>
    /* odd rows active background color */
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tr.odd td.sorting_1 {
        background-color: <?php echo $wdtTableFontColorSettings->wdtTableActiveOddCellColor ?> !important;
    }
    <?php } ?>
    <?php if(!empty($wdtTableFontColorSettings->wdtTableActiveEvenCellColor)){ ?>
    /* even rows active background color */
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tr.even td.sorting_1 {
        background-color: <?php echo $wdtTableFontColorSettings->wdtTableActiveEvenCellColor ?> !important;
    }
    <?php } ?>
    <?php if(!empty($wdtTableFontColorSettings->wdtTableHoverRowColor)){ ?>
    /* rows hover background color */
    .wpdt-c.wpDataTablesWrapper table#wpdtSimpleTable-<?php echo $tableID ?> tr.odd:hover > td,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tr.odd:hover > td,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tr.odd:hover > td.sorting_1,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tr.even:hover > td,
    .wpdt-c.wpDataTablesWrapper table#wpdtSimpleTable-<?php echo $tableID ?> tr.even:hover > td,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tr.even:hover > td.sorting_1 {
        background-color: <?php echo $wdtTableFontColorSettings->wdtTableHoverRowColor ?> !important;
    }
    <?php } ?>
    <?php if(!empty($wdtTableFontColorSettings->wdtTableSelectedRowColor)){ ?>
    /* selected rows background color */
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tr.odd.selected > td,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tr.odd.selected > td.sorting_1,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tr.even.selected > td,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> tr.even.selected > td.sorting_1 {
        background-color: <?php echo $wdtTableFontColorSettings->wdtTableSelectedRowColor ?> !important;
    }
    <?php } ?>
    <?php if(!empty($wdtTableFontColorSettings->wdtTableFont)){ ?>
    /* table font color */
    .wpdt-c.wpDataTablesWrapper table#wpdtSimpleTable-<?php echo $tableID ?>,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> {
        font-family: <?php echo $wdtTableFontColorSettings->wdtTableFont ?> !important;
    }
    <?php } ?>
    <?php if( !empty($wdtTableFontColorSettings->wdtTableFontSize ) ) { ?>
    /* table font size */
    .wpdt-c.wpDataTablesWrapper table#wpdtSimpleTable-<?php echo $tableID ?>,
    .wpdt-c .wpDataTablesWrapper table.wpDataTable.wpDataTableID-<?php echo $tableID ?> {
        font-size:<?php echo $wdtTableFontColorSettings->wdtTableFontSize ?>px !important;
    }
    <?php } ?>
    <?php if( !empty($wdtTableFontColorSettings->wdtTablePaginationCurrentBackgroundColor ) ) { ?>
    /* pagination current page background color */
    .wpDataTablesWrapper.wpDataTableID-<?php echo $tableID ?> .dataTables_paginate .paginate_button.current,
    .wpDataTablesWrapper.wpDataTableID-<?php echo $tableID ?> .dataTables_paginate .paginate_button.current:hover{
        background-color:<?php echo $wdtTableFontColorSettings->wdtTablePaginationCurrentBackgroundColor ?> !important;
    }
    <?php } ?>
    <?php if( !empty($wdtTableFontColorSettings->wdtTablePaginationCurrentColor ) ) { ?>
    /*pagination current page color */
    .wpDataTablesWrapper.wpDataTableID-<?php echo $tableID ?> .dataTables_paginate .paginate_button.current,
    .wpDataTablesWrapper.wpDataTableID-<?php echo $tableID ?> .dataTables_paginate .paginate_button.current:hover{
        color:<?php echo $wdtTableFontColorSettings->wdtTablePaginationCurrentColor ?> !important;
    }
    <?php } ?>

    <?php if( !empty($wdtTableFontColorSettings->wdtTablePaginationHoverBackgroundColor ) ) { ?>
    /* pagination other pages hover background color */
    .wpDataTablesWrapper.wpDataTableID-<?php echo $tableID ?> .dataTables_paginate .paginate_button:hover:not(.disabled):not(.current){
        background-color:<?php echo $wdtTableFontColorSettings->wdtTablePaginationHoverBackgroundColor ?> !important;
    }
    <?php } ?>
    <?php if( !empty($wdtTableFontColorSettings->wdtTablePaginationHoverColor ) ) { ?>
    /* pagination other pages hover color */
    .wpDataTablesWrapper.wpDataTableID-<?php echo $tableID ?> .dataTables_paginate .paginate_button:hover:not(.disabled):not(.current),
    .wpDataTablesWrapper.wpDataTableID-<?php echo $tableID ?> .paginate_button.previous:hover:before,
    .wpDataTablesWrapper.wpDataTableID-<?php echo $tableID ?> .paginate_button.first:hover:before,
    .wpDataTablesWrapper.wpDataTableID-<?php echo $tableID ?> .paginate_button.next:hover:before,
    .wpDataTablesWrapper.wpDataTableID-<?php echo $tableID ?> .paginate_button.last:hover:before{
        color:<?php echo $wdtTableFontColorSettings->wdtTablePaginationHoverColor ?> !important;
    }
    <?php } ?>
    <?php if( !empty($wdtTableFontColorSettings->wdtTablePaginationBackgroundColor ) ) { ?>
    /* pagination background color */
    .wpDataTablesWrapper.wpDataTableID-<?php echo $tableID ?> .dataTables_paginate {
        background-color:<?php echo $wdtTableFontColorSettings->wdtTablePaginationBackgroundColor ?> !important;
    }
    <?php } ?>
    <?php if( !empty($wdtTableFontColorSettings->wdtTablePaginationColor ) ) { ?>
    /* pagination color  */
    .wpDataTablesWrapper.wpDataTableID-<?php echo $tableID ?> .dataTables_paginate .paginate_button,
    .wpDataTablesWrapper.wpDataTableID-<?php echo $tableID ?> .dataTables_paginate .paginate_button.disabled:before,
    .wpDataTablesWrapper.wpDataTableID-<?php echo $tableID ?> .dataTables_paginate .paginate_button.disabled:hover:before,
    .wpDataTablesWrapper.wpDataTableID-<?php echo $tableID ?> .dataTables_paginate .ellipsis,
    .wpDataTablesWrapper.wpDataTableID-<?php echo $tableID ?> .paginate_button.previous:before,
    .wpDataTablesWrapper.wpDataTableID-<?php echo $tableID ?> .paginate_button.first:before,
    .wpDataTablesWrapper.wpDataTableID-<?php echo $tableID ?> .paginate_button.next:before,
    .wpDataTablesWrapper.wpDataTableID-<?php echo $tableID ?> .paginate_button.last:before{
        color:<?php echo $wdtTableFontColorSettings->wdtTablePaginationColor ?> !important;
    }
    <?php } ?>
</style>

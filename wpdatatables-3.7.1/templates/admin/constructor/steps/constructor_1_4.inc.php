<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="col p-0 wdt-constructor-step bg-white wdt-constructor-query-data-step hidden" data-step="1-4">

    <div class="alert alert-info alert-dismissible" role="alert">
        <i class="wpdt-icon-info-circle-full"></i>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
        </button>
        <span class="wdt-alert-title f-600"><?php _e('Please choose the SQL data which will be used to create a table.', 'wpdatatables'); ?></span><br>
        <span class="wdt-alert-subtitle"><?php _e('This constructor type will create a query to any SQL database and create a wpDataTable based on this query. This table content cannot be edited manually afterwards, but will always contain actual data from your SQL database.', 'wpdatatables'); ?></span>
    </div>

    <div class="row">
        <div class="col-sm-6 wdt-constructor-mysql-query-table-name-block">
            <h4 class="c-title-color m-b-2">
                <?php _e('Table name', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('What is the header of the table that will be visible to the site visitors?', 'wpdatatables'); ?>?"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <input type="text" class="form-control input-sm" value="New wpDataTable"
                           id="wdt-constructor-mysql-query-table-name">
                </div>
            </div>
        </div>
    </div>

    <div id="wdt-constructor-mysql-tables-block" class="row">

        <div class="wdt-constructor-mysql-tables-all col-sm-2-6">
            <div class="card m-t-15 m-b-15">
                <div class="card-header col-sm-12 ch-alt p-t-15 p-b-10 p-r-0 p-l-0">
                    <div class="col-sm-12">
                        <h2>
                            <span><?php _e('All SQL tables', 'wpdatatables'); ?></span>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('Add or drag MySQL tables.', 'wpdatatables'); ?>"></i>
                        </h2>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-inner table-vmiddle">
                        <tbody id="wdt-constructor-mysql-tables-all-table">
                        <?php
                        $defaultConnection = null;

                        if (Connection::enabledSeparate()) {
                            foreach (Connection::getAll() as $wdtSeparateConnection) {
                                if ($wdtSeparateConnection['default']) {
                                    $defaultConnection = $wdtSeparateConnection['id'];
                                    break;
                                }
                            }
                        }

                        foreach (wpDataTableConstructor::listMySQLTables($defaultConnection) as $mysqlTable) {
                            echo "<tr><td>$mysqlTable</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="wdt-constructor-arrows col-sm-0-8">
            <button class="btn bgm-gray m-b-5 wdt-constructor-add-mysql-table">
                <i class="wpdt-icon-arrow-right"></i>
            </button>
            <button class="btn bgm-gray wdt-constructor-remove-mysql-table">
                <i class="wpdt-icon-arrow-left"></i>
            </button>
        </div>

        <div class="wdt-constructor-mysql-tables-selected col-sm-2-6">
            <div class="card m-t-15 m-b-15">
                <div class="card-header col-sm-12 ch-alt p-t-15 p-b-10 p-r-0 p-l-0">
                    <div class="col-sm-12">
                        <h2>
                            <span><?php _e('Selected SQL tables', 'wpdatatables'); ?></span>
                        </h2>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-inner table-vmiddle">
                        <tbody id="wdt-constructor-mysql-tables-selected-table">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="wdt-constructor-mysql-columns-all col-sm-2-6">
            <div class="card m-t-15 m-b-15">
                <div class="card-header col-sm-12 ch-alt p-t-15 p-b-10 p-r-0 p-l-0">
                    <div class="col-sm-12">
                        <h2>
                            <span><?php _e('All SQL columns', 'wpdatatables'); ?></span>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('Add or drag MySQL columns.', 'wpdatatables'); ?>"></i>
                        </h2>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-inner table-vmiddle">
                        <tbody id="wdt-constructor-mysql-columns-all-table">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="wdt-constructor-arrows col-sm-0-8">
            <button class="btn bgm-gray m-b-5 wdt-constructor-add-mysql-column">
                <i class="wpdt-icon-arrow-right"></i>
            </button>
            <button class="btn bgm-gray wdt-constructor-remove-mysql-column">
                <i class="wpdt-icon-arrow-left"></i>
            </button>
        </div>

        <div class="wdt-constructor-mysql-columns-selected col-sm-2-6">
            <div class="card m-t-15 m-b-15">
                <div class="card-header col-sm-12 ch-alt p-t-15 p-b-10 p-r-0 p-l-0">
                    <div class="col-sm-12">
                        <h2>
                            <span><?php _e('Selected SQL columns', 'wpdatatables'); ?></span>
                        </h2>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-inner table-vmiddle">
                        <tbody id="wdt-constructor-mysql-columns-selected-table">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    <!-- /.row -->
    <div class="row">
        <div class="col-sm-8 wdt-constructor-mysql-tables-define-relations-block hidden">
            <div class="col-sm-12 p-0">
                <h4 class="c-title-color m-b-2">
                    <?php _e('Define SQL tables relations', 'wpdatatables'); ?>
                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                       title="<?php _e('Check to have an inner join, uncheck to have left join.', 'wpdatatables'); ?>"></i>
                </h4>
            </div>
            <div class="form-group m-b-0" id="wdt-constructor-mysql-tables-relations">

            </div>
        </div>
        <!-- /.col-sm-12 -->
    </div>
    <div class="row">
        <div class="col-sm-12">

            <div class="col-sm-6 p-0 wdt-constructor-mysql-conditions-block hidden">

                    <div class="col-sm-12 p-0">
                        <h4 class="c-title-color m-b-6">
                            <?php _e('Add conditions', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('Add conditions that you would like to have in the table.', 'wpdatatables'); ?>"></i>
                        </h4>
                    </div>
                <div class="form-group" id="wdt-constructor-mysql-conditions">

                </div>
                    <div class="col-sm-12 p-0">
                        <button class="btn pull-left" id="wdt-constructor-add-mysql-condition">
                            <i class="wpdt-icon-plus"></i>
                            <?php _e('Add condition', 'wpdatatables'); ?>
                        </button>
                    </div>

            </div>
            <!-- /.col-sm-6 -->

        </div>
        <!-- /.col-sm-12 -->
    </div>
    <div class="row">
        <div class="col-sm-12">

            <div class="col-sm-6 p-0 wdt-constructor-mysql-grouping-rules-block hidden">

                    <div class="col-sm-12 p-0">
                        <h4 class="c-title-color m-b-6">
                            <?php _e('Add grouping rules', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('Add grouping rules that you would like to have in the table.', 'wpdatatables'); ?>"></i>
                        </h4>
                    </div>
                <div class="form-group" id="wdt-constructor-mysql-grouping-rules">

                </div>
                    <div class="col-sm-12 p-0">
                        <button class="btn pull-left"
                                id="wdt-constructor-mysql-add-grouping-rule">
                            <i class="wpdt-icon-plus"></i>
                            <?php _e('Add grouping', 'wpdatatables'); ?>
                        </button>
                    </div>

            </div>
            <!-- /.col-sm-6 -->

        </div>
        <!-- /.col-sm-12 -->
    </div>
</div>

<script id="wdt-constructor-mysql-column-template" type="text/x-jsrender">
    {{for availableMySqlColumns}}
    <tr>
        <td>{{:}}</td>
    </tr>
    {{/for}}


</script>

<script id="wdt-constructor-mysql-relation-block-template" type="text/x-jsrender">
    <div class="row m-r-0 m-l-0 wdt-constructor-mysql-block">
        <div class="col-sm-2-0 wdt-constructor-relation-initiator-type">
            <span>{{>table}}.</span>
        </div>
        <div class="col-sm-4 p-l-0 p-r-0">
            <select class="wdt-constructor-relation-initiator-column" data-mysql-table="{{>table}}">
                <option value=""></option>
                {{for columns}}
                    <option value="{{:}}">{{:}}</option>
                {{/for}}
            </select>
        </div>
        <div class="col-sm-1-0 p-l-0 p-r-0 wdt-constructor-relation-equal">
            <span><i class="wpdt-icon-equals"></i></span>
        </div>
        <div class="col-sm-4 p-l-0 p-r-0">
             <select class="wdt-constructor-relation-connected-column" data-mysql-table="{{>table}}">
                <option value=""></option>
                {{for otherTableColumns}}
                    <option value="{{:}}">{{:}}</option>
                {{/for}}
             </select>
        </div>
        <div class="col-sm-1-0">
            <div class="form-group">
                <div class="toggle-switch" data-ts-color="blue">
                    <input id="wdt-constructor-relation-inner-join-{{>table}}" type="checkbox">
                    <label for="wdt-constructor-relation-inner-join-{{>table}}"></label>
                </div>
            </div>
        </div>
    </div>


</script>

<script id="wdt-constructor-mysql-where-condition-template" type="text/x-jsrender">
    <div class="row m-l-0 m-r-0 wdt-constructor-mysql-where-block">
        <div class="col-sm-5 p-r-0 p-l-0">
             <select class="wdt-constructor-where-condition-column">
                <option value=""></option>
                {{for allMySqlColumns}}
                    <option value="{{:}}">{{:}}</option>
                {{/for}}
             </select>
        </div>
        <div class="col-sm-3 p-r-0">
            <select class="wdt-constructor-where-operator">
                <option value="eq">=</option>
                <option value="gt">&gt;</option>
                <option value="gtoreq">&gt;=</option>
                <option value="lt">&lt;</option>
                <option value="ltoreq">&lt;=</option>
                <option value="neq">&lt;&gt;</option>
                <option value="like">LIKE</option>
                <option value="plikep">%LIKE%</option>
                <option value="in">IN</option>
            </select>
        </div>
        <div class="col-sm-3 p-r-0">
            <div class="form-group">
                <div class="fg-line">
                    <input type="text" placeholder="Choose criteria" class="form-control input-sm" value="" id="wdt-constructor-where-value">
                </div>
            </div>
        </div>
        <div class="col-sm-1 p-r-0 p-l-0 text-center">
            <ul class="actions p-r-5">
                <li class="p-t-5" id="wdt-constructor-delete-mysql-condition">
                    <a>
                        <i class="wpdt-icon-trash"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>


</script>

<script id="wdt-constructor-mysql-grouping-rule-template" type="text/x-jsrender">
    <div class="row m-b-15 m-l-0 m-r-0 wdt-constructor-mysql-grouping-rule-block">
        <div class="col-sm-2 wdt-constructor-group-by-label">
            <span><?php _e('Group by ', 'wpdatatables'); ?></span>
        </div>
        <div class="col-sm-5 p-r-0 p-l-0">
            <select class="wdt-constructor-grouping-rule-column">
                <option value=""></option>
                {{for mySqlColumns}}
                    <option value="{{:}}">{{:}}</option>
                {{/for}}
            </select>
        </div>
        <div class="col-sm-1 p-l-0 p-r-0 text-center">
            <ul class="actions p-r-5">
                <li class="p-t-5" id="wdt-constructor-delete-grouping-rule-mysql">
                    <a>
                        <i class="wpdt-icon-trash"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>


</script>

<script id="wdt-constructor-mysql-columns-options-template" type="text/x-jsrender">
    {{for}}
        <option value="{{:}}">{{:}}</option>
    {{/for}}


</script>
<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="col p-0 wdt-constructor-step wdt-constructor-query-data-step hidden bg-white" data-step="1-3">

    <div class="alert alert-info alert-dismissible" role="alert">
        <i class="wpdt-icon-info-circle-full"></i>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
        </button>
        <span class="wdt-alert-title f-600"><?php _e('Please choose the WP data which will be used to create a table.', 'wpdatatables'); ?></span><br>
        <span class="wdt-alert-subtitle"><?php _e('This constructor type will create a query to WordPress database and create a wpDataTable based on this query. This table content cannot be edited manually afterwards, but will always contain actual data from your WordPress database.', 'wpdatatables'); ?></span>
    </div>

    <div class="row">
        <div class="col-sm-6 wdt-constructor-wp-query-table-name-block">
            <h4 class="c-title-color m-b-2">
                <?php _e('Table name', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('What is the header of the table that will be visible to the site visitors?', 'wpdatatables'); ?>?"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <input type="text" class="form-control input-sm" value="New wpDataTable"
                           id="wdt-constructor-wp-query-table-name">
                </div>
            </div>
        </div>
    </div>

    <div class="row wdt-constructor-post-types-block">

        <div class="wdt-constructor-post-types-all col-sm-2-6">
            <div class="card m-t-15 m-b-15">
                <div class="card-header col-sm-12 ch-alt p-t-15 p-b-10 p-r-0 p-l-0">
                    <div class="col-sm-12">
                        <h2>
                            <span><?php _e('All post types', 'wpdatatables'); ?></span>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('Add or drag post types.', 'wpdatatables'); ?>"></i>
                        </h2>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-inner table-vmiddle">
                        <tbody id="wdt-constructor-post-types-all-table">
                        <tr>
                            <td><?php _e('all', 'wpdatatables'); ?></td>
                        </tr>
                        <?php foreach (get_post_types() as $post_type) { ?>
                            <tr>
                                <td><?php echo $post_type ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="wdt-constructor-arrows col-sm-0-8">
            <button class="btn m-b-5 wdt-constructor-add-post-type">
                <i class="wpdt-icon-arrow-right"></i>
            </button>
            <button class="btn  wdt-constructor-remove-post-type">
                <i class="wpdt-icon-arrow-left"></i>
            </button>
        </div>

        <div class="wdt-constructor-post-types-selected col-sm-2-6">
            <div class="card m-t-15 m-b-15">
                <div class="card-header col-sm-12 ch-alt p-t-15 p-b-10 p-r-0 p-l-0">
                    <div class="col-sm-12">
                        <h2>
                            <span><?php _e('Selected post types', 'wpdatatables'); ?></span>
                        </h2>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-inner table-vmiddle">
                        <tbody id="wdt-constructor-post-types-selected-table">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="wdt-constructor-post-columns-all col-sm-2-6">
            <div class="card m-t-15 m-b-15">
                <div class="card-header col-sm-12 ch-alt p-t-15 p-b-10 p-r-0 p-l-0">
                    <div class="col-sm-12">
                        <h2>
                            <span><?php _e('All post properties', 'wpdatatables'); ?></span>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('Add or drag post properties.', 'wpdatatables'); ?>"></i>
                        </h2>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-inner table-vmiddle">
                        <tbody id="wdt-constructor-post-columns-all-table">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="wdt-constructor-arrows col-sm-0-8">
            <button class="btn bgm-gray m-b-5 wdt-constructor-add-post-column">
                <i class="wpdt-icon-arrow-right"></i>
            </button>
            <button class="btn bgm-gray wdt-constructor-remove-post-column">
                <i class="wpdt-icon-arrow-left"></i>
            </button>
        </div>

        <div class="wdt-constructor-post-columns-selected col-sm-2-6">
            <div class="card m-t-15 m-b-15">
                <div class="card-header col-sm-12 ch-alt p-t-15 p-b-10 p-r-0 p-l-0">
                    <div class="col-sm-12">
                        <h2>
                            <span><?php _e('Selected post properties', 'wpdatatables'); ?></span>
                        </h2>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-inner table-vmiddle">
                        <tbody id="wdt-constructor-post-columns-selected-table">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    <!-- /.row -->
    <div class="row">

        <div class="col-sm-12 wdt-constructor-post-types-relationship-block hidden p-0">
            <div class="col-sm-4">
                <h4 class="c-title-color m-b-2">
                    <?php _e('Post types relationship', 'wpdatatables'); ?>
                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                       title="<?php _e('When it is enabled, relations will be defined (joining rules) between post types, otherwise relations between post types will not be defined (do a full outer join).', 'wpdatatables'); ?>"></i>
                </h4>
                <div class="form-group">
                    <div class="toggle-switch" data-ts-color="blue">
                        <div class="col-sm-12 p-0">
                            <input id="wdt-constructor-post-types-relationship" checked="checked" type="checkbox">
                            <label for="wdt-constructor-post-types-relationship"
                                   class="ts-label"><?php _e('Define relations (joining rules) between post types', 'wpdatatables'); ?></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.col-sm-4 -->

    </div>
    <div class="row">
        <div class="col-sm-8 wdt-constructor-post-types-define-relations-block hidden">
            <div class="col-sm-12 p-0">
                <h4 class="c-title-color m-b-2">
                    <?php _e('Define post types relations', 'wpdatatables'); ?>
                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                       title="<?php _e('Check to have an inner join, uncheck to have left join.', 'wpdatatables'); ?>"></i>
                </h4>
            </div>
            <div class="form-group m-b-0" id="wdt-constructor-post-types-relations">

            </div>
        </div>
        <!-- /.col-sm-12 -->
    </div>
    <div class="row">
        <div class="col-sm-12">

            <div class="col-sm-6 p-0 wdt-constructor-post-conditions-block hidden">

                    <div class="col-sm-12 p-0">
                        <h4 class="c-title-color m-b-6">
                            <?php _e('Add conditions', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('Add conditions that you would like to have in the table.', 'wpdatatables'); ?>"></i>
                        </h4>
                    </div>

                <div class="form-group" id="wdt-constructor-post-conditions">

                </div>
                <div class="col-sm-12 p-0">
                    <button class="btn pull-left" id="wdt-constructor-add-post-condition">
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

            <div class="col-sm-6 p-0 wdt-constructor-post-grouping-rules-block hidden">

                    <div class="col-sm-12 p-0">
                        <h4 class="c-title-color m-b-6">
                            <?php _e('Add grouping rules', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('Add grouping rules that you would like to have in the table.', 'wpdatatables'); ?>"></i>
                        </h4>
                    </div>


                <div class="form-group" id="wdt-constructor-post-grouping-rules">

                </div>

                <div class="col-sm-12 p-0">
                    <button class="btn pull-left"
                            id="wdt-constructor-post-add-grouping-rule">
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

<script type="text/javascript">
    var wdtPostMetaByPostTypes = <?php echo wpDataTableConstructor::wdtGetPostMetaKeysForPostTypes() ?>;
    var wdtTaxonomiesByPostTypes = <?php echo wpDataTableConstructor::wdtGetTaxonomiesForPostTypes() ?>;
</script>

<script id="wdt-constructor-post-column-template" type="text/x-jsrender">
    {{for availablePostColumns}}
    <tr>
        <td>{{:}}</td>
    </tr>
    {{/for}}


</script>

<script id="wdt-constructor-wp-relation-block-template" type="text/x-jsrender">
    <div class="row m-r-0 m-l-0 wdt-constructor-post-block" data-post-type="{{>postType}}">

        <div class="col-sm-2-0 wdt-constructor-relation-initiator-type">
            <span>{{>postType}}.</span>
        </div>
        <div class="col-sm-4 p-l-0 p-r-0">
            <select class="wdt-constructor-relation-initiator-column" data-post-type="{{>postType}}">
                <option value=""></option>
                {{for postTypeColumns}}
                    <option value="{{:}}">{{:}}</option>
                {{/for}}
            </select>
        </div>
        <div class="col-sm-1-0 p-l-0 p-r-0 wdt-constructor-relation-equal">
            <span><i class="wpdt-icon-equals"></i></span>
        </div>
        <div class="col-sm-4 p-l-0 p-r-0">
             <select class="wdt-constructor-relation-connected-column" data-post-type="{{>postType}}">
                <option value=""></option>
                {{for otherPostTypeColumns}}
                    <option value="{{:}}">{{:}}</option>
                {{/for}}
             </select>
        </div>
        <div class="col-sm-1-0">
            <div class="form-group">
                <div class="toggle-switch" data-ts-color="blue">
                    <input id="wdt-constructor-relation-inner-join-{{>postType}}" type="checkbox">
                    <label for="wdt-constructor-relation-inner-join-{{>postType}}"></label>
                </div>
            </div>
        </div>
    </div>


</script>

<script id="wdt-constructor-post-where-condition-template" type="text/x-jsrender">
    <div class="row m-l-0 m-r-0 wdt-constructor-post-where-block">
        <div class="col-sm-5 p-r-0 p-l-0">
             <select class="wdt-constructor-where-condition-column">
                <option value=""></option>
                {{for postTypeColumns}}
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
                <li class="p-t-5" id="wdt-constructor-delete-post-condition">
                    <a>
                        <i class="wpdt-icon-trash"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>


</script>

<script id="wdt-constructor-post-grouping-rule-template" type="text/x-jsrender">
    <div class="row m-b-15 m-l-0 m-r-0 wdt-constructor-post-grouping-rule-block">
        <div class="col-sm-2 wdt-constructor-group-by-label">
            <span><?php _e('Group by ', 'wpdatatables'); ?></span>
        </div>
        <div class="col-sm-5 p-r-0 p-l-0">
            <select class="wdt-constructor-grouping-rule-column">
                <option value=""></option>
                {{for postTypeColumns}}
                    <option value="{{:}}">{{:}}</option>
                {{/for}}
            </select>
        </div>
        <div class="col-sm-1 p-l-0 p-r-0 text-center">
            <ul class="actions p-r-5">
                <li class="p-t-5" id="wdt-constructor-delete-grouping-rule-post">
                    <a>
                        <i class="wpdt-icon-trash"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>


</script>

<script id="wdt-constructor-post-columns-options-template" type="text/x-jsrender">
    {{for}}
        <option value="{{:}}">{{:}}</option>
    {{/for}}


</script>
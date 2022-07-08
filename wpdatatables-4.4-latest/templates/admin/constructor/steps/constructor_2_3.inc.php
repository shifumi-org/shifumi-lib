<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="col p-0 wdt-constructor-step bg-white hidden" data-step="2-3">

    <div class="card">

        <div class="card-header">
            <h2><?php esc_html_e('Preview the query that has been generated for you', 'wpdatatables'); ?></h2>
            <ul class="actions">
                <li class="wdt-constructor-refresh-wp-query">
                    <a>
                        <i class="wpdt-icon-sync" data-toggle="tooltip" data-placement="top"
                           title="<?php esc_attr_e('Click to refresh the table', 'wpdatatables'); ?>"></i>
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body" id="wdt-constructor-preview-wp-query">

        </div>

    </div>
    <!-- /.row -->

    <div class="card wdt-preview-card">

        <div class="card-header p-l-0">
            <h2><?php esc_html_e('Preview the 5 first result rows', 'wpdatatables'); ?></h2>
        </div>
        <div class="card-body">
            <div class="wdt-constructor-preview-wp-table table-responsive">

            </div>
        </div>

    </div>
    <!-- /.row -->

</div>
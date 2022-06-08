<?php defined('ABSPATH') or die('Access denied.'); ?>

<!-- .wdt-datatables-admin-wrap .wrap -->
<div class="wrap wdt-datatables-admin-wrap">
    <!-- Preloader -->
    <?php include WDT_TEMPLATE_PATH . 'admin/common/preloader.inc.php'; ?>
    <!-- /Preloader -->
    <!-- .container -->
    <div class="container wdt-lite-vs-premium">

        <!-- .row -->
        <div class="row">

            <div class="card card-head m-b-0">
                <?php wp_nonce_field('wdtLiteVSPremiumNonce', 'wdtNonce'); ?>
                <div class="card-header wdt-admin-card-header ch-alt">
                    <img id="wpdt-inline-logo"
                         src="<?php echo WDT_ROOT_URL; ?>assets/img/logo.svg"/>
                    <h2>
                        <a href="<?php echo admin_url('admin.php?page=wpdatatables-dashboard'); ?>">
                            <i class="wpdt-icon-chevron-left"></i>
                        </a>
                        <span style="display: none">wpDataTables Lite VS Premium</span>
                        <?php esc_html_e('Plans', 'wpdatatables'); ?>
                    </h2>
                    <ul class="actions">
                        <li>
                            <button class="btn btn-default btn-icon-text wdt-documentation"
                                    data-doc-page="lite_vs_premium_page">
                                <i class="wpdt-icon-file-thin"></i>
                                <?php esc_html_e('View Documentation', 'wpdatatables'); ?>
                            </button>
                        </li>
                    </ul>
                </div>

            </div>

        </div>
        <!-- /.row -->

        <div class="row">
            <div class="col-sm-12 wdt-lite-vs-premium-block bg-white p-l-0 p-r-0">
                <div class="card wdt-lite-vs-premium-card m-b-0">

                    <div class="card-header wdt-admin-card-header">
                        <h5><?php esc_html_e('Lite vs Premium comparison', 'wpdatatables'); ?></h5>
                        <p><?php esc_html_e('Get the most out of wpDataTables Premium by using all of the powerful features.', 'wpdatatables'); ?></p>
                        <p class="m-b-0">
                            <i class="wpdt-icon-star-full"></i><?php esc_html_e('Thanks for choosing wpDataTables Premium', 'wpdatatables'); ?>
                        </p>
                    </div>
                    <div class="wpdt-comparison-div">
                        <!--  Live vs Premium Table -->
                        <table id="wpdt-comparison">
                            <thead>
                            <th><?php esc_html_e('Features', 'wpdatatables'); ?>
                                <hr>
                            </th>
                            <th><?php esc_html_e('Lite', 'wpdatatables'); ?>
                                <hr>
                            </th>
                            <th><i class="wpdt-icon-star-full"></i><?php esc_html_e('Premium', 'wpdatatables'); ?>
                                <hr>
                            </th>
                            </thead>
                            <tbody>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Creating tables from Excel files', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Creating data tables from CSV files', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Creating data tables from JSON files', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Creating data tables from XML files', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Creating data tables from Serialized PHP array', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Create a Simple tables', 'wpdatatables'); ?>
                                    <span style="color: #ef8137;font-weight: bold;float: right;"><?php esc_html_e('NEW!', 'wpdatatables'); ?></span>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Merge cells', 'wpdatatables'); ?>
                                    <span style="color: #ef8137;font-weight: bold;float: right;"><?php esc_html_e('NEW!', 'wpdatatables'); ?></span>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Column types', 'wpdatatables'); ?> <span style="font-size: 8px;"><?php esc_html_e('(String, Integer, Float, Date, DateTime, Time, Image, URL link and E-mail))', 'wpdatatables'); ?></span>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Sorting', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Pagination', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Export table data', 'wpdatatables'); ?> <span style="font-size: 8px;"><?php esc_html_e('(Export to Excel, CSV, PDF, Copy and Print options))', 'wpdatatables'); ?></span>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Column Customization', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Global Table Customization', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Creating Google charts', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Creating charts with Chart.js', 'wpdatatables'); ?>
                                    <span style="color: #ef8137;font-weight: bold;float: right;"><?php esc_html_e('NEW in Lite!', 'wpdatatables'); ?></span>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Table Customization', 'wpdatatables'); ?>
                                    <span style="color: #ef8137;font-weight: bold;float: right;"><?php esc_html_e('NEW!', 'wpdatatables'); ?></span>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-minus"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Create a data table manually', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-minus"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Creating data tables from Google Spreadsheet', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-minus"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Creating tables via Google Sheet API', 'wpdatatables'); ?>
                                    <span style="color: #ef8137;font-weight: bold;float: right;"><?php esc_html_e('NEW!', 'wpdatatables'); ?></span>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-minus"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Creating tables from Private Google Spreadsheet', 'wpdatatables'); ?>
                                    <span style="color: #ef8137;font-weight: bold;float: right;"><?php esc_html_e('NEW!', 'wpdatatables'); ?></span>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-minus"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Creating MySQL-based data tables from database', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-minus"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Creating MySQL-based data tables from Wordpress post types', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-minus"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Server-side processing', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-minus"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Multiple databases support (MySQL, MS SQL and PostgreSQL)', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-minus"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Advanced filtering', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-minus"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Front-end table editing', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-minus"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Excel-like editing', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-minus"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Creating charts with Highcharts', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-minus"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Creating charts with ApexCharts', 'wpdatatables'); ?>
                                    <span style="color: #ef8137;font-weight: bold;float: right;"><?php esc_html_e('NEW!', 'wpdatatables'); ?></span>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-minus"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Responsive', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-minus"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Conditional formatting', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-minus"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Calculating Tools', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-minus"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Placeholders', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite" class="text-center bg-grey"><i class="wpdt-icon-minus"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full"></i>
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td data-title="Features"><?php esc_html_e('Support', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                                <td data-title="Lite"
                                    class="text-center bg-grey"><?php esc_html_e('Limited', 'wpdatatables'); ?>
                                    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="top"
                                       title="<?php esc_attr_e('Response time is slow and can be up to 3 business days.', 'wpdatatables'); ?>"></i>
                                    <hr>
                                </td>
                                <td data-title="Premium" class="text-center bg-blue"><i
                                            class="wpdt-icon-check-full m-r-5"></i><?php esc_html_e('Premium', 'wpdatatables'); ?>
                                    <hr>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <!--  /Live vs Premium Table -->
                        <p style="padding-left: 28px;padding-top: 10px;font-size: 10px"><?php esc_html_e('* Please note that wpDataTables add-ons are not included in premium version of plugin.', 'wpdatatables'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <h6 class="text-center wdt-footer-title">
                <?php esc_html_e('Made by', 'wpdatatables'); ?>
                <a href="https://tms-outsource.com/?utm_source=full&utm_medium=plugin&utm_campaign=wpdtful" target="_blank">
                    <img src="<?php echo WDT_ASSETS_PATH; ?>img/TMS-Black.svg" alt="" style="width: 66px">
                </a>
            </h6>
            <ul class="wpdt-footer-links text-center">
                <li><a href="https://wpdatatables.com/?utm_source=full&utm_medium=plugin&utm_campaign=wpdtful" target="_blank">wpDataTables.com</a></li>
                <li>|</li>
                <li><a href="https://wpdatatables.com/documentation/general/features-overview/" target="_blank"> <?php esc_html_e('Documentation', 'wpdatatables'); ?></a>
                </li>
                <li>|</li>
                <li><a href="<?php echo admin_url('admin.php?page=wpdatatables-support'); ?>">
                        <?php esc_html_e('Support Center', 'wpdatatables'); ?></a></li>
            </ul>
        </div>
    </div>
    <!-- /.container -->

</div>
<!-- /.wdt-datatables-admin-wrap .wrap -->


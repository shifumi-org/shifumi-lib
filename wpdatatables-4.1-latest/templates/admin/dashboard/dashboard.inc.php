<?php defined('ABSPATH') or die('Access denied.'); ?>

<!-- .wdt-datatables-admin-wrap .wrap -->
<div class="wrap wdt-datatables-admin-wrap">
    <?php do_action('wpdatatables_admin_before_dashboard'); ?>

    <!-- .container -->
    <div class="container wdt-dashbord">

        <!-- .row -->
        <div class="row">

            <div class="card plugin-dashboard">
                <?php wp_nonce_field('wdtDashboardNonce', 'wdtNonce'); ?>
                <div class="card-header wdt-admin-card-header ch-alt">
                    <img id="wpdt-inline-logo"
                         src="<?php echo WDT_ROOT_URL; ?>assets/img/logo.svg"/>
                    <h2>
                        <span style="display: none">wpDataTables Dashboard</span>
                        <?php _e('Dashboard', 'wpdatatables'); ?>
                    </h2>
                    <ul class="actions">
                        <li>
                            <button class="btn btn-default btn-icon-text wdt-documentation"
                                    data-doc-page="dashboard_page">
                                <i class="wpdt-icon-file-thin"></i>
                                <?php _e('View Documentation', 'wpdatatables'); ?>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
        <!-- /.row -->
        <div class="row">
            <div class="col-sm-12 p-l-0 p-r-0">
                <div class="card wdt-welcome-card">
                    <div class="card-body card-padding">
                        <div class="col-sm-7 pull-left p-t-20 p-l-20 p-b-20">
                            <img src="<?php echo WDT_ASSETS_PATH; ?>img/wave.png" style="width: 32px;height: 32px;"
                                 alt="">
                            <h1 class="p-0 m-t-0 m-b-4">
                                <?php _e('Welcome, ', 'wpdatatables');
                                if (wp_get_current_user()->user_firstname != '' && wp_get_current_user()->user_lastname != '') {
                                    echo wp_get_current_user()->user_firstname . ' ' . wp_get_current_user()->user_lastname . '!';
                                } else if (wp_get_current_user()->user_firstname != '') {
                                    echo wp_get_current_user()->user_firstname . '!';
                                } else {
                                    echo wp_get_current_user()->user_login . '!';
                                }
                                ?>

                            </h1>
                            <p class="wpdt-text wpdt-font">   <?php _e('Congratulations! You are about to use the most powerful WordPress table plugin -  wpDataTables is designed to make the process of data representation and interaction quick, easy and effective.', 'wpdatatables'); ?></p>
                            <a href="<?php echo admin_url('admin.php?page=wpdatatables-constructor'); ?>"
                               class="btn btn-primary">
                                <i class="wpdt-icon-table"></i>
                                <?php _e('Create a Table', 'wpdatatables'); ?></a>
                            <a href="<?php echo admin_url('admin.php?page=wpdatatables-chart-wizard'); ?>"
                               class="btn btn-primary">
                                <i class="wpdt-icon-chart-line"></i>
                                <?php _e('Create a Chart', 'wpdatatables'); ?></a>

                            <?php if (get_option('wdtGettingStartedPageStatus') != 1) {?>
                            <a href="<?php echo admin_url('admin.php?page=wpdatatables-getting-started'); ?>"
                               class="wdt-link-tutorials">
                                <?php _e('I need help, show me tutorials', 'wpdatatables'); ?></a>
                            <?php } ?>

                        </div>
                        <div class="col-sm-5 pull-right text-right">
                            <img class="img-responsive wdt-welcome-img" src="<?php echo WDT_ASSETS_PATH; ?>img/dashboard/dashboard-welcome.svg"
                                 alt="Welcome message">
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 card-columns p-r-12 p-l-0">
                <div class="card wdt-table-card">
                    <div class="card-header">
                        <div class="wdt-card-header-title">
                            <i class="wpdt-icon-table"></i>
                            <?php _e('Tables', 'wpdatatables'); ?>
                        </div>
                        <ul class="actions">
                            <li>
                                <button class="wdt-card-header-button">
                                    <?php $tableCount = WDTTools::getTablesCount('table');
                                    if ($tableCount == 0) {
                                        ?>
                                        <a href="<?php echo admin_url('admin.php?page=wpdatatables-constructor'); ?>">
                                            <?php _e('Create Table', 'wpdatatables'); ?>
                                        </a>
                                    <?php } else { ?>
                                        <a href="<?php echo admin_url('admin.php?page=wpdatatables-administration'); ?>">
                                            <?php _e('Browse all tables', 'wpdatatables'); ?>
                                        </a>
                                    <?php } ?>
                                </button>
                            </li>
                        </ul>
                    </div>

                        <?php if ($tableCount == 0) { ?>
                            <div class="card-body wpdt-flex card-padding wdt-empty">
                            <div class="wdt-table-count text-center">
                                <span class="wdt-table-count-number"> <?php echo $tableCount; ?></span>
                                <p><?php _e('Created', 'wpdatatables'); ?></p>
                            </div>
                            <div class="wdt-table-message">
                                <p><?php _e('You have no tables created.', 'wpdatatables'); ?></p>
                                <?php if (get_option('wdtGettingStartedPageStatus') != 1) {?>
                                    <a href="<?php echo admin_url('admin.php?page=wpdatatables-tutorials'); ?>">
                                        <?php _e('View tutorials', 'wpdatatables'); ?>
                                    </a>
                                <?php } ?>
                            </div>
                            <div class="clear"></div>
                    </div>
                        <?php } else {
                            $lastTableData = WDTTools::getLastTableData('table');
                            $tableType = WDTTools::getConvertedTableType($lastTableData->table_type);
                            $simpleType = $lastTableData->table_type == 'simple' ? '&simple': '';
                            ?>
                            <div class="card-body wpdt-flex card-padding">
                            <div class="wdt-table-count text-center">
                                <span class="wdt-table-count-number"><a href="<?php echo admin_url('admin.php?page=wpdatatables-administration'); ?>"> <?php echo $tableCount; ?></a></span>
                                <p><?php _e('Created', 'wpdatatables'); ?></p>
                            </div>
                            <div class="wdt-table-last-created">
                                <a href="admin.php?page=wpdatatables-constructor&source<?php echo $simpleType ?>&table_id=<?php echo (int)$lastTableData->id; ?>"
                                   class="wdt-table-link">
                                    <?php echo $lastTableData->title ?>
                                </a>
                                <span class="wdt-table-type"><?php echo $tableType; ?></span>
                                <div id="wpdt-shortcode-container" class="pull-right">
                                    <a class="wdt-copy-shortcode" data-toggle="tooltip" data-shortcode-type="last-table"
                                       data-placement="top" title=""
                                       data-original-title="<?php _e('Click to copy shortcode', 'wpdatatables'); ?>">
                                        <i class="wpdt-icon-copy"></i>
                                    </a>
                                    <span id="wdt-last-table-shortcode-id">[wpdatatable id=<?php echo $lastTableData->id; ?>]</span>
                                </div>
                                <p><?php _e('Latest table created.', 'wpdatatables'); ?></p>
                                <div class="clear"></div>
                            </div>
                    </div>
                        <?php } ?>

                </div>
                <div class="card wdt-chart-card">
                    <div class="card-header">
                        <div class="wdt-card-header-title">
                            <i class="wpdt-icon-chart-line"></i>
                            <?php _e('Charts', 'wpdatatables'); ?>
                        </div>
                        <ul class="actions">
                            <li>
                                <button class="wdt-card-header-button">
                                    <?php $tableChartsCount = WDTTools::getTablesCount('chart');
                                    if ($tableChartsCount == 0) {
                                        ?>
                                        <a href="<?php echo admin_url('admin.php?page=wpdatatables-chart-wizard'); ?>">
                                            <?php _e('Create a Chart', 'wpdatatables'); ?>
                                        </a>
                                    <?php } else { ?>
                                        <a href="<?php echo admin_url('admin.php?page=wpdatatables-charts'); ?>">
                                            <?php _e('Browse all charts', 'wpdatatables'); ?>
                                        </a>
                                    <?php } ?>
                                </button>
                            </li>
                        </ul>
                    </div>

                        <?php if ($tableChartsCount == 0) { ?>
                    <div class="card-body wpdt-flex card-padding wdt-empty">
                            <div class="wdt-chart-count text-center">
                                <span class="wdt-chart-count-number"> <?php echo $tableChartsCount; ?></span>
                                <p><?php _e('Created', 'wpdatatables'); ?></p>
                            </div>
                            <div class="wdt-chart-message">
                                <p><?php _e('You have no charts created.', 'wpdatatables'); ?></p>
                                <?php if (get_option('wdtGettingStartedPageStatus') != 1) {?>
                                <a href="<?php echo admin_url('admin.php?page=wpdatatables-tutorials'); ?>">
                                    <?php _e('View tutorials', 'wpdatatables'); ?>
                                </a>
                                <?php } ?>
                            </div>
                            <div class="clear"></div>
                    </div>
                        <?php } else {
                            $lastChartData = WDTTools::getLastTableData('chart');
                            ?>
                        <div class="card-body wpdt-flex card-padding">
                            <div class="wdt-chart-count text-center">
                                <span class="wdt-chart-count-number"> <a href="<?php echo admin_url('admin.php?page=wpdatatables-charts'); ?>"><?php echo $tableChartsCount; ?></a></span>
                                <p><?php _e('Created', 'wpdatatables'); ?></p>
                            </div>
                            <div class="wdt-chart-last-created">
                                <a href="admin.php?page=wpdatatables-chart-wizard&chart_id=<?php echo (int)$lastChartData->id; ?>"
                                   class="wdt-chart-link">
                                    <?php echo $lastChartData->title ?>
                                </a>
                                <span class="wdt-chart-engine"><?php echo ucfirst($lastChartData->engine) ?></span>
                                <div id="wpdt-shortcode-container" class="pull-right">
                                    <a class="wdt-copy-shortcode" data-toggle="tooltip" data-shortcode-type="last-chart"
                                       data-placement="top" title=""
                                       data-original-title="<?php _e('Click to copy shortcode', 'wpdatatables'); ?>">
                                        <i class="wpdt-icon-copy"></i>
                                    </a>
                                    <span id="wdt-last-chart-shortcode-id">[wpdatachart id=<?php echo $lastChartData->id; ?>]</span>
                                </div>
                                <p><?php _e('Latest chart created.', 'wpdatatables'); ?></p>
                                <div class="clear"></div>
                            </div>
                        </div>
                        <?php } ?>

                </div>
                <div class="card wdt-support-card">
                    <div class="card-body card-padding">
                        <div class="col-sm-12 p-l-0 p-r-0">
                            <div class="col-sm-6 p-l-12 p-b-12 p-t-12 p-r-0 pull-left">
                                <h4 class="wdt-card-header-title m-t-0 m-b-4">
                                    <?php _e('Still need help? ', 'wpdatatables'); ?>
                                </h4>
                                <p class="wpdt-text wpdt-font">
                                    <?php _e('We provide professional support to all our users via our ticketing system.'); ?></p>
                                <a href="<?php echo admin_url('admin.php?page=wpdatatables-support'); ?>"
                                   class="btn btn-primary">
                                    <?php _e('Visit Support Center', 'wpdatatables'); ?></a>
                            </div>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
                <div class="card wdt-changelog-card">
                    <div class="card-header">
                        <div class="wdt-card-header-title">
                            <i class="wpdt-icon-file"></i>
                            <?php _e('Changelog', 'wpdatatables'); ?>
                        </div>
                        <ul class="actions">
                            <li>
                                <button class="wdt-card-header-button">
                                    <a href="https://wpdatatables.com/help/whats-new-changelog/" target="_blank">
                                        <?php _e('View Changelog', 'wpdatatables'); ?>
                                    </a>
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body card-padding">
                        <p class="wpdt-text wpdt-font m-b-4"> <?php _e('You are currently using ', 'wpdatatables'); ?>
                            <span class="f-600">
                                <?php _e('Version ', 'wpdatatables');
                                echo WDT_CURRENT_VERSION; ?>
                                 </span>
                        </p>
                        <p class="wpdt-text wpdt-font m-b-18">
                            New update with new feature, improvements, and bug fixes:
                        </p>
                        <div class="alert alert-info m-b-0" role="alert">
                            <i class="wpdt-icon-info-circle-full"></i>
                            <ul>
                                <li><strong>Feature:</strong> Added duplicate edit button</li>
                                <li><strong>Improvement:</strong> Removed "Apply and add new" button in the edit modal when "New entry" button is disabled.</li>
                                <li><strong>Improvement:</strong> Added new filters for each chart engine render data: wpdatatables_filter_apexcharts_render_data, wpdatatables_filter_google_charts_render_data, wpdatatables_filter_highcharts_render_data and wpdatatables_filter_chartjs_render_data.</li>
                                <li><strong>BugFix:</strong> Fixed issue with grid options in Apexcharts.</li>
                                <li><strong>BugFix:</strong> Fixed issue with Jet plugins in Elementor widgets.</li>
                                <li><strong>BugFix:</strong> Fixed issue with Gutenberg blocks when Avada's post/page types are disabled.</li>
                                <li><strong>BugFix:</strong> Fixed conflict with Amelia widgets in Divi Builder.</li>
                                <li><strong>BugFix:</strong> Fixed issue with number of rows in HTML source for server-side tables.</li>
                                <li><strong>BugFix:</strong> Fixed issue with conditional formatting for int/float columns when is used "Set cell content".</li>
                                <li>Compatibility with WordPress 5.9.2 approved.</li>
                                <li>Other small bug fixes</li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
            <div class="col-sm-6 card-columns p-l-12 p-r-0">
                <div class="card wdt-store-login-card">
                    <div class="card-header ">
                        <div class="wdt-card-header-title">
                            <i class="wpdt-icon-star-full" style="color: #FFD721;"></i>
                            <?php _e('You are a premium user!', 'wpdatatables'); ?>
                        </div>
                        <ul class="actions">
                            <li class="m-b-0">
                                <button class="wdt-card-header-link">
                                    <a href="https://store.tms-plugins.com/login" target="_blank">
                                        <?php _e('Manage your subscriptions', 'wpdatatables'); ?>
                                    </a>
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body card-padding">
                        <h5 class="card-title"><?php _e('Thank you for choosing wpDataTables Premium', 'wpdatatables'); ?></h5>
                        <p class="card-text m-b-0">
                            <?php _e('Enjoy using our premium features and professional support that we provide through our ', 'wpdatatables'); ?>
                            <a href="https://tmsplugins.ticksy.com/" target="_blank"><?php _e(' ticketing system.'); ?></a>
                        </p>
                    </div>
                </div>
                <div class="card wdt-settings-card">
                    <div class="card-header">
                        <div class="wdt-card-header-title">
                            <i class="wpdt-icon-wrench"></i>
                            <?php _e('Settings', 'wpdatatables'); ?>
                        </div>
                        <ul class="actions">
                            <li>
                                <button class="wdt-card-header-button">
                                    <a href="<?php echo admin_url('admin.php?page=wpdatatables-settings'); ?>">
                                        <?php _e('Configure', 'wpdatatables'); ?>
                                    </a>
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body card-padding">
                        <p class="wpdt-text wpdt-font">
                            <?php _e('Basic system info:', 'wpdatatables'); ?>
                        </p>
                        <ul class="m-b-0">
                            <li><span><?php esc_html_e('PHP', 'wpdatatables'); ?></span>
                                <?php if (version_compare('phpversion()', '5.6.0', '>')) { ?>
                                    <i class="wpdt-icon-check-circle-full"></i>
                                <?php } else { ?>
                                    <i class="wpdt-icon-times-circle-full"></i>
                                <?php } ?>
                            </li>
                            <li><span><?php esc_html_e('MySQL', 'wpdatatables'); ?></span>
                                <?php global $wpdb;
                                if (version_compare($wpdb->db_version(), '5.0.0', '>')) { ?>
                                    <i class="wpdt-icon-check-circle-full"></i>
                                <?php } else { ?>
                                    <i class="wwpdt-icon-times-circle-full"></i>
                                <?php } ?>
                            </li>
                            <li>
                                <span>
                                   <?php esc_html_e('Zip extension ', 'wpdatatables'); ?>
                                </span>
                                <?php if (class_exists('ZipArchive')) { ?>
                                    <i class="wpdt-icon-check-circle-full"></i>
                                <?php } else { ?>
                                    <i class="wpdt-icon-times-circle-full"></i>
                                <?php } ?>
                            </li>
                            <li>
                                <span>
                                    <?php esc_html_e('Curl extension ', 'wpdatatables'); ?>
                                </span>
                                <?php
                                if (extension_loaded('curl')) { ?>
                                    <i class="wpdt-icon-check-circle-full"></i>
                                <?php } else { ?>
                                    <i class="wpdt-icon-times-circle-full"></i>
                                <?php } ?>
                            </li>
                        </ul>
                        <p class="wdt-link pull-right m-b-0">
                            <a href="<?php echo admin_url('admin.php?page=wpdatatables-system-info'); ?>">
                                <?php _e('View Full System Info', 'wpdatatables'); ?></a></p>
                        <div class="clear"></div>
                    </div>

                </div>
                <div class="card wdt-blog-card">
                    <div class="card-header">
                        <div class="wdt-card-header-title">
                            <i class="wpdt-icon-bullhorn-full"></i>
                            <?php _e('News Blog', 'wpdatatables'); ?>
                        </div>
                    </div>
                    <div class="card-body card-padding">
                        <p class="wpdt-text">
                            <?php _e('Checkout useful articles from wpdatatables.com', 'wpdatatables'); ?>
                        </p>
                        <ul>

                            <?php
                            if (extension_loaded('xml') && extension_loaded('dom') && ini_get('allow_url_fopen')) {
                                $rss = new DOMDocument();
                                @$rss->load('https://wpdatatables.com/feed/');
                                if($rss){
                                    $feed = array();
                                    foreach ($rss->getElementsByTagName('item') as $node) {
                                        $item = array(
                                            'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
                                            'link' => $node->getElementsByTagName('link')->item(0)->nodeValue
                                        );
                                        array_push($feed, $item);
                                    }
                                    $limit = 4;
                                    if(!empty($feed)){
                                        for ($x = 0; $x < $limit; $x++) {
                                            $title = str_replace(' & ', ' &amp; ', $feed[$x]['title']);
                                            $link = $feed[$x]['link'];
                                            echo ' <li> <a href="' . $link . '" title="' . $title . '" class="card-link" target="_blank">' . $title . '<i class="wpdt-icon-external-link-square-alt"></i></a></li>';
                                        }
                                    } else { ?>
                                        <li> <a href="https://wpdatatables.com/how-to-create-the-premier-league-table/" title="How to create the Premier League table for your site" class="card-link" target="_blank">How to create the Premier League table for your site<i class="wpdt-icon-external-link-square-alt"></i></a></li>
                                        <li> <a href="https://wpdatatables.com/charts-vs-tables/" title="Charts Vs Tables or When to Use One Over the Other" class="card-link" target="_blank">Charts Vs Tables or When to Use One Over the Other<i class="wpdt-icon-external-link-square-alt"></i></a></li>
                                        <li> <a href="https://wpdatatables.com/scan-wordpress-database-for-malware/" title="How to Scan The WordPress Database For Malware" class="card-link" target="_blank">How to Scan The WordPress Database For Malware<i class="wpdt-icon-external-link-square-alt"></i></a></li>
                                        <li> <a href="https://wpdatatables.com/wordpress-database-cleanup/" title="How to Do a WordPress Database Cleanup" class="card-link" target="_blank">How to Do a WordPress Database Cleanup<i class="wpdt-icon-external-link-square-alt"></i></a></li>
                                    <?php }

                                } else { ?>
                                    <li> <a href="https://wpdatatables.com/how-to-create-the-premier-league-table/" title="How to create the Premier League table for your site" class="card-link" target="_blank">How to create the Premier League table for your site<i class="wpdt-icon-external-link-square-alt"></i></a></li>
                                    <li> <a href="https://wpdatatables.com/charts-vs-tables/" title="Charts Vs Tables or When to Use One Over the Other" class="card-link" target="_blank">Charts Vs Tables or When to Use One Over the Other<i class="wpdt-icon-external-link-square-alt"></i></a></li>
                                    <li> <a href="https://wpdatatables.com/scan-wordpress-database-for-malware/" title="How to Scan The WordPress Database For Malware" class="card-link" target="_blank">How to Scan The WordPress Database For Malware<i class="wpdt-icon-external-link-square-alt"></i></a></li>
                                    <li> <a href="https://wpdatatables.com/wordpress-database-cleanup/" title="How to Do a WordPress Database Cleanup" class="card-link" target="_blank">How to Do a WordPress Database Cleanup<i class="wpdt-icon-external-link-square-alt"></i></a></li>
                                <?php }

                            } else {  ?>
                                <li> <a href="https://wpdatatables.com/how-to-create-the-premier-league-table/" title="How to create the Premier League table for your site" class="card-link" target="_blank">How to create the Premier League table for your site<i class="wpdt-icon-external-link-square-alt"></i></a></li>
                                <li> <a href="https://wpdatatables.com/charts-vs-tables/" title="Charts Vs Tables or When to Use One Over the Other" class="card-link" target="_blank">Charts Vs Tables or When to Use One Over the Other<i class="wpdt-icon-external-link-square-alt"></i></a></li>
                                <li> <a href="https://wpdatatables.com/scan-wordpress-database-for-malware/" title="How to Scan The WordPress Database For Malware" class="card-link" target="_blank">How to Scan The WordPress Database For Malware<i class="wpdt-icon-external-link-square-alt"></i></a></li>
                                <li> <a href="https://wpdatatables.com/wordpress-database-cleanup/" title="How to Do a WordPress Database Cleanup" class="card-link" target="_blank">How to Do a WordPress Database Cleanup<i class="wpdt-icon-external-link-square-alt"></i></a></li>
                            <?php } ?>
                        </ul>
                        <div class="clear"></div>
                        <div class="wdt-subscribe">
                            <div class="wdt-subscribe-message">
                                <i class="wpdt-icon-envelope"></i>
                                <p class="wpdt-text"><?php _e('Never miss notifications about new cool features, promotions,
                                    giveaways or freebies – subscribe to our newsletter! Join 3000+ subscribers. We send
                                    about 1 message per month and never spam!', 'wpdatatables'); ?> </p>
                            </div>
                            <div class="wdt-subscribe-form">
                                <div class="_form_38"></div>
                                <script src="https://tmsoutsource.activehosted.com/f/embed.php?id=38"
                                        type="text/javascript" charset="utf-8"></script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 p-l-0 p-r-0">
                <h4 class="wdt-row-title text-center">
                    <?php _e('wpDataTables Addons', 'wpdatatables'); ?></h4>
                <p class="text-center wdt-row-desc">
                    <?php _e('While wpDataTables itself provides quite a large amount of features and unlimited customisation, flexibility, you can achieve even more with our premium addons. <br>(except Forminator Forms integration which is free and can be used with Lite version as well, all others requires wpDataTables Premium version)', 'wpdatatables'); ?>
                </p>
            </div>
        </div>
        <div class="row">
            <div class="col-1-4 col-md-6 col-xs-12 p-l-0">
                <div class="card wdt-addons-card">
                    <div class="thumbnail">
                        <div class="ribbon"><span><?php _e('Free', 'wpdatatables'); ?></span></div>
                        <div class="wpdt-addons-desc text-center">
                            <img class="img-responsive"
                                 src="<?php echo WDT_ASSETS_PATH; ?>img/addons/forminator-forms-logo.png"
                                 alt="">
                        </div>
                        <h4 class="text-center">
                            <?php _e('Forminator Forms integration for wpDataTables', 'wpdatatables'); ?>
                        </h4>
                        <div class="caption p-0">
                            <p class="text-center">
                                <?php _e('Tool that adds "Forminator Form" as a new table type and allows you to create wpDataTables from Forminator Forms submissions.', 'wpdatatables'); ?>
                            </p>
                        </div>
                        <?php if (!defined('WDT_FRF_ROOT_PATH')) { ?>
                            <div class="wdt-addons-links text-center">
                                <button id="wdt-forminator-integration" class="btn btn-icon-text btn-primary">
                                    <a href="https://downloads.wordpress.org/plugin/wpdatatables-forminator.zip" class="" role="button">
                                        <i class="wpdt-icon-file-download m-r-5"></i><?php _e('Download', 'wpdatatables'); ?></a>
                                    </button>
                                <a href="https://wordpress.org/plugins/wpdatatables-forminator/" target="_blank" class="wdt-addons-find-out-more" role="button"><?php _e('Learn more', 'wpdatatables'); ?></a>
                                <div class="clear"></div>
                            </div>
                        <?php } else { ?>
                            <div class="wdt-addons-links text-center">
                                <button class="wdt-plugin-installed btn btn-icon-text btn-primary">
                                    <i class="wpdt-icon-check-full m-r-5"></i>
                                    <?php _e('Installed', 'wpdatatables'); ?>
                                </button>
                                <div class="clear"></div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="col-1-4 col-md-6 col-xs-12 p-l-0">
                <div class="card wdt-addons-card">
                    <div class="thumbnail">
                        <div class="wpdt-addons-desc text-center">
                            <img class="img-responsive"
                                 src="<?php echo WDT_ASSETS_PATH; ?>img/addons/master-detail-logo.png"
                                 alt="">
                        </div>
                        <h4 class="text-center">
                            <?php _e('Master Detail Tables for wpDataTables', 'wpdatatables'); ?>
                        </h4>
                        <div class="caption p-0">
                            <p class="text-center">
                                <?php _e('A wpDataTables addon which allows showing additional details for a specific row in a popup or a separate page or post.', 'wpdatatables'); ?>
                            </p>
                        </div>
                        <?php if (!defined('WDT_MD_ROOT_PATH')) { ?>
                            <div class="wdt-addons-links text-center">
                                <button id="wdt-master-detail"
                                        class="btn btn-icon-text btn-primary wdt-checkout-button tms-store-checkout">
                                    <i class="wpdt-icon-shopping-bag"></i>
                                    <?php _e('Purchase', 'wpdatatables'); ?>
                                </button>
                                <a href="https://wpdatatables.com/documentation/addons/master-detail-tables/?utm_source=wpdt-admin&amp;medium=addons&amp;campaign=addons"
                                   target="_blank" class="wdt-addons-find-out-more" role="button">
                                    <?php _e('Learn more', 'wpdatatables'); ?>
                                </a>
                                <div class="clear"></div>
                            </div>
                        <?php } else { ?>
                            <div class="wdt-addons-links text-center">
                                <button class="wdt-plugin-installed btn btn-icon-text btn-primary">
                                    <i class="wpdt-icon-check-full"></i>
                                    <?php _e('Installed', 'wpdatatables'); ?>
                                </button>
                                <div class="clear"></div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="col-1-4 p-l-0 col-md-6 col-xs-12">
                <div class="card wdt-addons-card">
                    <div class="thumbnail">
                        <div class="wpdt-addons-desc text-center">
                            <img class="img-responsive"
                                 src="<?php echo WDT_ASSETS_PATH; ?>img/addons/powerful-filters-logo.png"
                                 alt="">
                        </div>
                        <h4 class="text-center">
                            <?php _e('Powerful Filters for wpDataTables', 'wpdatatables'); ?>
                        </h4>
                        <div class="caption p-0">
                            <p class="text-center">
                                <?php _e('An add-on for wpDataTables that provides powerful filtering features: cascade filtering, applying filters on button click, hide table before filtering.', 'wpdatatables'); ?>
                            </p>
                        </div>
                        <?php if (!defined('WDT_PF_ROOT_PATH')) { ?>
                            <div class="wdt-addons-links text-center">
                                <button id="wdt-powerful-filters"
                                        class="btn btn-icon-text btn-primary wdt-checkout-button tms-store-checkout">
                                    <i class="wpdt-icon-shopping-bag"></i>
                                    <?php _e('Purchase', 'wpdatatables'); ?>
                                </button>
                                <a href="https://wpdatatables.com/powerful-filtering/?utm_source=wpdt-admin&medium=addons&campaign=addons"
                                   target="_blank" class="wdt-addons-find-out-more" role="button">
                                    <?php _e('Learn more', 'wpdatatables'); ?>
                                </a>
                                <div class="clear"></div>
                            </div>
                        <?php } else { ?>
                            <div class="wdt-addons-links text-center">
                                <button class="wdt-plugin-installed btn btn-icon-text btn-primary">
                                    <i class="wpdt-icon-check-full"></i>
                                    <?php _e('Installed', 'wpdatatables'); ?>
                                </button>
                                <div class="clear"></div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="col-1-4 p-l-0 col-md-6 col-xs-12">
                <div class="card wdt-addons-card">
                    <div class="thumbnail">
                        <div class="wpdt-addons-desc text-center">
                            <img class="img-responsive"
                                 src="<?php echo WDT_ASSETS_PATH; ?>img/addons/report-builder-logo.png" alt="">
                        </div>
                        <h4 class="text-center">
                            <?php _e('Report Builder', 'wpdatatables'); ?>
                        </h4>
                        <div class="caption p-0">
                            <p class="text-center">
                                <?php _e('A unique tool that allows you to generate almost any Word DOCX and Excel XLSX documents filled in with actual data from your database.', 'wpdatatables'); ?>
                            </p>
                        </div>
                        <?php if (!defined('WDT_RB_ROOT_PATH')) { ?>
                            <div class="wdt-addons-links text-center">
                                <button id="reportbuilder"
                                        class="btn btn-icon-text btn-primary wdt-checkout-button tms-store-checkout">
                                    <i class="wpdt-icon-shopping-bag"></i>
                                    <?php _e('Purchase', 'wpdatatables'); ?>
                                </button>
                                <a href="http://wpreportbuilder.com?utm_source=wpdt" target="_blank"
                                   class="wdt-addons-find-out-more" role="button">
                                    <?php _e('Learn more', 'wpdatatables'); ?>
                                </a>
                                <div class="clear"></div>
                            </div>
                        <?php } else { ?>
                            <div class="wdt-addons-links text-center">
                                <button class="wdt-plugin-installed btn btn-icon-text btn-primary">
                                    <i class="wpdt-icon-check-full"></i>
                                    <?php _e('Installed', 'wpdatatables'); ?>
                                </button>
                                <div class="clear"></div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="col-1-4 p-l-0 col-md-6 col-xs-12">
                <div class="card wdt-addons-card">
                    <div class="thumbnail">
                        <div class="wpdt-addons-desc text-center">
                            <img class="img-responsive wdt-formidable-img"
                                 src="<?php echo WDT_ASSETS_PATH; ?>img/addons/formidable-forms-logo.png"
                                 alt="">
                        </div>
                        <h4 class="text-center">
                            <?php _e('Formidable Forms integration for wpDataTables', 'wpdatatables'); ?>
                        </h4>
                        <div class="caption p-0">
                            <p class="text-center">
                                <?php _e('Tool that adds "Formidable Form" as a new table type and allows you to create wpDataTables from Formidable Forms entries data.', 'wpdatatables'); ?>
                            </p>
                        </div>
                        <?php if (!defined('WDT_FF_ROOT_PATH')) { ?>
                            <div class="wdt-addons-links text-center">
                                <button id="wdt-formidable-integration"
                                        class="btn btn-icon-text btn-primary wdt-checkout-button tms-store-checkout">
                                    <i class="wpdt-icon-shopping-bag"></i>
                                    <?php _e('Purchase', 'wpdatatables'); ?>
                                </button>
                                <a href="https://wpdatatables.com/documentation/addons/formidable-forms-integration/?utm_source=wpdt-admin&medium=addons&campaign=addons"
                                   target="_blank" class="wdt-addons-find-out-more" role="button">
                                    <?php _e('Learn more', 'wpdatatables'); ?>
                                </a>
                                <div class="clear"></div>
                            </div>
                        <?php } else { ?>
                            <div class="wdt-addons-links text-center">
                                <button class="wdt-plugin-installed btn btn-icon-text btn-primary">
                                    <i class="wpdt-icon-check-full"></i>
                                    <?php _e('Installed', 'wpdatatables'); ?>
                                </button>
                                <div class="clear"></div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="col-1-4 p-l-0 col-md-6 col-xs-12">
                <div class="card wdt-addons-card">
                    <div class="thumbnail">
                        <div class="wpdt-addons-desc text-center">
                            <img class="img-responsive wdt-gravity-img"
                                 src="<?php echo WDT_ASSETS_PATH; ?>img/addons/gravity-forms-logo.png" alt="">
                        </div>
                        <h4 class="text-center">
                            <?php _e('Gravity Forms integration for wpDataTables', 'wpdatatables'); ?>
                        </h4>
                        <div class="caption p-0">
                            <p class="text-center">
                                <?php _e('Tool that adds "Gravity Form" as a new table type and allows you to create wpDataTables from Gravity Forms entries data.', 'wpdatatables'); ?>
                            </p>
                        </div>
                        <?php if (!defined('WDT_GF_ROOT_PATH')) { ?>
                            <div class="wdt-addons-links text-center">
                                <button id="wdt-gravity-integration"
                                        class="btn btn-icon-text btn-primary wdt-checkout-button tms-store-checkout">
                                    <i class="wpdt-icon-shopping-bag"></i>
                                    <?php _e('Purchase', 'wpdatatables'); ?>
                                </button>
                                <a href="https://wpdatatables.com/documentation/addons/gravity-forms-integration/?utm_source=wpdt-admin&medium=addons&campaign=addons"
                                   target="_blank" class="wdt-addons-find-out-more" role="button">
                                    <?php _e('Learn more', 'wpdatatables'); ?>
                                </a>
                                <div class="clear"></div>
                            </div>
                        <?php } else { ?>
                            <div class="wdt-addons-links text-center">
                                <button class="wdt-plugin-installed btn btn-icon-text btn-primary">
                                    <i class="wpdt-icon-check-full"></i>
                                    <?php _e('Installed', 'wpdatatables'); ?>
                                </button>
                                <div class="clear"></div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <h4 class="wdt-row-title m-b-20 text-center">
                <?php _e('Need booking plugin?', 'wpdatatables'); ?></h4>
        </div>
        <div class="row">
            <div class="col-sm-12 p-l-0 p-r-0">
                <div class="card wdt-amelia-card m-b-0">
                    <div class="card-body card-padding">
                        <div class="col-sm-6 pull-left amelia-desc">
                            <img src="<?php echo WDT_ASSETS_PATH; ?>img/amelia-logo.png" style="width: 122px;">
                            <div class="amelia-title m-b-4">
                                <?php _e('Appointments and Events WordPress Booking Plugin', 'wpdatatables'); ?>
                            </div>
                            <p class="wpdt-text wpdt-font m-b-0">
                                <?php _e('Amelia is a simple yet powerful automated booking specialist, working 24/7 to make sure your customers can book appointments or events and pay online even while you sleep! 30,000+ businesses from healthcare, beauty, sports, automotive, educational, creative, HR and other industries use Amelia to flawlessly manage 300,000+ appointments and events worldwide each month.', 'wpdatatables'); ?></p>
                            <p>
                                <span class="wdt-stars-container stars-100">★★★★★</span>
                                <span class="wdt-rating"> <?php _e('Rating: 4.8 - ‎123 reviews') ?></span>

                            </p>
                            <a href="https://wpamelia.com/?utm_source=full&utm_medium=plugin&utm_campaign=wpdtful" target="_blank" class="btn btn-primary">
                                <?php _e('Learn More', 'wpdatatables'); ?>
                                <i class="wpdt-icon-external-link-square-alt"></i>
                            </a>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <h6 class="text-center wdt-footer-title">
                <?php _e('Made by', 'wpdatatables'); ?>
                <a href="https://tms-outsource.com/?utm_source=full&utm_medium=plugin&utm_campaign=wpdtful" target="_blank">
                    <img src="<?php echo WDT_ASSETS_PATH; ?>img/TMS-Black.svg" alt="" style="width: 66px">
                </a>
            </h6>
            <ul class="wpdt-footer-links text-center">
                <li><a href="https://wpdatatables.com/?utm_source=full&utm_medium=plugin&utm_campaign=wpdtful" target="_blank">wpDataTables.com</a></li>
                <li>|</li>
                <li><a href="https://wpdatatables.com/documentation/general/features-overview/" target="_blank"> <?php _e('Documentation', 'wpdatatables'); ?></a>
                </li>
                <li>|</li>
                <li><a href="<?php echo admin_url('admin.php?page=wpdatatables-support'); ?>">
                        <?php _e('Support Center', 'wpdatatables'); ?></a></li>
            </ul>
        </div>
    </div>
    <!-- /.container -->

</div>
<!-- /.wdt-datatables-admin-wrap .wrap -->



<?php

defined('ABSPATH') or die('Access denied.');

/**
 * Created by PhpStorm.
 * User: miljkomilosevic
 * Date: 12/2/16
 * Time: 4:12 PM
 */
class WDTSettingsController
{

    public static function sanitizeSettings($settings)
    {
        foreach ($settings as $key => &$setting) {
            if (is_array($setting)) {
                foreach ($setting as &$childSetting) {
                    $childSetting = sanitize_text_field($childSetting);
                }
            } elseif (function_exists('sanitize_textarea_field') && ($key === "wdtCustomJs" || $key === "wdtCustomCss")) {
                $setting = sanitize_textarea_field($setting);
            } else {
                $setting = sanitize_text_field($setting);
            }
        }
        return $settings;
    }

    public static function saveGoogleSettings($settings)
    {
        $result = [];
        if (isset($settings['private_key_id'], $settings['private_key'], $settings['client_email'], $settings['client_id'])) {
            $googleSheet = new WPDataTable_Google_Sheet();
            try {
                $token = $googleSheet->getToken($settings);
            } catch (WDTException $e) {
                $result['error'] = '<br>' . $e->getMessage();
                echo json_encode($result) ;
                exit();
            }
            if ($token[0]) {
                $token[1]['expires_in'] = time() + $token[1]['expires_in'];
                update_option('wdtGoogleSettings', $settings);
                update_option('wdtGoogleToken', $token[1]);
            }
        } else {
            $result['error'] = '<br>Private data for your Google service account are not set.';
            echo json_encode($result) ;
            exit();
        }
    }

    public static function saveSettings($settings)
    {
        $settings = self::sanitizeSettings(stripslashes_deep($settings));

        foreach ($settings as $key => $value) {
            update_option($key, $value);
        }

        do_action('wpdatatables_after_save_settings');
    }

    public static function getCurrentPluginConfig()
    {
        return array(
            'wdtInterfaceLanguage' => get_option('wdtInterfaceLanguage'),
            'wdtTablesPerPage' => get_option('wdtTablesPerPage'),
            'wdtDateFormat' => get_option('wdtDateFormat'),
            'wdtTimeFormat' => get_option('wdtTimeFormat'),
            'wdtBaseSkin' => get_option('wdtBaseSkin'),
            'wdtNumberFormat' => get_option('wdtNumberFormat'),
            'wdtRenderFilter' => get_option('wdtRenderFilter'),
            'wdtDecimalPlaces' => get_option('wdtDecimalPlaces'),
            'wdtCSVDelimiter' => get_option('wdtCSVDelimiter'),
            'wdtSortingOrderBrowseTables'=> get_option('wdtSortingOrderBrowseTables'),
            'wdtTabletWidth' => get_option('wdtTabletWidth'),
            'wdtMobileWidth' => get_option('wdtMobileWidth'),
            'wdtGettingStartedPageStatus' => get_option('wdtGettingStartedPageStatus'),
            'wdtLiteVSPremiumPageStatus' => get_option('wdtLiteVSPremiumPageStatus'),
            'wdtIncludeBootstrap' => get_option('wdtIncludeBootstrap'),
            'wdtIncludeBootstrapBackEnd' => get_option('wdtIncludeBootstrapBackEnd'),
            'wdtPreventDeletingTables' => get_option('wdtPreventDeletingTables'),
            'wdtParseShortcodes' => get_option('wdtParseShortcodes'),
            'wdtNumbersAlign' => get_option('wdtNumbersAlign'),
            'wdtBorderRemoval' => get_option('wdtBorderRemoval'),
            'wdtBorderRemovalHeader' => get_option('wdtBorderRemovalHeader'),
            'wdtUseSeparateCon' => get_option('wdtUseSeparateCon'),
            'wdtSeparateCon' => get_option('wdtSeparateCon'),
            'wdtCustomCss' => get_option('wdtCustomCss'),
            'wdtCustomJs' => get_option('wdtCustomJs'),
            'wdtMinifiedJs' => get_option('wdtMinifiedJs'),
            'wdtGoogleSettings' => get_option('wdtGoogleSettings'),
            'wdtGoogleToken' => get_option('wdtGoogleToken'),
            'wdtSumFunctionsLabel' => get_option('wdtSumFunctionsLabel'),
            'wdtAvgFunctionsLabel' => get_option('wdtAvgFunctionsLabel'),
            'wdtMinFunctionsLabel' => get_option('wdtMinFunctionsLabel'),
            'wdtMaxFunctionsLabel' => get_option('wdtMaxFunctionsLabel'),
            'wdtFontColorSettings' => get_option('wdtFontColorSettings') ? get_option('wdtFontColorSettings') : new stdClass(),
            'wdtActivated' => get_option('wdtActivated'),
            'wdtPurchaseCodeStore' => get_option('wdtPurchaseCodeStore') != '' ? 1 : 0,
            'wdtEnvatoTokenEmail' => get_option('wdtEnvatoTokenEmail'),
            'wdtActivatedPowerful' => get_option('wdtActivatedPowerful'),
            'wdtPurchaseCodeStorePowerful' => get_option('wdtPurchaseCodeStorePowerful') != '' ? 1 : 0,
            'wdtEnvatoTokenEmailPowerful' => get_option('wdtEnvatoTokenEmailPowerful'),
            'wdtActivatedReport' => get_option('wdtActivatedReport'),
            'wdtPurchaseCodeStoreReport' => get_option('wdtPurchaseCodeStoreReport') != '' ? 1 : 0,
            'wdtEnvatoTokenEmailReport' => get_option('wdtEnvatoTokenEmailReport'),
            'wdtActivatedGravity' => get_option('wdtActivatedGravity'),
            'wdtPurchaseCodeStoreGravity' => get_option('wdtPurchaseCodeStoreGravity') != '' ? 1 : 0,
            'wdtEnvatoTokenEmailGravity' => get_option('wdtEnvatoTokenEmailGravity'),
            'wdtActivatedFormidable' => get_option('wdtActivatedFormidable'),
            'wdtPurchaseCodeStoreFormidable' => get_option('wdtPurchaseCodeStoreFormidable') != '' ? 1 : 0,
            'wdtEnvatoTokenEmailFormidable' => get_option('wdtEnvatoTokenEmailFormidable'),
            'wdtActivatedMasterDetail' => get_option('wdtActivatedMasterDetail'),
            'wdtPurchaseCodeStoreMasterDetail' => get_option('wdtPurchaseCodeStoreMasterDetail') != '' ? 1 : 0,
        );
    }

    /**
     * Returns languages
     */

    public static function getInterfaceLanguages()
    {

        $languages = array();

        foreach (glob(WDT_ROOT_PATH . 'source/lang/*.inc.php') as $lang_filename) {
            $lang_filename = str_replace(WDT_ROOT_PATH . 'source/lang/', '', $lang_filename);
            $name = ucwords(str_replace('_', ' ', $lang_filename));
            $name = str_replace('.inc.php', '', $name);
            $languages[] = array('file' => $lang_filename, 'name' => $name);
        }

        return $languages;
    }

    /**
     * Returns languages as assoc array
     */
    public static function getArrInterfaceLanguages()
    {
        $newArrLang = [];
        $languages= self::getInterfaceLanguages();

        foreach ($languages as $language) {
            $newArrLang[$language['name']] = $language['file'];
        }

        return $newArrLang;
    }

    /**
     * Returns system fonts
     */
    public static function wdtGetSystemFonts()
    {
        $systemFonts = array(
            'Georgia, serif',
            'Palatino Linotype, Book Antiqua, Palatino, serif',
            'Times New Roman, Times, serif',
            'Arial, Helvetica, sans-serif',
            'Impact, Charcoal, sans-serif',
            'Lucida Sans Unicode, Lucida Grande, sans-serif',
            'Tahoma, Geneva, sans-serif',
            'Verdana, Geneva, sans-serif',
            'Courier New, Courier, monospace',
            'Lucida Console, Monaco, monospace'
        );

        $systemFonts = apply_filters('wpdatatables_get_system_fonts', $systemFonts);

        return $systemFonts;
    }

}

<?php

use \Firebase\JWT\JWT;

defined('ABSPATH') or die('Access denied.');

class WPDataTable_Google_Sheet
{
    public $privateKeyID;
    public $privateKey;
    public $clientEmail;
    public $clientID;

    public function __construct()
    {
        $googleSettings = get_option('wdtGoogleSettings');

        if ($googleSettings and isset($googleSettings['private_key_id'], $googleSettings['private_key'], $googleSettings['client_email'], $googleSettings['client_id'])) {
            $this->privateKeyID = $googleSettings['private_key_id'];
            $this->privateKey = $googleSettings['private_key'];
            $this->clientEmail = $googleSettings['client_email'];
            $this->clientID = $googleSettings['client_id'];
        }
    }

    /**
     *  Get Google Service account token
     * @param string|array $credential
     * @throws WDTException
     */
    public function getToken(array $credential = [])
    {
        if (!isset($credential['client_email'], $credential['private_key'])) {

            throw new WDTException('Client email & private key are not set.');
        }

        $payload = array(
            "iss" => $credential['client_email'],
            "scope" => 'https://www.googleapis.com/auth/drive',
            "aud" => 'https://oauth2.googleapis.com/token',
            "exp" => time() + 3600,
            "iat" => time(),
        );

        $jwt = JWT::encode($payload, $credential['private_key'], 'RS256');

        $args = array(
            'headers' => array(),
            'body' => array(
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            )
        );

        $googleResponse = wp_remote_post('https://oauth2.googleapis.com/token', $args);

        if (!is_wp_error($googleResponse) && isset($googleResponse['response']['code']) && $googleResponse['response']['code'] == 200) {
            return array(TRUE, json_decode($googleResponse['body'], TRUE));
        } else {
            $errorMsg = isset($googleResponse['response']['message']) ? json_encode($googleResponse['response']['message']) : "Google Response for token authorization have failed";
            throw new WDTException($errorMsg);
        }
    }

    /**
     *  Google Token Validation
     * @param string|array $token
     */
    public function wdtTokenValidationChecker($token = '')
    {
        if (is_array($token) && isset($token['access_token'])) {
            if (empty($token['access_token'])) return new WDTException('Empty access_token');
            $request = wp_remote_get('https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=' . $token['access_token']);
        }

        if (isset($request['response']['code']) && $request['response']['code'] == 200) {
            return array(TRUE, $request['body']);
        } else {
            $errorMsg = isset($request['response']['message']) ? json_encode($request['response']['message']) : "Google validation token is false ";
            throw new WDTException($errorMsg);
        }
    }

    /**
     * Get data from Google Spreadsheet
     * @param string $spreadsheetsURL
     * @param string|array $credentials
     * @param string|array $token
     * @throws WDTException
     */
    public function getData($spreadsheetsURL, $credentials, $token)
    {
        $spreadsheetID = WDTTools::getGoogleSpreadsheetID($spreadsheetsURL);
        $sheetID = WDTTools::getGoogleWorksheetsID($spreadsheetsURL);
        if ($credentials && $token && time() > $token['expires_in']) {
            $credentials = $this->convertPrivateKeyFormat($credentials);
            $newToken = $this->getToken($credentials);
            if ($newToken[0]) {
                $newToken[1]['expires_in'] = time() + $newToken[1]['expires_in'];
                $token = $newToken[1];
                update_option('wdtGoogleToken', $newToken[1]);
            } else {
                return array(FALSE, "False credentials");
            }
        }

        $isValid = $this->wdtTokenValidationChecker($token);
        $worksheetsName = '';
        if ($isValid[0]) {
            $requestGoogleSheetMetaData = wp_remote_get('https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheetID . '/?access_token=' . $token['access_token']);
            if (!is_wp_error($requestGoogleSheetMetaData) && isset($requestGoogleSheetMetaData['response']['code']) && $requestGoogleSheetMetaData['response']['code'] == 200) {
                $googleSheetMetaData = json_decode($requestGoogleSheetMetaData['body'], TRUE);
                foreach ($googleSheetMetaData['sheets'] as $sheet) {
                    if ($sheet['properties']['sheetId'] == $sheetID) {
                        $worksheetsName = $sheet['properties']['title'];
                    }
                }
                $googleSheetDataRequest = wp_remote_get('https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheetID . '/values/' . urlencode($worksheetsName) . '?access_token=' . $token['access_token']);

                if (!is_wp_error($googleSheetDataRequest) && isset($googleSheetDataRequest['response']['code']) && $googleSheetDataRequest['response']['code'] == 200) {
                    $googleSheetData = json_decode($googleSheetDataRequest['body'], TRUE);
                    return WDTTools::gsArrayToWDTArray($googleSheetData['values']);
                } else {
                    if (is_wp_error($googleSheetDataRequest)){
                        $errorMsg = $googleSheetDataRequest->get_error_message();
                    } else {
                        $errorMsg = isset($googleSheetDataRequest['response']) && isset($googleSheetDataRequest['response']['message']) ? json_encode($googleSheetDataRequest['response']['message']) : "Google sheet meta data is not sent";
                    }
                    throw new WDTException($errorMsg);
                }
            } else {
                if (is_wp_error($requestGoogleSheetMetaData)){
                    $errorMsg = $requestGoogleSheetMetaData->get_error_message();
                } else {
                    $errorMsg = isset($requestGoogleSheetMetaData['response']) && isset($requestGoogleSheetMetaData['response']['message']) ? json_encode($requestGoogleSheetMetaData['response']['message']) : "Google sheet meta data is not sent";
                }
                throw new WDTException($errorMsg);
            }
        } else {
            throw new WDTException('Token is not valid!');
        }
    }

    private function convertPrivateKeyFormat($credentials) {
        $removeBegin = str_replace('-----BEGIN PRIVATE KEY-----','', $credentials['private_key']);
        $removeEnd = str_replace('-----END PRIVATE KEY-----','', $removeBegin);
        if (strrpos($removeEnd,' ') != false){
            $replaceSpaces = str_replace(' ',PHP_EOL, $removeEnd);
            $credentials['private_key'] = '-----BEGIN PRIVATE KEY-----' .  $replaceSpaces  . '-----END PRIVATE KEY-----' . PHP_EOL;
        }
        return $credentials;
    }
}

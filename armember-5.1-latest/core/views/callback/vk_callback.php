<?php 
$vk_code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
if ($vk_code !== '') {
    
    $client_id = $_COOKIE['arm_vk_client_id'];
    $client_secret = $_COOKIE['arm_vk_client_secret'];
    $redirect_uri = $_COOKIE['arm_vk_redirect_uri'];
    
    $token_url = "https://api.vk.com/oauth/access_token";
    $encoded = urlencode('client_id') . '=' . urlencode($client_id) . '&';
    $encoded .= urlencode('client_secret') . '=' . urlencode($client_secret) . '&';
    $encoded .= urlencode('code') . '=' . urlencode($vk_code) . '&';
    $encoded .= urlencode('redirect_uri') . '=' . urlencode($redirect_uri);
    
    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    $response = curl_exec($ch);
    curl_close($ch);
    $resp = json_decode($response);

    if (isset($resp->access_token) && $resp->access_token !== '') {
        $access_token = $resp->access_token;
        $arm_vk_user_id = $resp->user_id;
        $arm_vk_email = $resp->email;
        
        $access = urlencode('uids') . '=' . urlencode($arm_vk_user_id) . '&';
        $access .= urlencode('access_token') . '=' . urlencode($access_token) . '&';
        $access .= urlencode('v') . '=' . urlencode('5.126') . '&';
        $access .= urlencode('fields') . '=' . urlencode('uid,first_name,last_name,screen_name,nickname,photo_200');
        $token_url = "https://api.vk.com/method/users.get?".$access;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $token_url ); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        $resp = json_decode($response);
        
        if(isset($resp->response[0])){
            $resp = $resp->response[0];
            
            $user_vk_data = array(
                'first_name' => $resp->first_name,
                'last_name' => $resp->last_name,
                'user_email' => $arm_vk_email,
                'display_name' => $resp->nickname,
                'user_profile_picture' => $resp->photo_200,
                'userId' => $resp->uid,
                'user_login' => $resp->screen_name,
            );

            echo "<script type='text/javascript' id='authorize'>";
            echo "arm_vk_token();";
            echo "function arm_vk_token(){";
            echo "window.opener.document.getElementById('arm_vk_user_data').value = '".json_encode($user_vk_data)."';";
            echo "window.close();";
            echo "window.opener.arm_VKAuthCallBack()";
            echo "}";
            echo "</script>";
        } else {
            echo "<script type='text/javascript' id='not_authorize'>";
            echo "window.close();";
            echo "</script>";
        }
    } else {
        echo "<script type='text/javascript' id='not_authorize'>";
        echo "window.close();";
        echo "</script>";
    }
    
    unset($_COOKIE['arm_vk_client_id']);
    unset($_COOKIE['arm_vk_client_secret']);
    unset($_COOKIE['arm_vk_redirect_uri']);
}

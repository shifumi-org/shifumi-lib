<?php
$insta_code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
if ($insta_code !== '') {
	require_once ('../../../lib/instagram/src/Client.php');
	$client_id = $_COOKIE['arm_insta_client_id'];
	$client_secret = $_COOKIE['arm_insta_client_secret'];
	$redirect_uri = $_COOKIE['arm_insta_redirect_uri'];

	$OBJ_INSTAGRAM = new Andreyco\Instagram\Client(array(
		'apiKey'      => $client_id,
		'apiSecret'   => $client_secret,
		'apiCallback' => $redirect_uri,
	));

	$data = $OBJ_INSTAGRAM->getOAuthToken($_GET['code']);

	$user_insta_data = array(
		'username' => $data->user->username,
		'full_name' => $data->user->full_name,
		'display_name' => $data->user->full_name,
		'user_profile_picture' => $data->user->profile_picture,
		'userId' => $data->user->id,
	);

	echo "<script type='text/javascript' id='authorize'>";
	echo "function arm_insta_token(){";
	echo "window.opener.document.getElementById('arm_insta_user_data').value = '".json_encode($user_insta_data)."';";
	echo "window.close();";
	echo "window.opener.arm_InstaAuthCallBack()";
	echo "}";
	echo "arm_insta_token();";
	echo "</script>";
}
else {
	echo "<script type='text/javascript' id='not_authorize'>";
	echo "window.close();";
	echo "</script>";
}

unset($_COOKIE['arm_insta_client_id']);
unset($_COOKIE['arm_insta_client_secret']);
unset($_COOKIE['arm_insta_redirect_uri']);
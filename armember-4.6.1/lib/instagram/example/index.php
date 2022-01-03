<?php

// ini_set('display_errors', 'off');

//require '../vendor/autoload.php';

require '../src/Client.php';

$INSTAGRAM_CLIENT_ID = "d57321ae59aa4847896450424f59b53c";
$INSTAGRAM_CLIENT_SECRET = "2997cb2c11b44ef88da8c7d3f1332588 ";

$instagram = new Andreyco\Instagram\Client(array(
  'apiKey'      => $INSTAGRAM_CLIENT_ID,
  'apiSecret'   => $INSTAGRAM_CLIENT_SECRET,
  'apiCallback' => 'http://hardik.reputetest.local/sociamonials/instagram/Instagram-for-PHP-master/example/index.php',
  'scope'      => array('basic', 'comments', 'likes'),
));

// create login URL
$state = md5(time());
$loginUrl = $instagram->getLoginUrl(array('basic'), $state);

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instagram - OAuth Login</title>
    <link rel="stylesheet" type="text/css" href="assets/style.css">
    <style>
      .login {
        display: block;
        font-size: 20px;
        font-weight: bold;
        margin-top: 50px;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <header class="clearfix">
        <h1>Instagram <span>display your photo stream</span></h1>
      </header>
      <div class="main">
        <ul class="grid">
          <li><img src="assets/instagram-big.png" alt="Instagram logo"></li>
          <li>
            <a class="login" href="<?php echo $loginUrl ?>">» Login with Instagram</a>
            <h>Use your Instagram account to login.</h4>
          </li>
        </ul>
        <!-- GitHub project -->
        <footer>
          <p>created by <a href="https://github.com/cosenary/Instagram-PHP-API">cosenary's Instagram class</a>, available on GitHub</p>
        </footer>
      </div>
    </div>
  </body>
</html>

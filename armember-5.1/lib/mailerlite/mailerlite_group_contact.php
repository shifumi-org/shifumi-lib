<?php
require_once 'vendor/autoload.php';


$mailerlitegroupsApi = (new \MailerLiteApi\MailerLite($api_key))->groups();

/*
$mailerlitegroups = $mailerlitegroupsApi->get(); 


try
{

	foreach ($mailerlitegroups as $mailerlitegrouplist) 
	{
		
		$mailerlitegrouplist_id = $mailerlitegrouplist->id;
			
	}

} catch (Exception $ex) {
	
}
*/

?>
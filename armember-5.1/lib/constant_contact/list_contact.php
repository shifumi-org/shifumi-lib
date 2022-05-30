<?php
require_once 'src/Ctct/autoload.php';

use Ctct\ConstantContact;
use Ctct\Components\Contacts\Contact;
use Ctct\Components\Contacts\ContactList;
use Ctct\Components\Contacts\EmailAddress;
use Ctct\Exceptions\CtctException;

define("APIKEY", $api_key);
if(isset($user) and $user != '')
	define("ACCESS_TOKEN", $user);
else
	define("ACCESS_TOKEN", $access_token);
 
$cc = new ConstantContact(APIKEY);

/*attempt to fetch lists in the account, catching any exceptions and printing the errors to screen*/
try{
    $lists = $cc->getLists(ACCESS_TOKEN);
	
	foreach ($lists as $list) {
		if(strtolower($list->name) == strtolower(@$list_name)) {
			$list_id = $list->id;
		}
	}
						
} catch (CtctException $ex) {
    foreach ($ex->getErrors() as $error) {
   
    }     
}
?>
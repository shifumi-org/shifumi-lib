<?php
namespace Ctct\Util;

use Ctct\Exceptions\CTCTException;
use Ctct\Util\RestClientInterface;
use Ctct\Util\CurlResponse;

/**
 * Wrapper for curl HTTP request
 *
 * @package     Util
 * @author         Constant Contact
 */
class RestClient implements RestClientInterface
{
    /**
     * Make an Http GET request
     * @param $url - request url
     * @param array $headers - array of all http headers to send
     * @return array - array of the response body, http info, and error (if one exists)
     */
    public function get($url, array $headers)
    {
        return self::httpRequest($url, "GET", $headers);
    }
    
    /**
     * Make an Http POST request
     * @param $url - request url
     * @param array $headers - array of all http headers to send
     * @param $data - data to send with request
     * @return array - array of the response body, http info, and error (if one exists)
     */
    public function post($url, array $headers = array(), $data = null)
    {
        return self::httpRequest($url, "POST", $headers, $data);
    }
    
    /**
     * Make an Http PUT request
     * @param $url - request url
     * @param array $headers - array of all http headers to send
     * @param $data - data to send with request
     * @return array - array of the response body, http info, and error (if one exists)
     */
    public function put($url, array $headers = array(), $data = null)
    {
        return self::httpRequest($url, "PUT", $headers, $data);
    }
    
    /**
     * Make an Http DELETE request
     * @param $url - request url
     * @param array $headers - array of all http headers to send
     * @param $data - data to send with request
     * @return array - array of the response body, http info, and error (if one exists)
     */
    public function delete($url, array $headers = array())
    {
        return self::httpRequest($url, "DELETE", $headers);
    }
    
    /**
     * Make an Http request
     * @param $url - request url
     * @param array $headers - array of all http headers to send
     * @param $data - data to send with the request
     * @throws CTCTException - if any errors are contained in the returned payload
     * @return CurlResponse
     */
    private static function httpRequest($url, $method, array $headers = array(), $data = null)
    {   
		$temp_header1 = @explode(':', $headers[0]);
		$temp_header2 = @explode(':', $headers[1]);	
		$temp_header3 = @explode(':', $headers[2]);
			
		$new_header = array($temp_header1[0] => $temp_header1[1], $temp_header2[0]=>$temp_header2[1], $temp_header3[0]=>$temp_header3[1] );		

		if( $method == 'GET' ) {
			$data_array = parse_str($data, $data_array);
						
			$data_array['sslverify'] = false;
			$data_array['redirection'] = 5;
			$data_array['httpversion'] = '1.0';
			$data_array['timeout']	= 90;
			$data_array['blocking']	= true;
			$data_array['headers']	= @$new_header;
			$data_array['user-agent'] = "ConstantContact Appconnect PHP Library v1.0";
			$data_array['method'] = $method;		
			
			$response2 = wp_remote_get( $url, $data_array );
						
		} else if( $method == 'POST' ) { 
			$data_array = parse_str($data, $data_array);
			 

			$response2 = wp_remote_post( $url, array(
								'method' => 'POST',
								'timeout' => 90,
								'redirection' => 5,
								'httpversion' => '1.0',
								'blocking' => true,
								'sslverify' => false,
								'user-agent' => "ConstantContact Appconnect PHP Library v1.0",
								'headers' => @$new_header,
								'body' => $data
								)
							);
		}else if( $method == 'PUT' ) { 
			$data_array = parse_str($data, $data_array);

			$response2 = wp_remote_post( $url, array(
								'method' => 'PUT',
								'timeout' => 90,
								'redirection' => 5,
								'httpversion' => '1.0',
								'blocking' => true,
								'sslverify' => false,
								'user-agent' => "ConstantContact Appconnect PHP Library v1.0",
								'headers' => @$new_header,
								'body' => $data
								)
							);
		}
		$response = (object) $response2;
	
		// check if any errors were returned
        $body = json_decode($response->body, true);		
		
        return $response;
    }
}
?>
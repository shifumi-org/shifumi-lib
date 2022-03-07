<?php
/**
 * Tumblr OAuth class
 */
require 'OAuth.php';

class TumblrOAuth {
	
	protected $auth_response = [];	

  protected $params = [];

  protected $parts = '';

  protected $method = '';
      
  protected $tumblr_consumer_key = '';	

  protected $tumblr_consumer_secret = ''; 

  /**
   * Set API URLS
  */
 	  function authenticateURL()  { return 'https://api.tumblr.com/v2/user/info'; }
  	function accessTokenURL() { return 'https://www.tumblr.com/oauth/access_token'; }
  	function requestTokenURL() { return 'https://www.tumblr.com/oauth/request_token'; }
  	function SignatureMethod() { return 'HMAC-SHA1';}
   
   /**
   * construct TumblrOAuth object
   */
    function __construct( $consumer_key, $consumer_secret ) 
    {
      $this->tumblr_consumer_key = $consumer_key; 
    	$this->tumblr_consumer_secret = $consumer_secret;
  	}

  	function Request_Token( $callback_url )
    {
      $this->parts = $this->requestTokenURL();
      $oauth_nonce = $this->OAuthNonce();
     	$oauth_version = $this->OAuthVersion();
      $signature_method = $this->SignatureMethod();  
      $method = $this->HttpMethod();                      
      $this->method = $method;
	    $url = $this->requestTokenURL();
	    $token= "";

	    $consumer=array( "key"=>$this->tumblr_consumer_key,
	                     "secret" => $this->tumblr_consumer_secret,
	                   ); 

      $this->params = array( 'oauth_version' => $oauth_version,
                             'oauth_nonce' => $oauth_nonce, 
                             'oauth_timestamp' =>time(),
                             'oauth_consumer_key' => $this->tumblr_consumer_key,
                             'oauth_callback' => $callback_url,
                             'oauth_signature_method' => $signature_method,
                           );

	    $iparams['oauth_version'] = '"'.$oauth_version .'"';
	    $iparams['oauth_nonce'] = '"'.$oauth_nonce.'"'; 
	    $iparams['oauth_timestamp'] ='"'.time().'"';
	    $iparams['oauth_consumer_key'] = '"'.$this->tumblr_consumer_key.'"';
	    $iparams['oauth_callback'] = '"'.$this->urlencode_rfc3986( $callback_url).'"';
	    $iparams['oauth_signature_method'] = '"'.$signature_method.'"' ;
	    $iparams['oauth_signature'] = '"'.$this->urlencode_rfc3986( $this->build_signature( $consumer, $token ) ).'"'; 
	  
      $headers = $this->Build_Header($iparams);
	    
	    $parameters = array( 'oauth_callback' => $callback_url );
	 
	    $response= $this->curl_call($url, $method , $parameters , $headers);
	    parse_str($response,$this->auth_response);

      return $this->auth_response; 
    }

    function getAccessToken( $consumer_token,$consumer_token_secret,$oauth_verifier ) 
    {
      $this->parts = $this->accessTokenURL();
      $oauth_nonce = $this->OAuthNonce();
      $oauth_version = $this->OAuthVersion();
      $signature_method = $this->SignatureMethod();  
      $method = $this->HttpMethod();    
      $this->method = $method;                   
      $url = $this->accessTokenURL();
    
      $this->params = array( 'oauth_version' => $oauth_version,
                             'oauth_nonce' => $oauth_nonce, 
                             'oauth_timestamp' =>time(),
                             'oauth_consumer_key' => $this->tumblr_consumer_key,
                             'oauth_token' => $consumer_token,
                             'oauth_verifier' => $oauth_verifier,
                             'oauth_signature_method' => $signature_method,
                           );

      $token=array( "key"=>$consumer_token,
                    "secret" => $consumer_token_secret,
                  );

      $consumer=array( "key"=>$this->tumblr_consumer_key,
                       "secret" => $this->tumblr_consumer_secret,
                     ); 

      $iparams['oauth_version'] = '"'.$oauth_version .'"';
      $iparams['oauth_nonce'] = '"'.$oauth_nonce.'"'; 
      $iparams['oauth_timestamp'] ='"'.time().'"';
      $iparams['oauth_consumer_key'] = '"'.$this->tumblr_consumer_key.'"';
      $iparams['oauth_token']= '"'.$consumer_token.'"';
      $iparams['oauth_verifier']= '"'.$oauth_verifier.'"';
      $iparams['oauth_signature_method'] = '"'.$signature_method.'"' ;
      $iparams['oauth_signature'] = '"'.$this->urlencode_rfc3986($this->build_signature( $consumer, $token)).'"'; 

      $headers = $this->Build_Header($iparams);
            
      $parameters = array( 'oauth_token'=> $consumer_token,
                          'oauth_verifier' => $oauth_verifier 
                        );
     
      $response = $this->curl_call($url, $method , $parameters , $headers);
      parse_str( $response,$this->auth_response );
      return $this->auth_response;
    }

    function get( $consumer_token , $consumer_token_secret )
    {
      $method = 'GET';
      $this->method = $method;
      $this->parts = $this->authenticateURL();
      $oauth_nonce = $this->OAuthNonce();
      $oauth_version = $this->OAuthVersion();
      $signature_method = $this->SignatureMethod();  
      $url = $this->authenticateURL();
          
      $this->params = array( 'oauth_version' => $oauth_version,
                             'oauth_nonce' => $oauth_nonce, 
                             'oauth_timestamp' =>time(),
                             'oauth_consumer_key' => $this->tumblr_consumer_key,
                             'oauth_token' => $consumer_token,
                             'oauth_signature_method' => $signature_method,
                           );
                       
      $token = array( "key" => $consumer_token,
                      "secret" => $consumer_token_secret,
                      "call_back_url" =>"",
                    );
     
      $consumer = array( "key" => $this->tumblr_consumer_key,
                       "secret" => $this->tumblr_consumer_secret,
                       "call_back_url" =>"",
                    ); 

      $iparams['oauth_version'] = '"'.$oauth_version .'"';
      $iparams['oauth_nonce'] = '"'.$oauth_nonce.'"'; 
      $iparams['oauth_timestamp'] ='"'.time().'"';
      $iparams['oauth_consumer_key'] = '"'.$this->tumblr_consumer_key.'"';
      $iparams['oauth_token'] = '"'.$consumer_token.'"';
      $iparams['oauth_signature_method'] = '"'.$signature_method.'"' ;
      $iparams['oauth_signature'] = '"'.$this->urlencode_rfc3986( $this->build_signature( $consumer, $token) ).'"'; 

      $headers = $this->Build_Header( $iparams );

      $parameters =array();
                
      return json_decode( $this->curl_call( $url, $method , $parameters , $headers ) );     

    }

    function curl_call( $url, $method , $parameters , $headers){

      $curl = new Curl();

      $response= $curl->request( $url, $method , $parameters , $headers );

      return $response;

    }

    /* Build Header */

    function Build_Header( $iparams ) 
    {
      $oauth_header = array();
      foreach( $iparams as $key => $value ) {
          if ( strpos($key, "oauth") !== false ) { 
             $oauth_header []= $key ."=".$value;
          }
      }
      $oauth_header = "OAuth ". implode( ",", $oauth_header );

      $headers["Authorization"] = $oauth_header;

      return $headers;     
    }
    /* Set API METHOD  */

    function HttpMethod() { return 'POST'; }

    /* Set Version */

    function OAuthVersion() { return '1.0'; }

    /* set oAuthNonce */

    function OAuthNonce() { return md5( microtime().mt_rand() ); }
    	
    function urlencode_rfc3986($input) 
    { 
        if( is_array( $input ) ) {
          return array_map( array( $this,'urlencode_rfc3986' ),$input );
        } elseif ( is_scalar( $input )) {
            return str_replace('+', ' ', str_replace( '%7E', '~', rawurlencode( $input ) ) );
        } else {
            return '';
        }
   	}

    function urldecode_rfc3986( $string )
    {
        return $this->urldecode( $string );
    }
    
    function build_signature( $consumer, $token)
	  {     
        $base_string = $this->get_signature_base_string();
        $key_parts = array( $consumer['secret'], $token ? $token['secret'] : '' );
        $key_parts =$this->urlencode_rfc3986( $key_parts );
        $key = implode( '&', $key_parts );
        return base64_encode( hash_hmac( 'sha1', $base_string, $key, true ) );
    }   

    function get_signature_base_string()
    {
        $parts = array( $this->get_normalized_http_method(),
                        $this->get_normalized_http_url(),
                        $this->get_signable_parameters()
                      );
        $parts = $this->urlencode_rfc3986( $parts) ;
     
        return implode( '&', $parts );
    }

    function get_normalized_http_method()
    {
        return strtoupper( $this->method );
    }

    function get_normalized_http_url()
    {   
        $parts = parse_url( $this->parts );
        $scheme = ( isset( $parts['scheme'] ) ) ? $parts['scheme'] : 'http';
        $port   = ( isset( $parts['port'] ) ) ? $parts['port'] : ( ( $scheme == 'https' ) ? '443' : '80' );
        $host   = ( isset( $parts['host'] ) ) ? strtolower($parts['host'] ) : '';
        $path   = ( isset( $parts['path'] ) ) ? $parts['path'] : '';
        
        if ( ( $scheme == 'https' && $port != '443' ) || ( $scheme == 'http' && $port != '80' ) ) {
            $host = "$host:$port";
        }
        return "$scheme://$host$path";
    }
    
    function get_signable_parameters()
    {
        // Grab all parameters
      
        $params= $this->params;

        // Remove oauth_signature if present
        // Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
        if ( isset($params['oauth_signature'] ) ) {
             unset($params['oauth_signature']);
        }
        return $this->build_http_query( $params );
    }

    function build_http_query( $params )
    {
        if ( !$params ) {
            return '';
        }
        
        // Urlencode both keys and values
        $keys   = $this->urlencode_rfc3986( array_keys( $params ) );
        $values = $this->urlencode_rfc3986( array_values( $params ) );
        $params = array_combine( $keys, $values );
        
        // Parameters are sorted by name, using lexicographical byte value ordering.
        // Ref: Spec: 9.1.1 (1)
        uksort( $params, 'strcmp' );

        $pairs = array();

        foreach ( $params as $parameter => $value ) {
            if( is_array( $value ) ) {
                // If two or more parameters share the same name, they are sorted by their value
                // Ref: Spec: 9.1.1 (1)
                // June 12th, 2010 - changed to sort because of issue 164 by hidetaka
                sort( $value, SORT_STRING );
                foreach( $value as $duplicate_value ) {
                    $pairs[] = $parameter . '=' . $duplicate_value;
                }
            } else {
                $pairs[] = $parameter . '=' . $value;
            }
        }
        // For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
        // Each name-value pair is separated by an '&' character (ASCII code 38)
        return implode( '&', $pairs );
    }
    
}
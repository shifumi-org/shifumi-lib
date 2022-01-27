<?php


class Curl 
{
    /**
    * Default curl options
    *
    * These defaults options can be overwritten when sending requests.
    *
    * See setCurlOptions()
    *
    * @var array
    */
    protected $curlOptions = [
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLINFO_HEADER_OUT    => true,
        CURLOPT_ENCODING       => 'identity',
        // phpcs:ignore
        CURLOPT_USERAGENT      => 'Hybridauth, PHP Social Authentication Library (https://github.com/hybridauth/hybridauth)',
    ];

    /**
    * Method request() arguments
    *
    * This is used for debugging.
    *
    * @var array
    */
    protected $requestArguments = [];

    /**
    * Default request headers
    *
    * @var array
    */
    protected $requestHeader = [
        'Accept'          => '*/*',
        'Cache-Control'   => 'max-age=0',
        'Connection'      => 'keep-alive',
        'Expect'          => '',
        'Pragma'          => '',
        ];

    /**
    * Raw response returned by server
    *
    * @var string
    */
    protected $responseBody = '';

    /**
    * Response HTTP status code
    *
    * @var integer
    */
    
    public function request( $uri, $method = 'GET', $parameters = [], $headers = [], $multipart = false )
    {   

        $this->requestHeader = array_replace( $this->requestHeader, (array) $headers );
        
        $this->requestArguments = [
            'uri' => $uri,
            'method' => $method,
            'parameters' => $parameters,
            'headers' => $this->requestHeader,
        ];


        $curl = curl_init();
        
        switch ($method) {
            case 'GET':
                unset( $this->curlOptions[CURLOPT_POST] );
                unset( $this->curlOptions[CURLOPT_POSTFIELDS] );

                $uri = $uri . (strpos($uri, '?') ? '&' : '?') . http_build_query($parameters);
                break;
        
            case 'POST':
                $body_content = $multipart ? $parameters : http_build_query( $parameters );

                if ( isset($this->requestHeader['Content-Type'] )
                    && $this->requestHeader['Content-Type'] == 'application/json'

                ) {
                    $body_content = json_encode($parameters);
                }

                if ( $method === 'POST' ) {
                    $this->curlOptions[CURLOPT_POST] = true;
                } else {
                    $this->curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
                }

                $this->curlOptions[CURLOPT_POSTFIELDS] = $body_content;
                break;
        }
           
        $this->curlOptions[CURLOPT_URL]            = $uri;
        $this->curlOptions[CURLOPT_HTTPHEADER]     = $this->prepareRequestHeaders();
        $this->curlOptions[CURLOPT_HEADERFUNCTION] = [ $this, 'fetchResponseHeader' ];
    
        foreach ( $this->curlOptions as $opt => $value ) {
            curl_setopt($curl, $opt, $value);
        }
            
        $response = curl_exec($curl);
                     
        $this->responseBody = $response;
        
        curl_close( $curl );
        return $this->responseBody;
    }

     /**
    * Reset curl options
    *
    * @param array $curlOptions
    */
    public function setCurlOptions( $curlOptions )
    {
        foreach ( $curlOptions as $opt => $value ) {
            $this->curlOptions[ $opt ] = $value;
        }
    }
    
     /**
    * Fetch server response headers
    *
    * @param mixed  $curl
    * @param string $header
    *
    * @return integer
    */
    protected function fetchResponseHeader( $curl, $header )
    {   
        $pos = strpos( $header, ':' );

        if (! empty( $pos ) ) {
            $key   = str_replace( '-', '_', strtolower( substr( $header, 0, $pos ) ) );

            $value = trim( substr( $header, $pos + 2 ) );
            $this->responseHeader[ $key ] = $value;
        }

        return strlen( $header );
    }

    /**
    * Convert request headers to the expect curl format
    *
    * @return array
    */
    protected function prepareRequestHeaders()
    {
        $headers = [];

        foreach ($this->requestHeader as $header => $value) {
            $headers[] = trim($header) .': '. trim($value);
        }
    
        return $headers;
    }
}   

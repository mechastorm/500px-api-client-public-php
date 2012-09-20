<?php
/**
 * A simple PHP client to access the 500px API points 
 * that does is public (that does not require a user access token).
 * To use the API with a 500px's user access token please refer to
 * the official documentation
 *
 * Usage example to get photos:
 * <code>
 * // API options
 * $options = array(
 *		'key' => 'YOUR API KEY',
 *		'secret' => 'YOUR SECRET KEY',
 * );
 *
 * // Initialize Cient
 * $five00Px = Five00PxPubClient::factory($options);
 * $tags = array(
 * 		// Tag 1
 * 		// Tag 2
 * );
 *
 * // Search Params for the Photo
 * $apiParams = array (
 * 		'tag'   => implode(',', $tags),
 *		'term'	=> 'Search Term',
 *		'rpp'	=> 40,
 * );
 *
 * // Do the API call
 *	try {
 *		$photos = $five00Px->api('photos/search', $params);
 *	} catch(Exception $e) {
 *		echo 'Error in call ' . $e->getMessage();
 *	}
 *
 * var_dump($photos);
 * </code>
 * 
 * @see  https://github.com/500px/api-documentation
 * @requires php_curl
 * @author Shih Oon Liong <github@mechaloid.com>
 */
class Five00PxPubClient {
		
	const
		HTTP_METHOD_GET = 'GET',
		HTTP_METHOD_POST = 'POST',
		HTTP_RESPONSE_SUCCESS = 200,
		API_BASE_URL = 'https://api.500px.com';
	
	var
	    $logger = null,
		$version = 1,
		$defaultParams = array();

	/**
	 * Constructor
	 * @param array $options Initialization options. The array must contain the follow indexes
	 * 'key' => Your 500px Application Key
	 * 'secret' => Your 500px Secret Key
	 * 'version' => The version of the API. Default to 1
	 */
	public function __construct($options) {
	    // Set Logger
	    if ( array_key_exists('logger', $options) 
				&& ! empty ($options['logger']) ) {
	        $this->logger = $options['logger'];
	    }
			
		$this->consumerKey = $options['key'];
		$this->consumerSecret = $options['secret'];
		$this->defaultParams = array(
			'consumer_key' => $this->consumerKey,
			'consumer_secret' => $this->consumerSecret,
		);
		
		// Set API version
		if ( array_key_exists('version', $options) ) {
			$this->version = $options['version'];
		}				
	}
		
		public static function factory($options=array()) {
			$class = __CLASS__;
			$instance = new $class($options);
			
			return $instance;
		}
		
	/**
	 * Log messages
	 */
    private function logMessage() {
		$logger = $this->logger;
		if ($logger == null) {
			return;
		}
		
		$arg_list = func_get_args();
		call_user_func_array($logger, $arg_list);
	}
		
	/**
	 * Do an API call on 500px
	 * @param type $apiUrl
	 * @return mixed
	 * @throws Exception 
	 */
	public function api($apiUrlCall, $userArgs=array(), $method='GET') {
			
		// Set up Call Args
		$this->logMessage('Default Args', $this->defaultParams);
		$args = array_merge($userArgs, $this->defaultParams);
		$this->logMessage('API Args', $args);
		
		$apiUrl = self::API_BASE_URL . '/v' . $this->version . '/' . $apiUrlCall;
		
		if ($method == self::HTTP_METHOD_GET) {
			$apiUrl .= '?' . http_build_query($args);
		}
		
		$data = null;
		try {
			$ch = curl_init();
			
			// Set CURL options
			curl_setopt($ch, CURLOPT_URL, $apiUrl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			
			$response = curl_exec($ch);
			$metadata = curl_getinfo($ch);
			
			if ( $response === false ) {
				throw new Exception('Invalid Response received');
			}
			
			// Check Response
			if( array_key_exists('http_code', $metadata) 
				&& (int)$metadata['http_code'] == self::HTTP_RESPONSE_SUCCESS ) {
				$data = json_decode($response);
				curl_close($ch);	
			} else {
				
				// Failed (non-success) Response received
				$errorMsg = 'Response error - Error encountered: ' 
						  . '[' . $metadata['http_code'] . '] '
					      . '(Request: ' . $apiUrl . ') ';
				if ( ! empty($response) ) {
					$data = json_decode($response);
					$errorMsg .= $data->error;
				}
				
				// Throw exception
				throw new Exception($errorMsg);
					
			}
		} catch (Exception $e) { 
			// Failed to do curl call
			throw new Exception($e->getMessage());
		}

		return $data;

	}
}

?>

500px-api-client-public-php
===========================
@requires php_curl
@author Shih Oon Liong <github@mechaloid.com>

A simple PHP client to access the 500px API points  that does is public (that does not require a user access token).
To use the API with a 500px's user access token please refer to the official documentation

@see  https://github.com/500px/api-documentation

Usage example to get photos:
<code>

// API options
$options = array(
	'key' => 'YOUR API KEY',
	'secret' => 'YOUR SECRET KEY',
);

// Initialize Cient
$five00Px = Five00PxPubClient::factory($options);
$tags = array(
	// Tag 1
	// Tag 2
);

// Search Params for the Photo
$apiParams = array (
	'tag'   => implode(',', $tags),
	'term'	=> 'Search Term',
	'rpp'	=> 40,
);

// Do the API call
try {
	$photos = $five00Px->api('photos/search', $params);
} catch(Exception $e) {
	echo 'Error in call ' . $e->getMessage();
}

var_dump($photos);

</code>
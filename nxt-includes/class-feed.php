<?php

if ( !class_exists('SimplePie') )
	require_once (ABSPATH . nxtINC . '/class-simplepie.php');

class nxt_Feed_Cache extends SimplePie_Cache {
	/**
	 * Create a new SimplePie_Cache object
	 *
	 * @static
	 * @access public
	 */
	function create($location, $filename, $extension) {
		return new nxt_Feed_Cache_Transient($location, $filename, $extension);
	}
}

class nxt_Feed_Cache_Transient {
	var $name;
	var $mod_name;
	var $lifetime = 43200; //Default lifetime in cache of 12 hours

	function __construct($location, $filename, $extension) {
		$this->name = 'feed_' . $filename;
		$this->mod_name = 'feed_mod_' . $filename;
		$this->lifetime = apply_filters('nxt_feed_cache_transient_lifetime', $this->lifetime, $filename);
	}

	function save($data) {
		if ( is_a($data, 'SimplePie') )
			$data = $data->data;

		set_transient($this->name, $data, $this->lifetime);
		set_transient($this->mod_name, time(), $this->lifetime);
		return true;
	}

	function load() {
		return get_transient($this->name);
	}

	function mtime() {
		return get_transient($this->mod_name);
	}

	function touch() {
		return set_transient($this->mod_name, time(), $this->lifetime);
	}

	function unlink() {
		delete_transient($this->name);
		delete_transient($this->mod_name);
		return true;
	}
}

class nxt_SimplePie_File extends SimplePie_File {

	function __construct($url, $timeout = 10, $redirects = 5, $headers = null, $useragent = null, $force_fsockopen = false) {
		$this->url = $url;
		$this->timeout = $timeout;
		$this->redirects = $redirects;
		$this->headers = $headers;
		$this->useragent = $useragent;

		$this->method = SIMPLEPIE_FILE_SOURCE_REMOTE;

		if ( preg_match('/^http(s)?:\/\//i', $url) ) {
			$args = array( 'timeout' => $this->timeout, 'redirection' => $this->redirects);

			if ( !empty($this->headers) )
				$args['headers'] = $this->headers;

			if ( SIMPLEPIE_USERAGENT != $this->useragent ) //Use default nxt user agent unless custom has been specified
				$args['user-agent'] = $this->useragent;

			$res = nxt_remote_request($url, $args);

			if ( is_nxt_error($res) ) {
				$this->error = 'nxt HTTP Error: ' . $res->get_error_message();
				$this->success = false;
			} else {
				$this->headers = nxt_remote_retrieve_headers( $res );
				$this->body = nxt_remote_retrieve_body( $res );
				$this->status_code = nxt_remote_retrieve_response_code( $res );
			}
		} else {
			if ( ! $this->body = file_get_contents($url) ) {
				$this->error = 'file_get_contents could not read the file';
				$this->success = false;
			}
		}
	}
}

<?php
/**
 * This driver proxying any missing local assets to a remote host. i.e; the production site. This has been created 
 * with WordPress in mind but could be adjusted to work with any other system.
 */
use Valet\Drivers\Specific\WordPressValetDriver;

class LocalValetDriver extends WordPressValetDriver {

	/** @var string The remote host to proxy requests to */
	const REMOTE_HOST = 'https://domain.com/';

	/** @var string Assets folder */
	const URI_PREFIX = '/wp-content/uploads/';

	/** @var bool Whether or not to load the current request remotely */
	private static $tryRemoteFallback = false;

	/**
	 * Checks whether we have the file on disk. If not, change the domain of any requests for files within the
	 * uploads directory to the remote domain.
	 *
	 * @param string $sitePath
	 * @param string $siteName
	 * @param string $uri
	 *
	 * @return bool|false|string
	 */
	public function isStaticFile( $sitePath, $siteName, $uri ) {

		$localFileFound = parent::isStaticFile( $sitePath, $siteName, $uri );

		if ( $localFileFound ) {
			return $localFileFound;
		}

		if ( self::stringStartsWith( $uri, self::URI_PREFIX ) ) {
			self::$tryRemoteFallback = true;

			return rtrim( self::REMOTE_HOST, '/' ) . $uri;
		}

		return false;
	}

	/**
	 * This method checks if the remote flag is set and, if so, redirects the request by setting the Location header.
	 *
	 * @param string $staticFilePath
	 * @param string $sitePath
	 * @param string $siteName
	 * @param string $uri
	 */
	public function serveStaticFile( $staticFilePath, $sitePath, $siteName, $uri ) : void {
		if ( self::$tryRemoteFallback ) {
			header( "Location: $staticFilePath" );
		} else {
			parent::serveStaticFile( $staticFilePath, $sitePath, $siteName, $uri );
		}
	}

	/**
	 * @param string $string
	 * @param string $startsWith
	 *
	 * @return bool
	 */
	private static function stringStartsWith( $string, $startsWith ) {
		return strpos( $string, $startsWith ) === 0;
	}

}
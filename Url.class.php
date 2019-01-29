<?php
class Url{
	/**
	 * 
	 *@var array 
	 * 
	 */
	private static $parts=array();
	
	/**
	 * contrutor Url
	 * @param array
	 * 
	 */
	 public function __construct(array $url_options){
	 	foreach($url_options as $k=>$v){
	 		switch ($k){
	 			case "force_https":
	 				$force_ssl=$v;
	 			break;
	 			case "server":
	 				$s=$v;
	 			break;
	 			case "forwarded_host":
	 				$forwarded_host=$v;
	 			break;
	 			case "ssl_port":
	 				$ssl_port=$v;
	 			break;
	 		}
	 	}
	 	if($force_ssl){
	 		$this->force_ssl($s,$forwarded_host,$ssl_port);
	 	}
	 	SELF::setParts();
	 }
	 
	 /**
	  * 
	  * 
	  * @return void
	  */
	  private static function setParts(): void
	  {
	  	$path = array();
		if (isset($_SERVER['REQUEST_URI'])) {
			$request_path = explode('?', $_SERVER['REQUEST_URI']);
		
			$path['base'] = rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/');
			$path['call_utf8'] = substr(urldecode($request_path[0]), strlen($path['base']) + 1);
			$path['call'] = utf8_decode($path['call_utf8']);
			if ($path['call'] == basename($_SERVER['PHP_SELF'])) {
		  		$path['call'] = '';
			}
			$path['call_parts'] = explode('/', $path['call']);
			if(empty($request_path[1])){
				SELF::$parts=$path;
			}
			else{
				$path['query_utf8'] = urldecode($request_path[1]);
				$path['query'] = utf8_decode(urldecode($request_path[1]));
				$vars = explode('&', $path['query']);
				foreach ($vars as $var) {
			  		$t = explode('=', $var);
			  		$path['query_vars'][$t[0]] = $t[1];
				}
				SELF::$parts=$path;
			}
		}
		else{
			SELF::$parts=$path;
		}
		
	  }
	  
	  /**
	  * 
	  * 
	  * @return array $parts
	  */
	  public static function getParts(): array
	  {
	  	return SELF::$parts;
	  }
	  
	  /**
	   * 
	   * @param array $s
	   * @param bool $use_forwarded_host
	   * @param int $ssl_port
	   * @return void
	   */
	  public function force_ssl($s, $use_forwarded_host,$ssl_port): void
	  {
	  	$ssl      = ( ! empty( $s['HTTPS'] ) && $s['HTTPS'] == 'on' );
		if(!$ssl){
			$sp       = strtolower( $s['SERVER_PROTOCOL'] );
			$protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . 's';
			$port     = $ssl_port;
			$port     = ( ( $port=='' ) || ( $port=='443' ) ) ? '' : ':'.$port;
			$host     = ( $use_forwarded_host && isset( $s['HTTP_X_FORWARDED_HOST'] ) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
			$host     = isset( $host ) ? $host : $s['SERVER_NAME'] . $port;
			$parsed_host = explode(":",$host);
			
			$https_url = $protocol . '://' . $parsed_host[0] . $port . $s['REQUEST_URI'];
			header("location:".$https_url);
			exit();
		}
	  }
}

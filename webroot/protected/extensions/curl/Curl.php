<?PHP
/**
 * Basis curl class met cookie ondersteuning
 * 
 * @author Han
 * @package MG
 * @subpackage Proxy
 */
class Curl extends CApplicationComponent {
	
	const GET_RAW_DATA = true;
	
	/**
	 * User Agent String, eventueel aangepast
	 * @var str
	 */
	public $user_agent;
	
	/**
	 * Disable ssl check, valideer certificaat of niet
	 * @var boolean
	 */
	private $disable_ssl = false;
	
	
	/**
	 * Private storage of cookie
	 * @var array|str
	 */
	private $cookie = array();
	
	
	/**
	 * Huidige CurlChannel, er kan in PHP maar 1 ding tegelijk dus dan kan dit. In Java kan dit niet
	 * @var resource
	 */
	protected $ch;
	
	/**
	 * Store URL
	 * @var str
	 */
	private $url;
	
	/**
	 * Referer
	 * @var str
	 */
	protected $referer;
	
	/**
	 * Use raw information for the request
	 * @var boolean
	 */
	private $raw;
	
	/**
	 * Initialiseer de Class
	 * @param mixed $user_agent		NULL geeft huidige user_agent anders je eigen
	 * @param boolean $disable_ssl		Disable ssl check
	 * @return void
	 */
	public function __construct($raw = false) {
		if($this->user_agent == null) 
			$this->user_agent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.13 (KHTML, like Gecko) Chrome/24.0.1284.0 Safari/537.13';
		
		$this->raw = $raw;
	}
	
	public static function str_insert($str, $replace) {
		$_ret = array();
		foreach($replace as $key => $value) {
			$_ret['{'.$key.'}'] = $value;
		}
		return strtr($str, $_ret);
	}
	
	/**
	 * Init A connection
	 * @param str $url 		URL of page
	 * @return resource
	 */
	private function init_call($url, $headers = array()) {
		$this->url = $url;
		
		$this->ch = curl_init();
		
		// Set referer tot de laatste
		if(!empty($this->referer)) {
			curl_setopt($this->ch,CURLOPT_REFERER,$this->referer);
		}
		// Nieuwe ref
		$this->referer = $url;
		
		// set URL
		curl_setopt($this->ch,CURLOPT_URL,$url);
		
		// Disable ssl
		if($this->disable_ssl) {
			curl_setopt ($this->ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt ($this->ch, CURLOPT_SSL_VERIFYPEER, false);
		} 
		
		// Set options
		curl_setopt($this->ch,CURLOPT_USERAGENT,$this->user_agent);
		curl_setopt($this->ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($this->ch,CURLOPT_HEADER,true);
                
                curl_setopt($this->ch,CURLOPT_ENCODING , "gzip");
		
		// parse headers
		$curl_headers = array();
		foreach($headers as $hk => $hv) {
			$curl_headers[] = $hk .':'.$hv;
		}
		
		if(count($headers) > 0) {
			curl_setopt($this->ch,CURLOPT_HTTPHEADER,$curl_headers);
		}
		
		// options
		curl_setopt($this->ch,CURLOPT_FOLLOWLOCATION,false);
		curl_setopt($this->ch,CURLOPT_AUTOREFERER,true);
		
		curl_setopt($this->ch,CURLOPT_CONNECTTIMEOUT,3);
		curl_setopt($this->ch,CURLOPT_TIMEOUT,20);
		return $this->ch;
	}
	
	/**
	 * 
	 * @param str $url		URL to get
	 * @param array $cookie	Set stored cookie for this session
	 * @return str		Content of website
	 */
	public function get($url, $headers = array(), $cookie = false) {
		
		$this->init_call($url, $headers);
	
		// Als cookie wel moet, store hem dan
		if($cookie) {
			curl_setopt($this->ch,CURLOPT_COOKIE,$this->getStoredCookie());
		}
		
		// Uitvoeren
		$parse = $this->exec($this->ch);
		
		// Cookie store?
		if($cookie === false) {
			$this->parseCookies($parse['headers']);
		}
		
		curl_close($this->ch);

		return $this->raw ? $parse : $parse['content'];
	}
	
	
	/**
	 * Post A form, with or withoud cookie
	 * @param str $url
	 * @param array $postdata
	 * @param boolean $cookie
	 * @return str
	 */
	public function post($url, $postdata, $headers = array(), $cookie = false) {
	
		// Initaliseer
		$this->init_call($url, $headers);
		
		// _FILES gebeuren
		$uploaded_files = array();
		if(isset($_FILES)) {
			// Groote en bestanden
			foreach($_FILES as $key => $val) {
				$nkey = str_replace('_','.',$key);
				$f = '@'.$this->handleUpload($val);
				$uploaded_files[] = ltrim($f,'@');
				$postdata[$nkey] = $f;
			}
		}
		
		// Als cookie wel moet, store hem dan
		if($cookie) {
			curl_setopt($this->ch,CURLOPT_COOKIE,$this->getStoredCookie());
		}
		
		curl_setopt($this->ch,CURLOPT_POST,1);
		curl_setopt($this->ch,CURLOPT_POSTFIELDS,$postdata);
		
		$parse = $this->exec($this->ch);
		
		// Cookie store?
		if($cookie === false) {
			$this->parseCookies($parse['headers']);
		}
		
		curl_close($this->ch);
		
		// Verwijder de bestanden weer
		foreach($uploaded_files as $v) {
			unlink($v);
		}
		
		return $this->raw ? $parse : $parse['content'];
	}
	
	/**
	 * Uitvoeren van operatie
	 * @return str Output of request
	 */
	private function exec() {
		
		$out = curl_exec($this->ch);
		
		$error = curl_error($this->ch);
		if(!empty($error)) {
			throw new CurlException($error.' ('.curl_errno($this->ch).')');		
		}
		
		$parse = $this->splitContent($out);
		
		return $parse;
	}
	
	/**
	 * handleTheUpload
	 * @param str $file
	 * @return str Filename
	 */
	private function handleUpload($file) {
		if(is_uploaded_file($file['tmp_name'])) {
			$file_org = end(explode('/',$_SERVER['SCRIPT_FILENAME'])); // kan afgevangen worden met getFile uit snelget 
			$dir = str_replace($file_org,'',$_SERVER['SCRIPT_FILENAME']); // getDir uit snelget, tijdelijk hier niet toegankelijk
			
			$dest = $dir.self::TEMP_UPLOAD_DIR.'/'.$file['name'];
			if(move_uploaded_file($file['tmp_name'],$dest)) {
				return $dest;
			} else {
				throw new Exception('Dest niet schrijven, dest: '.$dest);
			}
		} 
	}
	
	/**
	 * Get cookie array
	 * @return array
	 */
	public function getCookies() {
		return $this->cookie;
	}
	
	/**
	 * Voeg cookie toe aan array
	 * @param str $key	
	 * @param str $data	
	 * @return void
	 */
	public function addCookie($key,$data) {
		$explode = explode(';',$data);
		$this->cookie[$key] = trim($explode[0]);	
	}
	
	/**
	 * Verwijder cookie
	 * @param str $key
	 * @return void
	 */
	public function removeCookie($key) {
		unset($this->cookie[$key]);	
	}
	
	/**
	 * Reset en Set de class met array data
	 * @param array $data 
	 * @return void
	 */
	public function setCookies(array $data) {
		$this->cookie = $data;
	}
	
	/**
	 * Set referer
	 * @param str $ref
	 * @return str
	 */
	public function setReferer($ref) {
		if($ref != null) {
			$this->referer = $ref;
		}
	}
	
	/**
	 * Parse the header string en zet het in de cookie array voor later
	 * @param str $header
	 * @return void
	 */
	private function parseCookies($header) {
		if(isset($header['Set-Cookie'])) {
			$patt = '#Set-Cookie: ([a-z0-9_]+)=(.*)#i';
			preg_match_all($patt,$header['Set-Cookie'],$return);
			
			foreach($return[1] as $key => $id) {
				$this->addCookie($id,$return[2][$key]);
			}
		}
	}
	
	/**
	 * Get stored cookie in CURL format
	 * @return str
	 */
	private function getStoredCookie() {
		
		$ret = '';
		foreach($this->cookie as $key => $val) {
			$ret .= $key.'='.$val.';';
		}
		
		return rtrim($ret,';');
	}
	
	/**
	 * Split content in header en content deel
	 * @param str $content
	 * @return array (header,content)
	 */
	private function splitContent($data) {
		
		$patt = "#HTTP/1\.[01] ([0-9]+) (.*)\n#i";
		$match = preg_split($patt,$data);
	
		// Match error code
		preg_match_all($patt,$data,$codes);
		
		// Laatste
		$data = end($match);
		$ex = explode("\n",$data);
		
		$header 		= '';
		$content 		= '';
		$headersplit 	= true;
		
		foreach($ex as $v) {
			
			// Lege regel, dus dan is de rest content
			$x = trim($v);
			if(empty($x)) {
				$headersplit = false;	
			}
			
			if($headersplit === true) {
				$header .= $v."\n";
			} else {
				$content .= $v."\n";
			}
			
		}
		
		
		$ret = array();
		
		$headers = array();
		foreach(explode("\n", $header) as $h) {
			if(empty($h)) continue;
			
			$ex = explode(":", $h, 2);
			
			$headers[$ex[0]] = trim($ex[1]);
		}
		
		$ret['headers'] 	= $headers;
		$ret['content'] 	= trim($content);
		$ret['code']		= end($codes[1]);
		
		return $ret;
	}
	
}

class CurlException extends Exception {}
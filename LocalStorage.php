<?php 

class localStorage {

	protected $tmpdir = 'localstorage_temp';

	protected $items = array();

	protected $prefix = '.storage_';

	protected $extension = '.bin';

	private $last_error = null;
	
	private $encrypt_content = false;

	protected $_key = '#8![gY]X7%#;8ozVP/:m@_q\TMz,vJ~[WO.xoCA)+PK+#A\kfA9pa&@X}1$U!3Q';

	public function __construct()
	{
		/**
		 * default temp directory 
		 */
		$this->tmpdir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . $this->tmpdir;
	}

	private function getFileName( $filename = null )
	{
		if( null == $filename ) return;

		return $this->prefix . md5($filename) . $this->extension;
	}

	public function getDirectory()
	{
		return $this->tmpdir . DIRECTORY_SEPARATOR;
	}

	public function setTempDirectory( $dir )
	{
		$this->tmpdir = $dir;
	}

	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;
	}

	public function setExtension( $ext )
	{
		if(strpos($ext, '.') == -1 ) $ext = '.'. $ext;

		$this->extension = $ext;
	}

	public function encrypt( $encrypt = true )
	{
		$this->encrypt_content = $encrypt;
	}

	public function setEncryptionKey($key = null)
	{
		$this->_key = $key;
	}

	public function setItem($item, $value = null) 
	{
		if( null != $this->last_error ) return false;

		$this->items[ $item ] = $value;

		return true;
	}

	public function save()
	{
		if(!is_dir( $this->getDirectory() ) ) {

			try {
				mkdir( $this->getDirectory() );
			} catch( Exception $e ) {
				$this->last_error = 'temp_dir_cannot_be_created';
				return false;				
			}
		}

		if(!is_writeable( $this->getDirectory() ) ) {
			$this->last_error = 'temp_dir_cannot_be_writeable';
			return false;
		}

		if( null != $this->last_error ) return false;

		chmod($this->getDirectory(), 0777);

		foreach( $this->items as $key => $item ) {
			$filename = $this->getDirectory() . $this->getFileName( $key );

			if(file_exists($filename)) 
				unlink( $filename );

			file_put_contents( $filename , $this->encrypt_content ? $this->_encrypt( $item ) : $item );
		}
	}

	public function getItem( $item ) 
	{
		$filename = $this->getDirectory() . $this->getFileName( $item );
		
		if( !file_exists( $filename ) ) {
			$this->last_error = 'item_does_not_exist';
			return false;
		}

		if($this->encrypt_content) 
			$content = $this->_decrypt( file_get_contents( $filename ) );
		else
			$content = file_get_contents( $filename );

		if( null != $this->last_error ) return false;

		if(base64_encode(base64_decode($content)) == $content ) {
			$this->last_error = 'content_is_encrypted';
			return false;
		}
		return $content;
	}

	public function clear()
	{
		foreach( glob( $this->getDirectory() . $this->prefix . '*'. $this->extension ) as $file ) {
			unlink( $file );
		}
	}

	public function getError()
	{
		return $this->last_error;
	}

	protected function _encrypt( $content )
	{
		$iv = mcrypt_create_iv(
			mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC),
			MCRYPT_DEV_URANDOM
		);

		$encrypted = base64_encode(
			$iv .
			mcrypt_encrypt(
				MCRYPT_RIJNDAEL_128,
				hash('sha256', $this->_key, true),
				$content,
				MCRYPT_MODE_CBC,
				$iv
			)
		);

		return $encrypted;
	}

	protected function _decrypt( $content )
	{
		$data = base64_decode($content);
		$iv = substr($data, 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC));

		$decrypted = rtrim(
			mcrypt_decrypt(
				MCRYPT_RIJNDAEL_128,
				hash('sha256', $this->_key, true),
				substr($data, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC)),
				MCRYPT_MODE_CBC,
				$iv
			),
			"\0"
		);

		if(mb_check_encoding($decrypted,'UTF-8') || mb_check_encoding($decrypted, 'ASCII')) {
			return $decrypted;
		} else {
			$this->last_error = 'invalid_decryption_key';
			return false;
		}
	}
}
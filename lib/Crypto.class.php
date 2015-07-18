<?php
/**
  * Crypto.class.php
  */ 

/**
 * This class contains cryptography functions
 */
class Crypto{
	/**
	 * Encode with AES 256 algorithm
	 */
	public static function aes256Encode($data, $key = CRYPTO_KEY, $iv = CRYPTO_IV){
		$block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$pad = $block - (strlen($data) % $block);
		$data .= str_repeat(chr($pad), $pad);
		
		$result = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv );
		$result = base64_encode($result);
		return $result;
	}

	/**
	 * Decode with AES 256 algorithm
	 */
	public static function aes256Decode($data, $key = CRYPTO_KEY, $iv = CRYPTO_IV){
		$code = base64_decode($data);
		$code = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $code, MCRYPT_MODE_CBC, $iv);
		$code = substr($code,0,-(ord($code[strlen($code)-1])));
		return $code;
	}

	
	/**
	 * Hash a string with a salt
	 */
	public static function saltHash($password, $salt = CRYPTO_SALT){
		return sha1($salt . $password . $salt);
	}


	/**
	 * Generate random key 
	 * @param int $length The length of the generated key
	 * @return string the generated key
	 */
	public function generateKey($length){
		$result = '';
		for ($i=0; $i < $length; $i++) { 
			$result .= chr(mt_rand(32,127));
		}

		return $result;
	}
}
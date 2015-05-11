<?php
/**********************************************************************
 *    						Crypto.class.php
 *
 *
 * Author:   Julien Thaon & Sebastien Lecocq 
 * Date: 	 Jan. 01, 2014
 * Copyright: ELVYRRA SAS
 *
 * This file is part of Beaver's project.
 *
 *
 **********************************************************************/
 
class Crypto{
	function aes256Encode($data, $key = CRYPTO_KEY, $iv = CRYPTO_IV){
		$block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$pad = $block - (strlen($data) % $block);
		$data .= str_repeat(chr($pad), $pad);
		
		$result = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv );
		$result = base64_encode($result);
		return $result;
	}

	function aes256Decode($data, $key = CRYPTO_KEY, $iv = CRYPTO_IV){
		$code = base64_decode($data);
		$code = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $code, MCRYPT_MODE_CBC, $iv);
		$code = substr($code,0,-(ord($code[strlen($code)-1])));
		return $code;
	}

	function saltHash($password, $salt = CRYPTO_SALT){
		return sha1($salt . $password . $salt);
	}
}
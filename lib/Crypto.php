<?php
/**
 * Crypto.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class contains cryptography functions
 *
 * @package Security
 */
class Crypto{

    /**
     * Encode a string with AES 256 algorithm
     *
     * @param string $data The data to encrypt
     * @param string $key  The encryption key
     * @param string $iv   The initialization vector for encryption
     *
     * @return string The encrypted data
     */
    public static function aes256Encode($data, $key = CRYPTO_KEY, $iv = CRYPTO_IV){
        $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $pad = $block - (strlen($data) % $block);
        $data .= str_repeat(chr($pad), $pad);

        $result = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv);
        $result = base64_encode($result);
        return $result;
    }

    /**
     * Decode with AES 256 algorithm
     *
     * @param string $data The data to decrypt
     * @param string $key  The decryption key
     * @param string $iv   The initialization vector for decryption
     *
     * @return string The decrypted data
     */
    public static function aes256Decode($data, $key = CRYPTO_KEY, $iv = CRYPTO_IV){
        $code = base64_decode($data);
        $code = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $code, MCRYPT_MODE_CBC, $iv);
        $code = substr($code, 0, -(ord($code[strlen($code)-1])));
        return $code;
    }


    /**
     * Hash a string with a salt
     *
     * @deprecated 2.4.1
     * @param string $data The data to hash
     * @param string $salt The salt to use before hashing
     *
     * @return string The hashed data
     */
    public static function saltHash($data, $salt = CRYPTO_SALT) {
        return sha1($salt . $data . $salt);
    }


    /**
     * Hash a string with a salt
     *
     * @param string $data The data to hash
     * @param string $salt The salt to use before hashing
     *
     * @return string The hashed data
     */
    public static function hashPassword($data) {
        $salt = substr(md5(mcrypt_create_iv(HASH_SALT_LENGTH, MCRYPT_RAND)), 0, HASH_SALT_LENGTH);
        $algos = hash_algos();
        $algo = $algos[HASH_ALGO];
        $hash = hash($algo, $salt . $data);

        return HASH_ALGO . '$' . $salt . '$' . $hash;
    }


    /**
     * Generate random key
     *
     * @param int $length The length of the generated key
     *
     * @return string the generated key
     */
    public static function generateKey($length){
        $result = '';
        for ($i=0; $i < $length; $i++) {
            do{
                $char = chr(mt_rand(33, 126));
            }while($char == '\\');

            $result .= $char;
        }

        return $result;
    }
}
<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 12/09/12
 * Time: 10:26
 */
trait EncryptionTrait
{
    /**
     * Will encrypt string and return encrypted string.
     * The encryption will be an two-way function, so not as secure as could be,
     * but secure enough to hide passwords in database, and is highly encouraged to be used to that.
     *
     * @param string $string
     * @param string $key
     * @return string
     */
    protected function encrypt($string, $key){
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, md5(md5($key))));
    }

    /**
     * Will decrypt an encrypted string with an given key.
     * The encryption which must be encoded with encrypt() in this trait.
     *
     * @param string $string
     * @param string $key
     * @return string
     */
    protected function decrypt($string, $key){
        return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($string), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
    }

}

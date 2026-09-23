<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Crypt;

class EncryptHelper
{

    /**
     * Encrypt ID untuk URL
     */
    public static function encrypt($id)
    {
        return Crypt::encryptString($id);
    }


    /**
     * Decrypt ID dari URL
     */
    public static function decrypt($encrypted)
    {
        return Crypt::decryptString($encrypted);
    }

}
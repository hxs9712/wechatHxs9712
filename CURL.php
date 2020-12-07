<?php

namespace WechatHxs9712;

class CURL
{
    private static $__inst = null;

    public static function instance()
    {
        if (null == CURL::$__inst) {
            CURL::$__inst = new CURL();
        }

        return CURL::$__inst;
    }

    /*
     * post接口
     */
    public function post($url, $data, $header = false)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_POST, true);
        if (is_array($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        }

        $ret = curl_exec($ch);

        curl_close($ch);
        return $ret;
    }

    /*
    * get接口
    */
    public function get($url, $header = false)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        }

        $ret = curl_exec($ch);

        curl_close($ch);
        return $ret;
    }
}

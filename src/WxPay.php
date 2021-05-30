<?php

namespace WechatHxs9712;

class WxPay
{
    private static $instance;
    /**
     * 微信支付
     */
    protected $wxpay;
    /**
     * 微信小程序
     */
    protected $app;
    protected $url;

    /**
     * OrderController constructor.

     */
    private function __construct($appId,$mch_id,$key)
    {
        $this->app = ['appId'=>$appId,'mch_id'=>$mch_id,'key'=>$key];
        $this->url = "https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi";
    }


    public static function getInstance($appId,$mch_id,$key){
        //判断实例有无创建，没有的话创建实例并返回，有的话直接返回
        if(!(self::$instance instanceof self)){
            self::$instance = new self();
        }
        return self::$instance;
    }

    //克隆方法私有化，防止复制实例
    private function __clone(){}

    //统一下单接口
    public function unifiedorder($total_fee,$notify_url,$openid,$cert_path,$key_path,$nonce_str=null,$body="支付",$out_trade_no=null)
    {
        $post = array();
        $post['appid']      =  $this->app['appid'];
        $post['mch_id']     =  $this->app['mch_id'];
        $post['nonce_str']  = $nonce_str??$this->randStr(30);
        $post['body']       = $body; //商品描述
        $post['out_trade_no']   = $out_trade_no;
        $post['total_fee']      = $total_fee*10;
        $post['spbill_create_ip']   = $_SERVER['REMOTE_ADDR'];
        $post['notify_url']         = $notify_url;
        $post['trade_type']         = 'JSAPI';
        $post['openid']             = $openid;
        $post['cert_path']              = $cert_path;//payment/apiclient_cert.pem
        $post['key_path']               = $key_path;//payment/apiclient_key.pem

        //排序
        ksort($post);
        //生成sign
        $str    = urldecode(http_build_query($post)).'&key='.$this->app['key'];
        $sign   = strtoupper(md5($str));

        $post['sign'] = $sign;

        $xml = $this->arrayToXml($post);
        $re = CURL::instance()->post($this->url, $xml);

        return $re;
    }

    //数组转xml
    function arrayToXml($arr){
        $xml = "<xml>";
        foreach ($arr as $key=>$val){
            if(is_array($val)){
                $xml.="<".$key.">".$this->arrayToXml($val)."</".$key.">";
            }else{
                $xml.="<".$key.">".$val."</".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    //生成nonce_str
    function randStr($n)
    {
        $allStr = '0123456789abcdefghigklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str = '';
        for ($i=0;$i<$n;$n++){
            $str.=$allStr[rand(0,strlen($allStr))];
        }

        return $str;
    }

    //生成签名
    function generate_sign(array $attributes, $key, $encryptMethod = 'md5')
    {
        ksort($attributes);

        $attributes['key'] = $key;

        return strtoupper(call_user_func_array($encryptMethod, [urldecode(http_build_query($attributes))]));
    }
}

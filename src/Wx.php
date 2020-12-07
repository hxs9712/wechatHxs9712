<?php

namespace WechatHxs9712;

use CURLFile;

class Wx
{
   private static $__inst = null;

    /**
     * Wx constructor.
     */
	private function __construct(){
	}

   public static function instance(){
      if (self::$__inst == null){
         self::$__inst = new self();
      }

      return self::$__inst;
   }

    /**
     * @param $appId
     * @param $appSecret
     * @param $jscode
     * @return array
     */
	public function jscodeToSession($appId, $appSecret, $jscode){
      $reqUrl = 'https://api.weixin.qq.com/sns/jscode2session' .
                '?appid=' . $appId .
                '&secret=' . $appSecret .
                '&js_code=' . $jscode .
                '&grant_type=authorization_code';

      $ret = CURL::instance()->get($reqUrl);// file_get_contents($reqUrl);


      //保存用户openid或session key
      $obj = json_decode($ret);
      if (!property_exists($obj, 'errcode')){
         return [ErrorCode::$OK, $obj];
      }
      return [$obj->errcode, $obj->errmsg];
	}


   /**
   * 获取access token
   * @param void
   * @return [errcode, errmsg / access token]
   */
   public function accessToken($appId, $appSecret){
       if(!isset($_SESSION)){
           session_start();
           ini_set('session.gc_maxlifetime', 7000);
       }
      $accessTokenRedisKey = $appId . '_AccessToken';

      $accessToken = $_SESSION[$accessTokenRedisKey];

      //是否已经过期
      if (!$accessToken){
         $reqUrl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential' .
                   '&appid=' . $appId .
                   '&secret=' . $appSecret;

         $ret = CURL::instance()->get($reqUrl);

         $obj = json_decode($ret);
         if (!property_exists($obj, 'errcode')){
            $accessToken = $obj->access_token;
            $_SESSION[$accessTokenRedisKey] = $accessToken;
            return [ErrorCode::$OK, $accessToken];
         }

         return [$obj->errcode, $obj->errmsg];
      }

      return [ErrorCode::$OK, $accessToken];
   }

    /**
     * @param $appId
     * @param $sessionKey
     * @param $encryptedData
     * @param $iv
     * @return array
     */
   public function decrypt($appId, $sessionKey, $encryptedData, $iv){
      $crypt = new WxBizDataCrypt($appId, $sessionKey);

      $data = [];
      $ret = $crypt->decryptData($encryptedData, $iv, $data);

      if ($ret == ErrorCode::$OK){
         return [$ret, $data];
      }

      return [$ret, ErrorCode::code2Message($ret)];
   }

    /**
   * 创建带参二维码
   */
   public function createQrcode($appId, $appSecret, $param){
      $accessToken = $this->accessToken($appId, $appSecret);
      if (ErrorCode::$OK != $accessToken[0]){
         return [$accessToken[0], $accessToken[1]];
      }


      $reqUrl = 'https://api.weixin.qq.com/wxa/getwxacode?access_token=' . $accessToken[1];

      $qrcodeImageData = CURL::instance()->post($reqUrl, json_encode([
         'path' => 'pages/index/index?from=' . $param
      ]));

      $qrcodeFilePath = '/tmp/' . time().rand(9999,99999999999). '.png';
      file_put_contents($qrcodeFilePath, $qrcodeImageData);

      return [ErrorCode::$OK, $qrcodeFilePath];
   }

   /**
   * 图片鉴黄
   */
   public function imageCheck($appId, $appSecret, $filePath){
      $accessToken = $this->accessToken($appId, $appSecret);
      if (ErrorCode::$OK != $accessToken[0]){
         return $accessToken[0];
      }

      $ret = CURL::instance()->post('https://api.weixin.qq.com/wxa/img_sec_check?access_token=' . $accessToken[1], ['media' => new CURLFile($filePath)], ['content-type: multipart/form-data;']);

      $retObj = json_decode($ret);
      if (87014 == $retObj->errcode){
         return ErrorCode::$ContentIllegal;
      }

      return ErrorCode::$OK;
   }

   /**
   * 敏感词识别
   */
   public function textCheck($appId,$appSecret, $keyword){
      $accessToken = $this->accessToken($appId,$appSecret);
      if (ErrorCode::$OK != $accessToken[0]){
         return $accessToken[0];
      }

      $ret = CURL::instance()->post('https://api.weixin.qq.com/wxa/msg_sec_check?access_token=' . $accessToken[1], json_encode(['content' => $keyword], JSON_UNESCAPED_UNICODE), ['content-type: application/x-www-form-urlencoded;charset=UTF-8']);

      $retObj = json_decode($ret);
      if (87014 == $retObj->errcode){
         return ErrorCode::$ContentIllegal;
      }

      return ErrorCode::$OK;
   }

   /**
   * 发送订阅消息
   */
   public function sendSubscribeMessage($appId, $appSecret, $openid, $templateMessageId, $page, $data){
      $tmData = [
         'touser' => $openid,
         'template_id' => $templateMessageId,
         'page' => $page,
         'data' => $data
      ];

      $accessToken = $this->accessToken($appId, $appSecret);
      if (ErrorCode::$OK != $accessToken[0]){
         return $this->fail($accessToken[0], $accessToken[1]);
      }
      $ret = CURL::instance()->post('https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=' . $accessToken[1], json_encode($tmData));

      $obj = json_decode($ret);

      //如果发送成功，则将form id失效
      if ($obj->errcode == 0 || $obj->errcode == 41029 || $obj->errcode == 41028){
         return [ErrorCode::$OK];
      }

      return [$obj->errcode, $obj->errmsg];
   }

   /**
   * 发送模板消息
   */
   public function sendTemplateMessage($appId, $appSecret, $openid, $formId, $templateMessageId, $page, $data){
      $tmData = [
         'touser' => $openid,
         'template_id' => $templateMessageId,
         'page' => $page,
         'form_id' => $formId,
         'data' => []
      ];

      $keywordIndex = 1;
      foreach ($data as $val) {
         $tmData['data']['keyword' . $keywordIndex++] = [
            'value' => $val
         ];
      }

      $accessToken = $this->accessToken($appId,$appSecret);
      if (ErrorCode::$OK != $accessToken[0]){
         return $this->fail($accessToken[0], $accessToken[1]);
      }
      $ret = CURL::instance()->post('https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=' . $accessToken[1], json_encode($tmData));

      $obj = json_decode($ret);

      //如果发送成功，则将form id失效
      if ($obj->errcode == 0 || $obj->errcode == 41029 || $obj->errcode == 41028){
         return [ErrorCode::$OK];
      }

      return [$obj->errcode, $obj->errmsg];
   }

   /**
   * 文字识别
   */
   public function ocrScan($appId,$appSecret,$path){
      $accessToken = $this->accessToken($appId, $appSecret);
      if (ErrorCode::$OK != $accessToken[0]){
         return $accessToken[0];
      }

      $ret = CURL::instance()->post('https://api.weixin.qq.com/cv/ocr/comm?access_token=' . $accessToken[1], ['img' => new CURLFile($path)], ['content-type: multipart/form-data;']);

      return $ret;
   }
}

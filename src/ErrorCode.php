<?php

namespace WechatHxs9712;

class ErrorCode{
	static public $Success = 0;
	static public $OK = 0;
	static public $Fail = -1;

	public static $ContentIllegal = -40001;
	public static $IllegalIv = -40002;
	public static $ComputeSignatureError = -40003;
	public static $IllegalAesKey = -40004;
	public static $ValidateAppidError = -40005;
	public static $EncryptAESError = -40006;
	public static $DecryptAESError = -40007;
	public static $IllegalBuffer = -40008;
	public static $EncodeBase64Error = -40009;
	public static $DecodeBase64Error = -40010;
	public static $GenReturnXmlError = -40011;
	public static $CallException = -40012;

    static private $Message = [
		'0' => '成功',
		'-1' => '失败',

		'-40001' => '内容非法',
		'-40002' => '非法iv',

        '-50001' => '保存文章图片失败',

	];

	static private $UnknowError = '未知错误';

	static public function code2Message($code){
		if (array_key_exists($code, ErrorCode::$Message)){
			return ErrorCode::$Message[$code];
		}

		return ErrorCode::$UnknowError;
	}
}

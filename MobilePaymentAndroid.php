<?php

require_once 'MobilePayment.php';

/**
 * Android用支払クラス
 * @author daisuke
 */
class MobilePaymentAndroid extends MobilePayment {

	const VERIFY_STATUS_CODE_COMPLETE        = 1;  // 検証成功
	const VERIFY_STATUS_CODE_WRONG_SIGNATURE = 0;  // 署名が正しくない
	const VERIFY_STATUS_CODE_ERROR           = -1; // エラー

	#################### property ####################

	/**
	 * GooglePlay署名
	 * @var string
	 */
	private $signature;

	public function setSignature( $signature )
	{
		$this->signature = $signature;
	}

	public function getSignature()
	{
		return $this->signature;
	}

	/**
	 * 公開鍵のパス
	 */
	private $publicKeyPath = NULL;

	public function setPublicKeyPath( $publicKeyPath )
	{
		$this->publicKeyPath = $publicKeyPath;
	}

	/**
	 * レシート検証ステータスコード
	 * @var int
	 */
	private $verifyStatusCode = NULL;

	protected function setVerifyStatusCode( $verifyStausCode )
	{
		$this->verifyStatusCode = $verifyStausCode;
	}

	public function getVerifyStatusCode()
	{
		return $this->verifyStatusCode;
	}

	#################### public function ####################

	/**
	 * レシート検証 ＆ 2重決済チェック
	 */
	public function verify()
	{
		// レシート確認
		$receipt = base64_decode( $this->getReceipt() );
		if ( is_null( $receipt ) === TRUE ){
			throw new Exception( 'receipt_null_error' );
		}
		// 署名署名確認
		$signature = base64_decode( $this->getSignature() );
		if ( is_null( $signature ) === TRUE ){
			throw new Exception( 'signature_null_error' );
		}
		// 公開鍵のパス確認
		if ( is_null( $this->publicKeyPath ) === TRUE ){
			throw new Exception( 'public_key_path_null_error' );
		}

		//--------------
		// レシート検証
		//--------------
		// GooglePlayの管理画面から取得した公開鍵をPEM形式に変換したもの
		$publicKey   = file_get_contents( $this->publicKeyPath );
		$publicKeyId = openssl_get_publickey( $publicKey );

		$statusCode = openssl_verify( $receipt, $signature, $publicKeyId );

		// キーをメモリから解放
		openssl_free_key( $publicKeyId );

		// レシート検証ステータスコードを保存
		$this->setVerifyStatusCode( $statusCode );

		// ステータスコードによる結果振り分け
		switch ( $statusCode ) {
			// ▼ レシート検証成功
			case self::VERIFY_STATUS_CODE_COMPLETE:
				$this->setPaymentResultCode( self::PAYMENT_RESULT_CODE_COMPLETE );
				break;

			// ▼ 検証エラーの場合
			case self::VERIFY_STATUS_CODE_WRONG_SIGNATURE:
			case self::VERIFY_STATUS_CODE_ERROR:
			default:
				$this->setPaymentResultCode( self::PAYMENT_RESULT_CODE_VERIFY_ERROR );
				break;
		}
	}

	/**
	 * 商品ID取得
	 * @return string
	 */
	public function getProductIdFromReceipt()
	{
		return $this->getDataFromReceipt( 'productId' );
	}

	/**
	 * 購入トークン取得
	 * @return string
	 */
	public function getPurchaseTokenFromReceipt()
	{
		return $this->getDataFromReceipt( 'purchaseToken' );
	}

	/**
	 * 購入日取得
	 * @return DateTime
	 */
	public function getPurchaseTimeFromReceipt()
	{
		// レシート内のpurchaseTimeはマイクロ秒表示
		$dateTime = new DateTime( date( 'Y-m-d H:i:s', (int)($this->getDataFromReceipt( 'purchaseTime' ) / 1000) ) );
		$dateTime->setTimezone( new DateTimeZone( $this->getTimeZone() ) );

		return $dateTime;
	}

	#################### private function ####################

	/**
	 * レシートからデータを取得
	 */
	private function getDataFromReceipt( $key )
	{
		$receipt = $this->getReceipt();
		if ( is_null( $receipt ) === TRUE ) {
			throw new Exception( 'receipt_null_error' );
		}
		$receiptData = json_decode( base64_decode( $receipt ), TRUE );

		if ( isset( $receiptData[$key] ) ) {
			return $receiptData[$key];
		} else {
			return NULL;
		}
	}
}

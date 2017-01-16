<?php

/**
 * 支払共通クラス
 * @author daisuke
 */
abstract class MobilePayment {

	const PAYMENT_RESULT_CODE_COMPLETE                  = 0;  // 課金正常完了
	const PAYMENT_RESULT_CODE_VERIFY_ERROR              = 1;  // レシート無効エラー
	const PAYMENT_RESULT_CODE_ALREADY                   = 2;  // 商品適用済み
	const PAYMENT_RESULT_CODE_PRODUCT_IS_NOT_VALID      = 3;  // 商品無効エラー
	const PAYMENT_RESULT_CODE_PLEASE_RETRY              = 4;  // リトライ要求
	const PAYMENT_RESULT_CODE_ANOTHER_APPLICATION_ERROR = 5;  // 別アプリ
	const PAYMENT_RESULT_CODE_EXCEPTION_ERROR           = 99; // 例外エラー

	#################### property ####################

	/**
	 * レシート
	 * @var json
	 */
	private $receipt = NULL;

	public function setReceipt( $receipt )
	{
		$this->receipt = $receipt;
	}

	public function getReceipt()
	{
		return $this->receipt;
	}

	/**
	 * 課金検証結果
	 * @var string
	 */
	private $paymentResultCode = NULL;

	protected function setPaymentResultCode( $resultCode )
	{
		$this->paymentResultCode = $resultCode;
	}
	public function getPaymentResultCode()
	{
		return $this->paymentResultCode;
	}

	/**
	 * タイムゾーン
	 * @var string
	 */
	private $timeZone = 'Asia/Tokyo';

	public function setTimeZone( $timeZone )
	{
		$this->timeZone = $timeZone;
	}

	public function getTimeZone()
	{
		return $this->timeZone;
	}

	#################### abstract function ####################

	/**
	 * レシート検証
	 */
	abstract public function verify();
}

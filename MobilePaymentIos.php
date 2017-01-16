<?php

require_once 'MobilePayment.php';

/**
 * iOS用支払クラス
 * @author daisuke
 * 【参考PDF】
 * https://developer.apple.com/jp/documentation/ValidateAppStoreReceipt.pdf
 */
class MobilePaymentIos extends MobilePayment {

	/**
	 * 課金成功
	 */
	const VERIFY_STATUS_CODE_COMPLETE           = '0';

	/**
	 * App Storeは、提供したJSONオブジェクトを読むことができません
	 */
	const VERIFY_STATUS_CODE_UNREADABLE         = '21000';

	/**
	 * receipt-dataプロパティのデータが不正であるか、または欠落しています
	 */
	const VERIFY_STATUS_CODE_WRONG_RECEIPT      = '21002 ';

	/**
	 * レシートを認証できません
	 */
	const VERIFY_STATUS_CODE_NON_AUTHENTICATION = '21003';

	/**
	 * この共有秘密鍵は、アカウントのファイルに保存された共有秘密鍵と一致しません
	 * 自動更新型の購読に用いる、iOS 6型のトランザクションレシートの場合のみ
	 */
	const VERIFY_STATUS_CODE_WRONG_KEY          = '21004';

	/**
	 * レシートサーバは現在利用できません
	 */
	const VERIFY_STATUS_CODE_NOT_RUNNING_SERVER = '21005';

	/**
	 * このレシートは有効ですが、定期購読の期限が切れています。
	 * ステータスコードがサーバに返される際、レシートデータもデコードされ、応答の一部として返されます
	 * 自動更新型の購読に用いる、iOS 6型のトランザクションレシートの場合のみ
	 */
	const VERIFY_STATUS_CODE_EXPIRED            = '21006';

	/**
	 * テスト環境のレシートを、実稼働環境に送信して検証しようとしました
	 * これはテスト環境に送信してください
	 */
	const VERIFY_STATUS_CODE_SENT_TO_DEVELOP    = '21007';

	/**
	 * 実稼働環境のレシートを、テスト環境に送信して検証しようとしました
	 * これは実稼働環境に送信してください
	 */
	const VERIFY_STATUS_CODE_SENT_TO_PRODUCT    = '21008';

	/**
	 * レシート検証先URL
	 * @var string
	 */
	const VERIFY_URL = 'https://buy.itunes.apple.com/verifyReceipt';

	/**
	 * サンドボックス用レシート検証先URL
	 * @var string
	 */
	const VERIFY_URL_TO_SANDBOX = 'https://sandbox.itunes.apple.com/verifyReceipt';

	#################### property ####################

	/**
	 * レシート検証結果
	 * @var stdClass
	 */
	private $verifyResult = NULL;

	private function setVerifyResult( $verifyResult )
	{
		$this->verifyResult = $verifyResult;
	}

	public function getVerifyResult()
	{
		return $this->verifyResult;
	}

	#################### public function ####################

	/**
	 * レシート検証
	 * 2重決済チェック
	 */
	public function verify()
	{
		// セットしたレシートを取得
		$receipt = $this->getReceipt();
		if ( is_null ( $receipt ) === TRUE ) {
			throw new Exception( 'receipt_null_error' );
		}

		//--------------
		// レシート検証
		//--------------
		$postReceiptData = json_encode( ['receipt-data' => $receipt ] );
		$verifyResult    = self::post( self::VERIFY_URL, $postReceiptData );

		// 本番環境にテスト環境のレシートを問い合わせてしまった場合、テスト環境に問い合わせなおす
		// 実行時に本番かテストかを判定できないのでこのように対応する
		if ( $verifyResult->status == self::VERIFY_STATUS_CODE_SENT_TO_DEVELOP ) {
			$verifyResult = self::post( self::VERIFY_URL_TO_SANDBOX, $postReceiptData );
		}

		// レシート検証結果を保存
		$this->setVerifyResult( $verifyResult );

		switch ( $this->getVerifyResult()->status ) {
			// ▼ レシートが有効な場合
			case self::VERIFY_STATUS_CODE_COMPLETE:
				$this->setPaymentResultCode( parent::PAYMENT_RESULT_CODE_COMPLETE );
				break;

			// ▼ アップル側のサーバーが落ちているのでリトライ要求を出す
			case self::VERIFY_STATUS_CODE_NOT_RUNNING_SERVER:
				$this->setPaymentResultCode( parent::PAYMENT_RESULT_CODE_PLEASE_RETRY );
				break;

			// ▼ 上記以外はエラー
			case self::VERIFY_STATUS_CODE_UNREADABLE:
			case self::VERIFY_STATUS_CODE_WRONG_RECEIPT:
			case self::VERIFY_STATUS_CODE_NON_AUTHENTICATION:
			case self::VERIFY_STATUS_CODE_WRONG_KEY:
			case self::VERIFY_STATUS_CODE_EXPIRED:
			default:
				$this->setPaymentResultCode( parent::PAYMENT_RESULT_CODE_VERIFY_ERROR );
				break;
		}

		return;
	}

	/**
	 * 商品ID取得
	 * @return string
	 */
	public function getProductIdFromVerifyResult()
	{
		$verifyResult = $this->getVerifyResult();

		// verifyしてない
		if ( is_null( $verifyResult ) === TRUE ) {
			throw new Exception( 'verify_result_null_error' );
		}

		// 有効レシートの場合
		if ( $verifyResult->status == self::VERIFY_STATUS_CODE_COMPLETE ) {
			return $verifyResult->receipt->in_app[0]->product_id;
		} else { // 有効レシートではない場合
			return NULL;
		}
	}

	/**
	 * バンドルID取得
	 */
	public function getBundleIdFromVerifyResult()
	{
		$verifyResult = $this->getVerifyResult();

		// verifyしてない
		if ( is_null( $verifyResult ) === TRUE ) {
			throw new Exception( 'verify_result_null_error' );
		}

		// 有効レシートの場合
		if ( $verifyResult->status == self::VERIFY_STATUS_CODE_COMPLETE ) {
			return $verifyResult->receipt->bundle_id;
		} else { // 有効レシートではない場合
			return NULL;
		}
	}

	/**
	 * トランザクションID取得
	 */
	public function getTransactionIdFromVerifyResult()
	{
		$verifyResult = $this->getVerifyResult();

		// verifyしてない
		if ( is_null( $verifyResult ) === TRUE ) {
			throw new Exception( 'verify_result_null_error' );
		}

		// 有効レシートの場合
		if ( $verifyResult->status == self::VERIFY_STATUS_CODE_COMPLETE ) {
			return $verifyResult->receipt->in_app[0]->transaction_id;
		} else { // 有効レシートではない場合
			return NULL;
		}
	}

	/**
	 * 購入日取得
	 * @return DateTime
	 */
	public function getPurchaseDateFromVerifyResult()
	{
		$verifyResult = $this->getVerifyResult();

		// verifyしてない
		if ( is_null( $verifyResult ) === TRUE ) {
			throw new Exception( 'verify_result_null_error' );
		}

		// 有効レシートの場合
		if ( $verifyResult->status == self::VERIFY_STATUS_CODE_COMPLETE ) {
			$purchaseDate = new DateTime( $verifyResult->receipt->in_app[0]->purchase_date );
			$purchaseDate->setTimezone( new DateTimeZone( $this->getTimeZone() ) );
			return $purchaseDate;
		} else { // 有効レシートではない場合
			return NULL;
		}
	}

	#################### private function ####################

	/**
	 * ポストメソッド
	 * @param string $url
	 * @param string $postData
	 * @return json
	 */
	private static function post( $url, $postData )
	{
		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ch, CURLOPT_POST, TRUE );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $postData );
		$verifyResult = json_decode( curl_exec( $ch ) );
		curl_close( $ch );

		return $verifyResult;
	}
}

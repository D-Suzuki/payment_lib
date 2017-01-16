<?php

/**
 * Android用支払クラス
 * @author daisuke
 */
class PaymentAndroid extends MobilePaymentAndroid {

	// 公開鍵ファイル名
	const PUBLIC_KEY_FILE_NAME = 'android_rsa.pub';

	######################  public function ######################

	/**
	 * レシート検証
	 * @see MobilePaymentAndroid::verify()
	 */
	public function verify()
	{
		// レシート検証
		parent::verify();

		// レシートが有効だった場合、下記チェックを行う
		if ( $this->getVerifyStatusCode() == parent::VERIFY_STATUS_CODE_COMPLETE ){

			// ▼ 商品IDチェック
			if ( $this->isValidProductId( parent::getProductIdFromReceipt() ) === FALSE ) {
				$paymentObj->setPaymentResultCode( parent::PAYMENT_RESULT_CODE_PRODUCT_IS_NOT_VALID );
				return;
			}

			// ▼ 2重決済チェック
			// 決済済み & 商品適用済みのレシートを投げられていないか検証
			if ( $this->isNewPurchaseToken( parent::getPurchaseTokenFromReceipt() ) === FALSE ) {
				$this->setPaymentResultCode( parent::PAYMENT_RESULT_CODE_ALREADY );
				return;
			}

			// ▼ 2重決済確認用履歴データ登録
			try {
				$this->addPurchaseToken( parent::getPurchaseTokenFromReceipt() );
			} catch( Exception $e ) {
				// 2重決済だったら課金結果を変更（同時トランザクションを考慮）
				if ( preg_match('/Duplicate entry/', $e->getMessage() ) === 1 ) {
					$this->setPaymentResultCode( parent::PAYMENT_RESULT_CODE_ALREADY );
					return;
				}
				throw new Exception( $e );
			}
		}
	}

	/**
	 * 商品ID取得
	 * @return string
	 */
	public function getProductId()
	{
		return parent::getProductIdFromReceipt();
	}

	/**
	 * レシート検証履歴追加
	 * @param int $userSeqNum ユーザ識別番号
	 */
	public function addVerifyHistory( $userSeqNum )
	{
		// レシート検証済みであれば履歴作成
		$verifyStatusCode = $this->getVerifyStatusCode();
		if ( is_null ( $verifyStatusCode ) === TRUE ) {
			throw new Exeption( 'unverified_error' );
		}

		##########################
		# 検証履歴追加処理       #
		# ▼ 推奨履歴項目        #
		# ・ユーザ識別番号       #
		# ・レシート             #
		# ・署名                 #
		# ・検証ステータス       #
		# ・購入トークン         #
		# ・購入日(レシート内の) #
		##########################

		return $historySeqNum;
	}

	######################  private function ######################

	/**
	 * 商品ID有効チェック
	 * @param string $productId
	 * @return boolean
	 */
	private function isValidProductId( $productId )
	{
		######################
		# 商品IDチェック処理 #
		# マスタ照会         #
		######################

		return TRUE;
	}

	/**
	 * 決済済みのレシートかどうかの検証
	 * @param  string $purchaseToken
	 * @return boolean
	 */
	private function isNewPurchaseToken( $purchaseToken )
	{
		###########################
		# DBへpurchaseTokenで検索 #
		# レコードがあればFALSE   #
		# レコードがなければTRUE  #
		###########################

		return TRUE;
	}

	/**
	 * 2重決済確認用履歴データ登録
	 * @param string $purchaseToken
	 */
	private function addPurchaseToken( $purchaseToken )
	{
		#############################
		# DBへpurchaseTokenをinsert #
		#############################
	}
}
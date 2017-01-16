<?php

/**
 * iOS用支払クラス
 * @author daisuke
 */
class PaymentIos extends MobilePaymentIos {

	################################
	# アプリのbundleIdを定数へ記述 #
	################################
	const BUNDLE_ID = 'sample_bundle_id';

	######################  public function ######################

	/**
	 * (non-PHPdoc)
	 * @see MobilePaymentIos::verify()
	 */
	public function verify()
	{
		// レシート検証
		parent::verify();

		// レシートが有効だった場合、下記チェックを行う
		if ( $this->getVerifyResult()->status == parent::VERIFY_STATUS_CODE_COMPLETE ){

			// ▼ アプリ認証確認
			if ( $this->isValidBundleId( parent::getBundleIdFromVerifyResult() ) === FALSE ) {
				$paymentObj->setPaymentResultCode( parent::PAYMENT_RESULT_CODE_ANOTHER_APPLICATION_ERROR );
				return;
			}

			// ▼ 商品IDチェック
			if ( $this->isValidProductId( parent::getProductIdFromVerifyResult() ) === FALSE ) {
				$paymentObj->setPaymentResultCode( parent::PAYMENT_RESULT_CODE_PRODUCT_IS_NOT_VALID );
				return;
			}

			// ▼ 2重決済チェック
			// 決済済み & 商品適用済みのレシートを投げられていないか検証
			if ( $this->isNewTransactionId( parent::getTransactionIdFromVerifyResult() ) === FALSE ) {
				$this->setPaymentResultCode( parent::PAYMENT_RESULT_CODE_ALREADY );
				return;
			}

			// ▼ 2重決済確認用履歴データ登録
			try {
				$this->addTransactionId( parent::getTransactionIdFromVerifyResult() );
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
		return parent::getProductIdFromVerifyResult();
	}

	/**
	 * レシート検証履歴追加
	 * @param int $userSeqNum ユーザ識別番号
	 */
	public function addVerifyHistory( $userSeqNum )
	{
		// レシート検証済みであれば履歴作成
		$verifyResult = $this->getVerifyResult();
		if ( is_null ( $verifyResult ) === TRUE ) {
			throw new Exception( 'unverified_error' );
		}

		##########################
		# 検証履歴追加処理       #
		# ▼ 推奨履歴項目        #
		# ・ユーザ識別番号       #
		# ・レシート             #
		# ・検証結果(JSON値)     #
		# ・検証ステータス       #
		# ・トランザクションID   #
		# ・購入日(検証結果内の) #
		##########################

		return $historySeqNum;
	}

	######################  private function ######################

	/**
	 * バンドルID有効チェック
	 * @param string $bundleId
	 * @return boolean
	 */
	private function isValidBundleId( $bundleId )
	{
		if ( $bundleId == self::BUNDLE_ID ){
			return TRUE;
		} else {
			return FALSE;
		}
	}

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
	 * 決済済みのレシートを投げられていないか検証
	 * @param  string  $transactionId
	 * @return boolean
	 */
	private function isNewTransactionId( $transactionId )
	{
		###########################
		# DBへtransactionIdで検索 #
		# レコードがあればFALSE   #
		# レコードがなければTRUE  #
		###########################

		return TRUE;
	}

	/**
	 * 2重決済確認用履歴データ登録
	 * @param string $transactionId
	 */
	private function addTransactionId( $transactionId )
	{
		#############################
		# DBへtransactionIdをinsert #
		#############################
	}
}
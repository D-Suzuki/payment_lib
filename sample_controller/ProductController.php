<?php

/**
 * 商品コントローラー
 */
class ProductController
{
	const OS_TYPE_IOS     = 1;
	const OS_TYPE_ANDROID = 2;

	/**
	 * レシート検証
	 * @throws Exception
	 */
	public function buyAction()
	{
		// リクエストパラメータ取得
		$productId   = $this -> getRequestParam( 'product_id' ); // 商品ID
		$price       = $this -> getRequestParam( 'price' );      // 購入金額取得
		$receipt     = $this -> getRequestParam( 'receipt' );    // レシート取得
		$signature   = $this -> getRequestParam( 'signature' );  // 署名取得(Android用)

		// ユーザOSタイプ
		$userOsType = OS_TYPE_IOS;
		// ユーザ識別番号
		$userSeqNum = '1';
		// Android用公開鍵のパス
		$publickeyPath = '/path_to_key_directory/android_rsa.pub';

		try {

			// OSタイプから支払オブジェクト取得
			// ※ファクトリーパターンで作るときれい
			if ( $userOsType == self::OS_TYPE_IOS ) {
				$paymentObj = new PaymentIos();
			} elseif( $userOsType == self::OS_TYPE_ANDROID ) {
				$paymentObj = new PaymentAndroid();
			}

			// 購入日用のタイムゾーンをセット
			$paymentObj->setTimezone( 'Asia/Tokyo' );
			// レシートをセット
			$paymentObj->setReceipt( $receipt );

			// 検証情報をセット
			if ( $userOsType == self::OS_TYPE_ANDROID ) { // ▼ Androidの場合、下記もセット
				$paymentObj->setSignature( $signature );
				$paymentObj->setPublicKeyPath( $publickeyPath );
			}

			// レシート検証
			$paymentObj->verify();

			// ▼ 課金成功の場合
			if ( $paymentObj->getPaymentResultCode() == MobilePayment::PAYMENT_RESULT_CODE_COMPLETE ){

				################
				# 商品購入処理 #
				################

			} else { // ▼ 課金失敗の場合

				##################
				# 課金失敗時処理 #
				##################

			}

			// 検証履歴追加
			$historySeqNum = $paymentObj->addVerifyHistory( $userSeqNum );

			##############################
			# 課金履歴追加               #
			# ▼ 推奨履歴項目            #
			# ・OSタイプ                 #
			# ・ユーザ識別番号           #
			# ・商品ID                   #
			# ・購入商品情報             #
			# ・支払ステータス           #
			# ・検証履歴のシーケンス番号 #
			##############################

			##################
			# レスポンス処理 #
			##################

		} catch( Exception $e ) {

			################################################
			# エラー通知処理                               #
			# ユーザ識別番号やレシート、署名情報などを通知 #
			################################################

			// ロールバック処理へ
			throw new Exception( $e );
		}
	}
}
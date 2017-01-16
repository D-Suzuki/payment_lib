## MobilePayment Class
MobilePaymentIos、MobilePaymentAndroidの親クラス。
抽象クラスであり、課金結果ステータスの定数と
いくつかのメソッドをもつ。

### 定数
```
const PAYMENT_RESULT_CODE_COMPLETE                  = 0;  // 課金正常完了
const PAYMENT_RESULT_CODE_VERIFY_ERROR              = 1;  // レシート無効エラー
const PAYMENT_RESULT_CODE_ALREADY                   = 2;  // 商品適用済み
const PAYMENT_RESULT_CODE_PRODUCT_IS_NOT_VALID      = 3;  // 商品無効エラー
const PAYMENT_RESULT_CODE_PLEASE_RETRY              = 4;  // リトライ要求
const PAYMENT_RESULT_CODE_ANOTHER_APPLICATION_ERROR = 5;  // 別アプリ
const PAYMENT_RESULT_CODE_EXCEPTION_ERROR           = 99; // 例外エラー
```

### メソッド
```
/* public */
public function setReceipt( $receipt )
public function getReceipt()
public function getPaymentResultCode()
public function setTimeZone( $timeZone )
public function getTimeZone()

/* protected */
protected function setPaymentResultCode( $resultCode )

/* abstract */
abstract public function verify()
```

## MobilePaymentIos Class
IOS用支払クラス。
MobilePayment親クラスにもつ。

### 定数
```
const VERIFY_STATUS_CODE_COMPLETE           = '0';
const VERIFY_STATUS_CODE_UNREADABLE         = '21000';
const VERIFY_STATUS_CODE_WRONG_RECEIPT      = '21002 ';
const VERIFY_STATUS_CODE_NON_AUTHENTICATION = '21003';
const VERIFY_STATUS_CODE_WRONG_KEY          = '21004';
const VERIFY_STATUS_CODE_NOT_RUNNING_SERVER = '21005';
const VERIFY_STATUS_CODE_EXPIRED            = '21006';
const VERIFY_STATUS_CODE_SENT_TO_DEVELOP    = '21007';
const VERIFY_STATUS_CODE_SENT_TO_PRODUCT    = '21008';
const VERIFY_URL                            = 'https://buy.itunes.apple.com/verifyReceipt';
const VERIFY_URL_TO_SANDBOX                 = 'https://sandbox.itunes.apple.com/verifyReceipt';
```

### メソッド
```
/* public */
public function getVerifyResult()
public function verify()
public function getProductIdFromVerifyResult()
public function getBundleIdFromVerifyResult()
public function getTransactionIdFromVerifyResult()
public function getPurchaseDateFromVerifyResult()

/* private */
private function setVerifyResult( $verifyResult )
private static function post( $url, $postData )
```

### 使い方
```php
$paymentObj = new MobliePaymentIos();
$paymentObj->setReceipt( $receipt );
$paymentObj->verify();

// 課金成功時処理
if ( $paymentObj->getPaymentResultCode() == MobilePayment::PAYMENT_RESULT_CODE_COMPLETE ) {

} else { // 課金失敗時処理

}
```

### 注意点
このクラスではレシート検証時以下のチェックはしないのでサブクラスを実装してこれらのチェックを行う必要がある。
```
・正しいアプリかチェック
・正しい商品かチェック
・2重決済チェック

※サブクラスサンプル
sample_sub_class/PaymentIos.php
#####
#   #
#####
で囲われたコメント部分を実装してください。
```

## MobilePaymentAndroid Class
Android用支払クラス。
MobilePayment親クラスにもつ。

### 定数
```
const VERIFY_STATUS_CODE_COMPLETE        = 1;  // 検証成功
const VERIFY_STATUS_CODE_WRONG_SIGNATURE = 0;  // 署名が正しくない
const VERIFY_STATUS_CODE_ERROR           = -1; // エラー
```

### メソッド
```
/* public */
public function setSignature( $signature )
public function getSignature()
public function setPublicKeyPath( $publicKeyPath )
public function getVerifyStatusCode()
public function verify()
public function getProductIdFromReceipt()
public function getPurchaseTokenFromReceipt()
public function getPurchaseTimeFromReceipt()

/* protected*/
protected function setVerifyStatusCode( $verifyStausCode )

/* private */
private function getDataFromReceipt( $key )
```

### 使い方
```php
$paymentObj = new MobliePaymentAndroid();
$paymentObj->setReceipt( $receipt );
$paymentObj->setSignature( $signature );
$paymentObj->setPublicKeyPath( $publicKeyPath );
$paymentObj->verify();

// 課金成功時処理
if ( $paymentObj->getPaymentResultCode() == MobilePayment::PAYMENT_RESULT_CODE_COMPLETE ) {

} else { // 課金失敗時処理

}
```

### 注意点
このクラスではレシート検証時以下のチェックはしないのでサブクラスを実装してこれらのチェックを行う必要がある。
```
・正しい商品かチェック
・2重決済チェック

※サブクラスサンプル
sample_sub_class/PaymentAndroid.php
#####
#   #
#####
で囲われたコメント部分を実装してください。
```
# payment_lib

### Description
MobilePaymentIos.php、MobilePaymentAndroid.phpだけではセキュアなレシート検証は行えないため、sample_sub_classディレクトリ内に用意されているサンプルサブクラスをプロジェクトに合うように修正し、使用するようにしてください。

レシート検証するにはプラットフォーム毎に以下のものが必要になります。
```
■IOS
Itunes Connectで登録したBundleId

■Android
Google Play Developer Consoleから取得したRAS公開鍵をPEM形式にしたもの。
※プロジェクト内に配置しておく
```
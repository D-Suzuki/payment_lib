# payment_lib

MobilePaymentIos.php、MobilePaymentAndroid.phpだけではセキュアなレシート検証は行えないため、sample_sub_classディレクトリ内に用意されているサンプルサブクラスをプロジェクトに合うように修正し、使用するようにしてください。

レシート検証するにはプラットフォーム毎に以下のものが必要になります。
```
■IOS（任意）
Itunes Connectで登録したBundleId

■Android（必須）
Google Play Developer Consoleから取得したRAS公開鍵をPEM形式にしたもの。
※プロジェクト内に配置しておく
```

クラス概要はコチラ
https://github.com/up-system/payment_lib/blob/master/Class.md

サブクラスを実装した上でのレシート検証の行い方サンプルはコチラ
https://github.com/up-system/payment_lib/blob/master/sample_controller/ProductController.php

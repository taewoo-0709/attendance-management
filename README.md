# attendance-management

## 環境構築 🔗

Docker ビルド

・  git@github.com:taewoo-0709/attendance-management.git<br>

・  docker-compose up -d --build

＊MySQL は、OS によって起動しない場合があるのでそれぞれの PC に合わせて docker-compose.yml ファイルを編集してください。

Laravel 環境構築

・ docker-compose exec php bash

・ composer install

・ .env.example ファイルから.env を作成し、環境変数を変更
   cp .env.example .env

・ php artisan key:generate

・ php artisan migrate

・ php artisan db:seed

### ユーザー例
・ 管理者
メールアドレス: admin@example.com<br>
パスワード: coachtech1100

・ユーザー<br>
名前:佐藤一郎<br> メールアドレス:staff1@example.com<br>
名前:鈴木花子<br> メールアドレス:staff2@example.come<br>
名前:田中次郎<br> メールアドレス:staff3@example.come<br>
名前:山田優子<br> メールアドレス:staff4@example.come<br>
<br>
パスワード（共通）:coachtech123

## メール認証機能
mailhogを使用しています。<br>
メール認証誘導画面の、「認証はこちら」ボタンから認証画面に遷移するため、mailhogにアクセスし、届いているメールから認証コードを確認して認証画面にコードを入力して認証完了してください。

## テスト実施
### テスト用データベースの作成・コマンド
1. MySQLコンテナで、「demo_test」というDBを作成。<br>
・docker-compose exec mysql bash<br>
・mysql -u ユーザー名 -p<br>
  例:mysql -u root -p<br>
・パスワード + Enter (docker-compose.ymlのMYSQL_ROOT_PASSWORDで指定したパスワードです。)<br>
  例: root + Enter<br>
・mysql> CREATE DATABASE demo_test;<br>
・mysql> SHOW DATABASES;<br>
※demo_testが追加されているか確認してください。<br>

2. .env.testingのAPP_KEY= にアプリケーションキーを追加<br>
・docker-compose exec php bash<br>
・php artisan key:generate --env=testing<br>

3. php artisan config:clear
4. php artisan migrate --env=testing
5. php artisan test<br>
※「Unit」ディレクトリがなければディレクトリを作成してからテスト実行してください。

## テーブル仕様書
<img width="397" height="454" alt="スクリーンショット 2025-09-21 12 44 27" src="https://github.com/user-attachments/assets/e6fc2a28-d844-4da4-b2b8-1593adf1e208" /><br>
<img width="410" height="325" alt="スクリーンショット 2025-09-21 12 44 40" src="https://github.com/user-attachments/assets/6ecd1c57-2f22-4e47-84c1-4128fdcb3203" /><br>

※attendances_tableのreasonはnullableですが、管理者修正時,reasonを入力必須のバリデーションを実装しています。<br>そのため、Seederや通常打刻ではreasonは不要としています。<br>
同じくattendances_tableのcheck_in_timeとcheck_out_timeは、nullableですが、修正時に休憩時間のみで登録されないようバリデーション実装と、当日の打刻の修正のみ出勤時間と備考のみでも可能としています（過去の出勤日は出退勤の入力必須）。

※申請承認済みのデータに関しては、再度申請が行える様になっています。

※ログイン機能のバリデーションに関して、それぞれの権限以外でのログインがなされた際にバリデーションがかかるよう、Fortify＋Controllerで実装している箇所があります。

## ER図
<img width="454" height="585" alt="スクリーンショット 2025-09-07 11 46 20" src="https://github.com/user-attachments/assets/a2492ac9-8c59-428c-a38e-e2309a62e606" />

## 使用技術 🔗

・PHP 8.1.33

・Laravel 8.83.29

・MySQL 8.0.34

・nginx 1.21.1

・mailhog

## URL

・開発環境：http://localhost/ <br>
※ http://localhost/login か http://localhost/admin/login にアクセスしてください。

・phpMyAdmin：http://localhost:8080/

・mailhog:http://localhost:8025/
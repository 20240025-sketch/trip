# 参加者・バス・部屋割一括管理機能

## 概要
このシステムは、旅行計画の参加者情報とその割り当て（バス座席・部屋割）を一括で管理する機能を提供します。

## 主な機能

### 入力・編集できる項目
1. **基本情報**
   - メールアドレス
   - 名前
   - クラス
   - 連絡先

2. **バス座席** （複数登録可能）
   - 何日目か（日程選択）
   - 何号車
   - 何列目

3. **部屋割** （複数登録可能）
   - 何日目か（日程選択）
   - 何号室

## 使い方

### 1. 参加者管理ページへのアクセス
プラン詳細ページから「👥 参加者管理」ボタンをクリック、または以下のURLにアクセス：
```
/plans/{プランID}/participants
```

### 2. 参加者の追加
1. 「参加者を追加」ボタンをクリック
2. 基本情報を入力
3. 必要に応じてバス座席・部屋割を追加

### 3. バス座席・部屋割の追加
各参加者カード内で：
- 「バス座席」セクションの「追加」ボタンをクリック
- 「部屋割」セクションの「追加」ボタンをクリック
- 複数の割り当てを登録可能（複数日程に対応）

### 4. 保存
すべての入力が完了したら「すべて保存」ボタンをクリック

## API エンドポイント

### 参加者と割り当ての一覧取得
```
GET /api/plans/{planId}/participant-assignments
```

### 一括作成
```
POST /api/plans/{planId}/participant-assignments/bulk

リクエストボディ:
{
  "participants": [
    {
      "name": "桑原　遥希",
      "email": "20230001@seiei.ac.jp",
      "class_name": "3年 3特進",
      "contact": "20230001",
      "bus_assignments": [
        {
          "day_id": 1,
          "bus_number": "1号車",
          "row_number": "2列目"
        }
      ],
      "room_assignments": [
        {
          "day_id": 1,
          "room_number": "101号室"
        }
      ]
    }
  ]
}
```

### 更新
```
PUT /api/participant-assignments/{participantId}

リクエストボディ: （一括作成と同じ形式）
```

## サンプルデータのインポート

提供されたサンプルデータをインポートするには：

```bash
php artisan db:seed --class=SampleParticipantsSeeder
```

このコマンドは、20名の学生データを最初のプランに追加します。

## データベース構造

### participants テーブル
- id
- plan_id
- name（名前）
- email（メールアドレス）
- class_name（クラス）
- contact（連絡先/学籍番号）
- created_at
- updated_at

### bus_assignments テーブル
- id
- plan_id
- day_id（日程）
- participant_id
- bus_number（何号車）
- row_number（何列目）
- created_at
- updated_at

### room_assignments テーブル
- id
- plan_id
- day_id（日程）
- participant_id
- room_number（何号室）
- created_at
- updated_at

## 技術スタック

### バックエンド
- Laravel 11
- PHP 8.2+
- MySQL/PostgreSQL

### フロントエンド
- Vue.js 3
- Composition API
- Axios for API calls

## ファイル構成

### バックエンド
- `app/Models/Participant.php` - 参加者モデル
- `app/Models/BusAssignment.php` - バス座席モデル
- `app/Models/RoomAssignment.php` - 部屋割モデル
- `app/Http/Controllers/Api/ParticipantAssignmentController.php` - 一括管理コントローラー
- `database/migrations/*_add_email_to_participants_table.php` - メールアドレスカラム追加

### フロントエンド
- `resources/js/components/ParticipantAssignmentManager.vue` - 管理コンポーネント
- `resources/js/pages/ParticipantManagementPage.vue` - 管理ページ
- `resources/js/router/index.js` - ルート定義

## 注意事項

1. **認証が必要**: 参加者管理ページにアクセスするには、ログインが必要です
2. **プランの編集権限**: プランの所有者またはadminユーザーのみが参加者を管理できます
3. **データの整合性**: 日程（day_id）は必ず存在するDayレコードを指定してください
4. **複数割り当て**: 1人の参加者が複数のバス座席・部屋割を持つことができます（複数日程対応）

## トラブルシューティング

### エラー: "日程が選択されていません"
- プランに日程（Days）が登録されているか確認してください

### エラー: "保存に失敗しました"
- 必須項目（名前、日程、号車/号室）が入力されているか確認してください
- ネットワーク接続を確認してください

### データが表示されない
- ブラウザのコンソールでエラーを確認してください
- APIエンドポイントが正しく設定されているか確認してください

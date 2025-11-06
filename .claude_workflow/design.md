# 設計書：旅の計画管理システム

## 前提
**requirements.mdを読み込みました**

## 1. アーキテクチャ設計

### 1.1 全体構成
```
┌─────────────────────────────────────────┐
│         フロントエンド (Vue.js 3)        │
│  - SPA (Single Page Application)         │
│  - Tailwind CSS                          │
│  - Vite (ビルドツール)                   │
└─────────────────┬───────────────────────┘
                  │ Axios (HTTP Client)
                  │
┌─────────────────▼───────────────────────┐
│      バックエンド API (Laravel 11)       │
│  - RESTful API                           │
│  - Eloquent ORM                          │
│  - Validation                            │
└─────────────────┬───────────────────────┘
                  │
        ┌─────────┴──────────┐
        │                    │
┌───────▼────────┐  ┌────────▼─────────┐
│  SQLite DB     │  │  Local Storage   │
│  - データ保存  │  │  - 画像ファイル │
└────────────────┘  └──────────────────┘
```

### 1.2 ディレクトリ構造
```
laravel-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── PlanController.php
│   │   │   ├── DayController.php
│   │   │   ├── ScheduleItemController.php
│   │   │   ├── ParticipantController.php
│   │   │   ├── ChecklistItemController.php
│   │   │   ├── ImageController.php
│   │   │   └── PdfController.php
│   │   ├── Requests/
│   │   │   ├── StorePlanRequest.php
│   │   │   ├── UpdatePlanRequest.php
│   │   │   ├── StoreScheduleItemRequest.php
│   │   │   └── UploadImageRequest.php
│   │   └── Resources/
│   │       ├── PlanResource.php
│   │       ├── DayResource.php
│   │       └── ScheduleItemResource.php
│   ├── Models/
│   │   ├── Plan.php
│   │   ├── Day.php
│   │   ├── ScheduleItem.php
│   │   ├── Participant.php
│   │   ├── ChecklistItem.php
│   │   └── Image.php
│   └── Services/
│       ├── ImageService.php
│       └── PdfService.php
├── database/
│   ├── migrations/
│   │   ├── xxxx_create_plans_table.php
│   │   ├── xxxx_create_days_table.php
│   │   ├── xxxx_create_schedule_items_table.php
│   │   ├── xxxx_create_participants_table.php
│   │   ├── xxxx_create_checklist_items_table.php
│   │   └── xxxx_create_images_table.php
│   └── seeders/
│       └── SampleDataSeeder.php
├── resources/
│   ├── js/
│   │   ├── app.js
│   │   ├── components/
│   │   │   ├── layout/
│   │   │   │   ├── Header.vue
│   │   │   │   ├── Footer.vue
│   │   │   │   └── Navigation.vue
│   │   │   ├── plans/
│   │   │   │   ├── PlanList.vue
│   │   │   │   ├── PlanCard.vue
│   │   │   │   ├── PlanForm.vue
│   │   │   │   ├── PlanDetail.vue
│   │   │   │   └── PlanPublic.vue
│   │   │   ├── schedule/
│   │   │   │   ├── DayTabs.vue
│   │   │   │   ├── Timeline.vue
│   │   │   │   ├── ScheduleItemForm.vue
│   │   │   │   └── ScheduleItemCard.vue
│   │   │   ├── participants/
│   │   │   │   ├── ParticipantList.vue
│   │   │   │   └── ParticipantForm.vue
│   │   │   ├── checklist/
│   │   │   │   ├── ChecklistList.vue
│   │   │   │   └── ChecklistItem.vue
│   │   │   ├── images/
│   │   │   │   ├── ImageUploader.vue
│   │   │   │   ├── ImageGallery.vue
│   │   │   │   └── ImageModal.vue
│   │   │   └── common/
│   │   │       ├── Button.vue
│   │   │       ├── Modal.vue
│   │   │       ├── Alert.vue
│   │   │       └── Loading.vue
│   │   ├── views/
│   │   │   ├── Home.vue
│   │   │   ├── PlanIndex.vue
│   │   │   ├── PlanCreate.vue
│   │   │   ├── PlanEdit.vue
│   │   │   ├── PlanShow.vue
│   │   │   └── PlanPublic.vue
│   │   ├── router/
│   │   │   └── index.js
│   │   └── stores/
│   │       ├── planStore.js
│   │       └── uiStore.js
│   ├── views/
│   │   ├── app.blade.php
│   │   └── pdf/
│   │       └── plan.blade.php
│   └── css/
│       └── app.css
├── routes/
│   ├── web.php
│   └── api.php
└── storage/
    └── app/
        └── public/
            ├── plans/
            └── thumbnails/
```

## 2. データベース設計

### 2.1 ER図
```
┌──────────────┐         ┌──────────────┐
│    plans     │1      *│     days     │
│──────────────│─────────│──────────────│
│ id (PK)      │         │ id (PK)      │
│ title        │         │ plan_id (FK) │
│ description  │         │ date         │
│ start_date   │         │ day_number   │
│ end_date     │         │ title        │
│ cover_image  │         │ created_at   │
│ is_public    │         │ updated_at   │
│ slug         │         └──────┬───────┘
│ created_at   │                │1
│ updated_at   │                │
└──────┬───────┘                │
       │1                       │
       │                        │*
       │                 ┌──────▼──────────────┐
       │                 │  schedule_items     │
       │                 │─────────────────────│
       │                 │ id (PK)             │
       │                 │ day_id (FK)         │
       │                 │ time                │
       │                 │ title               │
       │                 │ description         │
       │                 │ location            │
       │                 │ transport_type      │
       │                 │ transport_from      │
       │                 │ transport_to        │
       │                 │ transport_duration  │
       │                 │ transport_cost      │
       │                 │ order               │
       │                 │ created_at          │
       │                 │ updated_at          │
       │                 └─────────────────────┘
       │
       │*                ┌──────────────────┐
       ├─────────────────│  participants    │
       │                 │──────────────────│
       │                 │ id (PK)          │
       │                 │ plan_id (FK)     │
       │                 │ name             │
       │                 │ contact          │
       │                 │ avatar           │
       │                 │ created_at       │
       │                 │ updated_at       │
       │                 └──────────────────┘
       │
       │*                ┌──────────────────┐
       ├─────────────────│ checklist_items  │
       │                 │──────────────────│
       │                 │ id (PK)          │
       │                 │ plan_id (FK)     │
       │                 │ category         │
       │                 │ item             │
       │                 │ is_checked       │
       │                 │ order            │
       │                 │ created_at       │
       │                 │ updated_at       │
       │                 └──────────────────┘
       │
       │*                ┌──────────────────┐
       └─────────────────│     images       │
                         │──────────────────│
                         │ id (PK)          │
                         │ imageable_type   │
                         │ imageable_id     │
                         │ filename         │
                         │ original_name    │
                         │ path             │
                         │ thumbnail_path   │
                         │ size             │
                         │ mime_type        │
                         │ caption          │
                         │ order            │
                         │ created_at       │
                         │ updated_at       │
                         └──────────────────┘
                         (Polymorphic Relation)
```

### 2.2 テーブル詳細仕様

#### plans テーブル
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| id | bigint unsigned | NO | AUTO_INCREMENT | 主キー |
| title | varchar(255) | NO | - | 計画タイトル |
| description | text | YES | NULL | 計画の説明 |
| start_date | date | NO | - | 開始日 |
| end_date | date | NO | - | 終了日 |
| cover_image | varchar(255) | YES | NULL | カバー画像パス |
| is_public | boolean | NO | false | 公開設定 |
| slug | varchar(255) | NO | UNIQUE | URL用スラッグ |
| memo | text | YES | NULL | メモ・注意事項 |
| created_at | timestamp | YES | NULL | 作成日時 |
| updated_at | timestamp | YES | NULL | 更新日時 |

**インデックス**
- PRIMARY KEY (id)
- UNIQUE KEY (slug)
- INDEX (is_public)
- INDEX (start_date)

#### days テーブル
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| id | bigint unsigned | NO | AUTO_INCREMENT | 主キー |
| plan_id | bigint unsigned | NO | - | 計画ID（外部キー） |
| date | date | NO | - | 日付 |
| day_number | integer | NO | - | 何日目か |
| title | varchar(255) | YES | NULL | 日のタイトル |
| created_at | timestamp | YES | NULL | 作成日時 |
| updated_at | timestamp | YES | NULL | 更新日時 |

**インデックス**
- PRIMARY KEY (id)
- FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE CASCADE
- INDEX (plan_id, day_number)

#### schedule_items テーブル
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| id | bigint unsigned | NO | AUTO_INCREMENT | 主キー |
| day_id | bigint unsigned | NO | - | 日程ID（外部キー） |
| time | time | YES | NULL | 時刻 |
| title | varchar(255) | NO | - | タイトル |
| description | text | YES | NULL | 説明 |
| location | varchar(255) | YES | NULL | 場所 |
| transport_type | varchar(50) | YES | NULL | 移動手段 |
| transport_from | varchar(255) | YES | NULL | 出発地 |
| transport_to | varchar(255) | YES | NULL | 到着地 |
| transport_duration | integer | YES | NULL | 所要時間（分） |
| transport_cost | integer | YES | NULL | 費用 |
| order | integer | NO | 0 | 表示順 |
| created_at | timestamp | YES | NULL | 作成日時 |
| updated_at | timestamp | YES | NULL | 更新日時 |

**インデックス**
- PRIMARY KEY (id)
- FOREIGN KEY (day_id) REFERENCES days(id) ON DELETE CASCADE
- INDEX (day_id, order)

#### participants テーブル
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| id | bigint unsigned | NO | AUTO_INCREMENT | 主キー |
| plan_id | bigint unsigned | NO | - | 計画ID（外部キー） |
| name | varchar(255) | NO | - | 参加者名 |
| contact | varchar(255) | YES | NULL | 連絡先 |
| avatar | varchar(255) | YES | NULL | アバター画像パス |
| created_at | timestamp | YES | NULL | 作成日時 |
| updated_at | timestamp | YES | NULL | 更新日時 |

**インデックス**
- PRIMARY KEY (id)
- FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE CASCADE
- INDEX (plan_id)

#### checklist_items テーブル
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| id | bigint unsigned | NO | AUTO_INCREMENT | 主キー |
| plan_id | bigint unsigned | NO | - | 計画ID（外部キー） |
| category | varchar(100) | YES | NULL | カテゴリ |
| item | varchar(255) | NO | - | アイテム名 |
| is_checked | boolean | NO | false | チェック状態 |
| order | integer | NO | 0 | 表示順 |
| created_at | timestamp | YES | NULL | 作成日時 |
| updated_at | timestamp | YES | NULL | 更新日時 |

**インデックス**
- PRIMARY KEY (id)
- FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE CASCADE
- INDEX (plan_id, order)

#### images テーブル
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| id | bigint unsigned | NO | AUTO_INCREMENT | 主キー |
| imageable_type | varchar(255) | NO | - | 関連モデル型 |
| imageable_id | bigint unsigned | NO | - | 関連モデルID |
| filename | varchar(255) | NO | - | ファイル名 |
| original_name | varchar(255) | NO | - | 元のファイル名 |
| path | varchar(255) | NO | - | 保存パス |
| thumbnail_path | varchar(255) | YES | NULL | サムネイルパス |
| size | integer | NO | - | ファイルサイズ（bytes） |
| mime_type | varchar(100) | NO | - | MIMEタイプ |
| caption | text | YES | NULL | キャプション |
| order | integer | NO | 0 | 表示順 |
| created_at | timestamp | YES | NULL | 作成日時 |
| updated_at | timestamp | YES | NULL | 更新日時 |

**インデックス**
- PRIMARY KEY (id)
- INDEX (imageable_type, imageable_id)

## 3. API設計

### 3.1 APIエンドポイント一覧

#### 計画（Plans）
| メソッド | エンドポイント | 説明 | 認証 |
|---------|---------------|------|------|
| GET | /api/plans | 計画一覧取得 | 不要 |
| POST | /api/plans | 計画作成 | 不要 |
| GET | /api/plans/{id} | 計画詳細取得 | 不要 |
| PUT | /api/plans/{id} | 計画更新 | 不要 |
| DELETE | /api/plans/{id} | 計画削除 | 不要 |
| GET | /api/plans/slug/{slug} | スラッグで計画取得 | 不要 |

#### 日程（Days）
| メソッド | エンドポイント | 説明 | 認証 |
|---------|---------------|------|------|
| GET | /api/plans/{planId}/days | 日程一覧取得 | 不要 |
| POST | /api/plans/{planId}/days | 日程作成 | 不要 |
| PUT | /api/days/{id} | 日程更新 | 不要 |
| DELETE | /api/days/{id} | 日程削除 | 不要 |

#### スケジュールアイテム（Schedule Items）
| メソッド | エンドポイント | 説明 | 認証 |
|---------|---------------|------|------|
| GET | /api/days/{dayId}/schedule-items | アイテム一覧取得 | 不要 |
| POST | /api/days/{dayId}/schedule-items | アイテム作成 | 不要 |
| PUT | /api/schedule-items/{id} | アイテム更新 | 不要 |
| DELETE | /api/schedule-items/{id} | アイテム削除 | 不要 |
| PUT | /api/schedule-items/{id}/reorder | 順序変更 | 不要 |

#### 参加者（Participants）
| メソッド | エンドポイント | 説明 | 認証 |
|---------|---------------|------|------|
| GET | /api/plans/{planId}/participants | 参加者一覧取得 | 不要 |
| POST | /api/plans/{planId}/participants | 参加者作成 | 不要 |
| PUT | /api/participants/{id} | 参加者更新 | 不要 |
| DELETE | /api/participants/{id} | 参加者削除 | 不要 |

#### 持ち物リスト（Checklist Items）
| メソッド | エンドポイント | 説明 | 認証 |
|---------|---------------|------|------|
| GET | /api/plans/{planId}/checklist-items | アイテム一覧取得 | 不要 |
| POST | /api/plans/{planId}/checklist-items | アイテム作成 | 不要 |
| PUT | /api/checklist-items/{id} | アイテム更新 | 不要 |
| DELETE | /api/checklist-items/{id} | アイテム削除 | 不要 |
| PUT | /api/checklist-items/{id}/toggle | チェック切替 | 不要 |

#### 画像（Images）
| メソッド | エンドポイント | 説明 | 認証 |
|---------|---------------|------|------|
| POST | /api/images/upload | 画像アップロード | 不要 |
| DELETE | /api/images/{id} | 画像削除 | 不要 |
| PUT | /api/images/{id} | 画像更新（キャプション等） | 不要 |

#### PDF出力
| メソッド | エンドポイント | 説明 | 認証 |
|---------|---------------|------|------|
| GET | /api/plans/{id}/pdf | PDF生成・ダウンロード | 不要 |

### 3.2 リクエスト/レスポンス例

#### POST /api/plans（計画作成）
**Request:**
```json
{
  "title": "鎌倉日帰りの旅",
  "description": "友達と鎌倉観光",
  "start_date": "2025-12-01",
  "end_date": "2025-12-01",
  "is_public": true,
  "memo": "集合時間に遅れないこと"
}
```

**Response (201):**
```json
{
  "data": {
    "id": 1,
    "title": "鎌倉日帰りの旅",
    "description": "友達と鎌倉観光",
    "start_date": "2025-12-01",
    "end_date": "2025-12-01",
    "cover_image": null,
    "is_public": true,
    "slug": "kamakura-day-trip-abc123",
    "memo": "集合時間に遅れないこと",
    "days": [],
    "participants": [],
    "checklist_items": [],
    "created_at": "2025-11-06T10:00:00Z",
    "updated_at": "2025-11-06T10:00:00Z"
  }
}
```

#### POST /api/days/{dayId}/schedule-items（スケジュール作成）
**Request:**
```json
{
  "time": "09:00",
  "title": "鶴岡八幡宮参拝",
  "description": "有名な神社を訪問",
  "location": "鶴岡八幡宮",
  "order": 1
}
```

**Response (201):**
```json
{
  "data": {
    "id": 1,
    "day_id": 1,
    "time": "09:00:00",
    "title": "鶴岡八幡宮参拝",
    "description": "有名な神社を訪問",
    "location": "鶴岡八幡宮",
    "transport_type": null,
    "transport_from": null,
    "transport_to": null,
    "transport_duration": null,
    "transport_cost": null,
    "order": 1,
    "images": [],
    "created_at": "2025-11-06T10:05:00Z",
    "updated_at": "2025-11-06T10:05:00Z"
  }
}
```

#### POST /api/images/upload（画像アップロード）
**Request (multipart/form-data):**
```
image: (binary)
imageable_type: "App\\Models\\ScheduleItem"
imageable_id: 1
caption: "鶴岡八幡宮の本殿"
```

**Response (201):**
```json
{
  "data": {
    "id": 1,
    "imageable_type": "App\\Models\\ScheduleItem",
    "imageable_id": 1,
    "filename": "abc123def456.jpg",
    "original_name": "shrine.jpg",
    "path": "/storage/plans/abc123def456.jpg",
    "thumbnail_path": "/storage/thumbnails/abc123def456.jpg",
    "size": 2048576,
    "mime_type": "image/jpeg",
    "caption": "鶴岡八幡宮の本殿",
    "order": 0,
    "created_at": "2025-11-06T10:10:00Z",
    "updated_at": "2025-11-06T10:10:00Z"
  }
}
```

## 4. フロントエンド設計

### 4.1 コンポーネント構成

#### 4.1.1 レイアウトコンポーネント
- **Header.vue**: ヘッダー、ナビゲーション
- **Footer.vue**: フッター、著作権表示
- **Navigation.vue**: メインナビゲーション

#### 4.1.2 計画管理コンポーネント
- **PlanList.vue**: 計画一覧表示
- **PlanCard.vue**: 計画カード（サムネイル）
- **PlanForm.vue**: 計画作成・編集フォーム
- **PlanDetail.vue**: 計画詳細表示（管理画面）
- **PlanPublic.vue**: 計画公開ページ

#### 4.1.3 スケジュール管理コンポーネント
- **DayTabs.vue**: 日付タブ切り替え
- **Timeline.vue**: タイムライン表示
- **ScheduleItemForm.vue**: スケジュールアイテム入力フォーム
- **ScheduleItemCard.vue**: スケジュールアイテムカード

#### 4.1.4 その他機能コンポーネント
- **ParticipantList.vue**: 参加者一覧
- **ParticipantForm.vue**: 参加者入力フォーム
- **ChecklistList.vue**: 持ち物リスト
- **ChecklistItem.vue**: 持ち物アイテム
- **ImageUploader.vue**: 画像アップローダー
- **ImageGallery.vue**: 画像ギャラリー
- **ImageModal.vue**: 画像拡大モーダル

#### 4.1.5 共通コンポーネント
- **Button.vue**: 汎用ボタン
- **Modal.vue**: 汎用モーダル
- **Alert.vue**: 通知・警告表示
- **Loading.vue**: ローディング表示

### 4.2 ルーティング設計

```javascript
const routes = [
  {
    path: '/',
    name: 'home',
    component: Home
  },
  {
    path: '/plans',
    name: 'plans.index',
    component: PlanIndex
  },
  {
    path: '/plans/create',
    name: 'plans.create',
    component: PlanCreate
  },
  {
    path: '/plans/:id',
    name: 'plans.show',
    component: PlanShow
  },
  {
    path: '/plans/:id/edit',
    name: 'plans.edit',
    component: PlanEdit
  },
  {
    path: '/p/:slug',
    name: 'plans.public',
    component: PlanPublic
  }
]
```

### 4.3 状態管理（Pinia）

#### planStore.js
```javascript
export const usePlanStore = defineStore('plan', {
  state: () => ({
    plans: [],
    currentPlan: null,
    loading: false,
    error: null
  }),
  actions: {
    async fetchPlans() { ... },
    async fetchPlan(id) { ... },
    async createPlan(data) { ... },
    async updatePlan(id, data) { ... },
    async deletePlan(id) { ... }
  }
})
```

#### uiStore.js
```javascript
export const useUiStore = defineStore('ui', {
  state: () => ({
    showModal: false,
    modalContent: null,
    alerts: [],
    theme: 'light'
  }),
  actions: {
    openModal(content) { ... },
    closeModal() { ... },
    addAlert(message, type) { ... },
    removeAlert(id) { ... }
  }
})
```

## 5. 画像処理設計

### 5.1 アップロード処理フロー
```
1. クライアント: 画像選択
2. バリデーション（フロント）
   - ファイル形式チェック（JPEG/PNG）
   - サイズチェック（5MB以下）
3. サーバー送信（FormData）
4. バリデーション（バックエンド）
5. 一意なファイル名生成（UUID）
6. オリジナル保存
7. サムネイル生成（800x600）
8. データベース登録
9. レスポンス返却
```

### 5.2 画像保存構造
```
storage/app/public/
├── plans/
│   ├── {uuid}.jpg
│   ├── {uuid}.jpg
│   └── ...
└── thumbnails/
    ├── {uuid}.jpg
    ├── {uuid}.jpg
    └── ...
```

### 5.3 画像処理仕様
- **オリジナル**: 最大幅1920px、JPEG品質90%
- **サムネイル**: 最大幅800px、JPEG品質85%
- **アスペクト比**: 維持

## 6. PDF出力設計

### 6.1 PDF生成フロー
```
1. クライアント: PDF出力ボタンクリック
2. API呼び出し: GET /api/plans/{id}/pdf
3. サーバー: 計画データ取得
4. Bladeテンプレート描画
5. DomPDF: HTML→PDF変換
6. レスポンス: PDFファイル
7. クライアント: ダウンロード
```

### 6.2 PDFレイアウト
**ページ構成:**
1. 表紙
   - タイトル
   - 日程
   - カバー画像
   - 参加者リスト

2. 日別スケジュール
   - 日付見出し
   - タイムライン形式
   - 画像（適度なサイズ）
   - 交通情報

3. 持ち物リスト
   - カテゴリ別

4. メモ・注意事項

### 6.3 PDF設定
- **用紙サイズ**: A4
- **向き**: 縦（Portrait）
- **マージン**: 上下左右 20mm
- **フォント**: Noto Sans JP（日本語対応）
- **ファイル名**: `{計画タイトル}_{日付}.pdf`

## 7. バリデーション設計

### 7.1 計画（Plan）
```php
[
    'title' => 'required|string|max:255',
    'description' => 'nullable|string|max:5000',
    'start_date' => 'required|date',
    'end_date' => 'required|date|after_or_equal:start_date',
    'is_public' => 'boolean',
    'memo' => 'nullable|string|max:10000'
]
```

### 7.2 スケジュールアイテム（ScheduleItem）
```php
[
    'time' => 'nullable|date_format:H:i',
    'title' => 'required|string|max:255',
    'description' => 'nullable|string|max:5000',
    'location' => 'nullable|string|max:255',
    'transport_type' => 'nullable|string|in:train,bus,plane,car,walk,other',
    'transport_from' => 'nullable|string|max:255',
    'transport_to' => 'nullable|string|max:255',
    'transport_duration' => 'nullable|integer|min:0',
    'transport_cost' => 'nullable|integer|min:0',
    'order' => 'required|integer|min:0'
]
```

### 7.3 画像（Image）
```php
[
    'image' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB
    'imageable_type' => 'required|string',
    'imageable_id' => 'required|integer',
    'caption' => 'nullable|string|max:500'
]
```

## 8. エラーハンドリング設計

### 8.1 HTTPステータスコード
- **200 OK**: 成功
- **201 Created**: 作成成功
- **204 No Content**: 削除成功
- **400 Bad Request**: バリデーションエラー
- **404 Not Found**: リソース未発見
- **422 Unprocessable Entity**: バリデーションエラー（詳細）
- **500 Internal Server Error**: サーバーエラー

### 8.2 エラーレスポンス形式
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "title": [
      "タイトルは必須です。"
    ],
    "start_date": [
      "開始日は有効な日付である必要があります。"
    ]
  }
}
```

### 8.3 フロントエンドエラー表示
- バリデーションエラー: フォーム下に赤文字で表示
- システムエラー: Alertコンポーネントで通知
- ネットワークエラー: 再試行ボタン付きメッセージ

## 9. パフォーマンス最適化

### 9.1 バックエンド
- Eager Loading（N+1問題回避）
- API Resourceでのデータ整形
- 画像の適切なリサイズ
- キャッシュ（将来実装）

### 9.2 フロントエンド
- Lazy Loading（画像）
- コンポーネントの動的インポート
- Virtual Scrolling（大量データ）
- Debounce（検索入力）

### 9.3 データベース
- 適切なインデックス設定
- CASCADE DELETE設定
- 不要なデータの定期削除（将来実装）

## 10. セキュリティ対策

### 10.1 XSS対策
- Blade/Vue.jsの自動エスケープ機能
- v-htmlの使用制限
- DOMPurify（将来実装）

### 10.2 CSRF対策
- Laravel標準のCSRFトークン
- APIではSanctum（将来実装）

### 10.3 画像アップロード対策
- MIMEタイプ検証
- ファイルサイズ制限
- 拡張子チェック
- ランダムファイル名生成

### 10.4 SQLインジェクション対策
- Eloquent ORM使用
- プリペアドステートメント

## 11. テスト設計

### 11.1 単体テスト（Unit Tests）
- Modelのリレーション
- Validationルール
- Serviceクラスのロジック

### 11.2 機能テスト（Feature Tests）
- API エンドポイント
- CRUD操作
- 画像アップロード
- PDF生成

### 11.3 テストデータ
- Factory定義
- Seederによるサンプルデータ

## 12. デプロイ・環境設定

### 12.1 必要なパッケージ
```bash
# Composer
composer require barryvdh/laravel-dompdf
composer require intervention/image-laravel

# NPM
npm install vue@next
npm install vue-router@4
npm install pinia
npm install axios
npm install @headlessui/vue
npm install @heroicons/vue
```

### 12.2 環境変数
```env
APP_NAME="旅の計画管理システム"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite

FILESYSTEM_DISK=public
```

### 12.3 初期設定コマンド
```bash
php artisan storage:link
php artisan migrate:fresh --seed
npm run build
```

## 13. 実装優先順位

### Phase 1: 基本機能（必須）
1. データベース設計・マイグレーション
2. Eloquentモデル作成
3. 計画CRUD API
4. 基本的なフロントエンド（Vue.js）
5. 計画一覧・詳細表示

### Phase 2: スケジュール機能
1. 日程管理API
2. スケジュールアイテムAPI
3. タイムライン表示
4. ドラッグ&ドロップ並び替え

### Phase 3: 画像機能
1. 画像アップロードAPI
2. 画像表示コンポーネント
3. サムネイル生成
4. 画像ギャラリー

### Phase 4: 追加機能
1. 参加者管理
2. 持ち物リスト
3. メモ機能

### Phase 5: PDF出力
1. PDFテンプレート作成
2. PDF生成API
3. ダウンロード機能

### Phase 6: UI/UX改善
1. レスポンシブ対応
2. アニメーション
3. エラーハンドリング強化
4. ローディング表示

---

**作成日**: 2025-11-06
**バージョン**: 1.0
**ステータス**: 承認待ち
**前段階**: requirements.md読み込み完了

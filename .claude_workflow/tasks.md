# タスクリスト：旅の計画管理システム

## 前提
**design.mdを読み込みました**

## タスク実行ステータス
- ⏳ 未着手
- 🚧 進行中
- ✅ 完了
- ❌ スキップ/不要

---

## Phase 1: 基本機能（必須）

### 1.1 環境準備・パッケージインストール
**優先度**: 🔴 最高

#### Task 1.1.1: Composerパッケージインストール ⏳
```bash
composer require barryvdh/laravel-dompdf
composer require intervention/image-laravel
```
**成果物**: `composer.json`更新、vendorディレクトリ
**所要時間**: 5分

#### Task 1.1.2: NPMパッケージインストール ⏳
```bash
npm install vue@next vue-router@4 pinia axios
npm install @headlessui/vue @heroicons/vue
```
**成果物**: `package.json`更新、node_modulesディレクトリ
**所要時間**: 5分

#### Task 1.1.3: ストレージリンク作成 ⏳
```bash
php artisan storage:link
mkdir -p storage/app/public/plans
mkdir -p storage/app/public/thumbnails
```
**成果物**: `public/storage`シンボリックリンク
**所要時間**: 1分

---

### 1.2 データベース設計・マイグレーション
**優先度**: 🔴 最高

#### Task 1.2.1: plansテーブルマイグレーション作成 ⏳
```bash
php artisan make:migration create_plans_table
```
**ファイル**: `database/migrations/xxxx_create_plans_table.php`
**カラム**: id, title, description, start_date, end_date, cover_image, is_public, slug, memo, timestamps
**所要時間**: 10分

#### Task 1.2.2: daysテーブルマイグレーション作成 ⏳
```bash
php artisan make:migration create_days_table
```
**ファイル**: `database/migrations/xxxx_create_days_table.php`
**カラム**: id, plan_id (FK), date, day_number, title, timestamps
**所要時間**: 10分

#### Task 1.2.3: schedule_itemsテーブルマイグレーション作成 ⏳
```bash
php artisan make:migration create_schedule_items_table
```
**ファイル**: `database/migrations/xxxx_create_schedule_items_table.php`
**カラム**: id, day_id (FK), time, title, description, location, transport_*, order, timestamps
**所要時間**: 15分

#### Task 1.2.4: participantsテーブルマイグレーション作成 ⏳
```bash
php artisan make:migration create_participants_table
```
**ファイル**: `database/migrations/xxxx_create_participants_table.php`
**カラム**: id, plan_id (FK), name, contact, avatar, timestamps
**所要時間**: 10分

#### Task 1.2.5: checklist_itemsテーブルマイグレーション作成 ⏳
```bash
php artisan make:migration create_checklist_items_table
```
**ファイル**: `database/migrations/xxxx_create_checklist_items_table.php`
**カラム**: id, plan_id (FK), category, item, is_checked, order, timestamps
**所要時間**: 10分

#### Task 1.2.6: imagesテーブルマイグレーション作成 ⏳
```bash
php artisan make:migration create_images_table
```
**ファイル**: `database/migrations/xxxx_create_images_table.php`
**カラム**: id, imageable_type, imageable_id, filename, original_name, path, thumbnail_path, size, mime_type, caption, order, timestamps
**所要時間**: 15分

#### Task 1.2.7: マイグレーション実行 ⏳
```bash
php artisan migrate
```
**成果物**: SQLiteデータベースにテーブル作成
**所要時間**: 2分

---

### 1.3 Eloquentモデル作成
**優先度**: 🔴 最高

#### Task 1.3.1: Planモデル作成 ⏳
```bash
php artisan make:model Plan
```
**ファイル**: `app/Models/Plan.php`
**実装内容**:
- fillable定義
- リレーション: hasMany(Day, Participant, ChecklistItem), morphMany(Image)
- Mutator: slug自動生成
- Accessor: formatted_dates
**所要時間**: 20分

#### Task 1.3.2: Dayモデル作成 ⏳
```bash
php artisan make:model Day
```
**ファイル**: `app/Models/Day.php`
**実装内容**:
- fillable定義
- リレーション: belongsTo(Plan), hasMany(ScheduleItem)
- Cast: date
**所要時間**: 15分

#### Task 1.3.3: ScheduleItemモデル作成 ⏳
```bash
php artisan make:model ScheduleItem
```
**ファイル**: `app/Models/ScheduleItem.php`
**実装内容**:
- fillable定義
- リレーション: belongsTo(Day), morphMany(Image)
- Cast: time
**所要時間**: 15分

#### Task 1.3.4: Participantモデル作成 ⏳
```bash
php artisan make:model Participant
```
**ファイル**: `app/Models/Participant.php`
**実装内容**:
- fillable定義
- リレーション: belongsTo(Plan)
**所要時間**: 10分

#### Task 1.3.5: ChecklistItemモデル作成 ⏳
```bash
php artisan make:model ChecklistItem
```
**ファイル**: `app/Models/ChecklistItem.php`
**実装内容**:
- fillable定義
- リレーション: belongsTo(Plan)
- Cast: is_checked (boolean)
**所要時間**: 10分

#### Task 1.3.6: Imageモデル作成 ⏳
```bash
php artisan make:model Image
```
**ファイル**: `app/Models/Image.php`
**実装内容**:
- fillable定義
- リレーション: morphTo(imageable)
**所要時間**: 10分

---

### 1.4 API Controller作成
**優先度**: 🔴 最高

#### Task 1.4.1: PlanController作成 ⏳
```bash
php artisan make:controller Api/PlanController --api
```
**ファイル**: `app/Http/Controllers/Api/PlanController.php`
**メソッド**: index, store, show, update, destroy, showBySlug
**所要時間**: 30分

#### Task 1.4.2: DayController作成 ⏳
```bash
php artisan make:controller Api/DayController --api
```
**ファイル**: `app/Http/Controllers/Api/DayController.php`
**メソッド**: index, store, update, destroy
**所要時間**: 20分

#### Task 1.4.3: ScheduleItemController作成 ⏳
```bash
php artisan make:controller Api/ScheduleItemController --api
```
**ファイル**: `app/Http/Controllers/Api/ScheduleItemController.php`
**メソッド**: index, store, update, destroy, reorder
**所要時間**: 25分

#### Task 1.4.4: ParticipantController作成 ⏳
```bash
php artisan make:controller Api/ParticipantController --api
```
**ファイル**: `app/Http/Controllers/Api/ParticipantController.php`
**メソッド**: index, store, update, destroy
**所要時間**: 20分

#### Task 1.4.5: ChecklistItemController作成 ⏳
```bash
php artisan make:controller Api/ChecklistItemController --api
```
**ファイル**: `app/Http/Controllers/Api/ChecklistItemController.php`
**メソッド**: index, store, update, destroy, toggle
**所要時間**: 20分

#### Task 1.4.6: ImageController作成 ⏳
```bash
php artisan make:controller Api/ImageController
```
**ファイル**: `app/Http/Controllers/Api/ImageController.php`
**メソッド**: upload, update, destroy
**所要時間**: 25分

#### Task 1.4.7: PdfController作成 ⏳
```bash
php artisan make:controller Api/PdfController
```
**ファイル**: `app/Http/Controllers/Api/PdfController.php`
**メソッド**: generate
**所要時間**: 30分

---

### 1.5 Form Request作成
**優先度**: 🟡 中

#### Task 1.5.1: StorePlanRequest作成 ⏳
```bash
php artisan make:request StorePlanRequest
```
**ファイル**: `app/Http/Requests/StorePlanRequest.php`
**バリデーションルール**: title, description, start_date, end_date, is_public, memo
**所要時間**: 15分

#### Task 1.5.2: UpdatePlanRequest作成 ⏳
```bash
php artisan make:request UpdatePlanRequest
```
**ファイル**: `app/Http/Requests/UpdatePlanRequest.php`
**所要時間**: 10分

#### Task 1.5.3: StoreScheduleItemRequest作成 ⏳
```bash
php artisan make:request StoreScheduleItemRequest
```
**ファイル**: `app/Http/Requests/StoreScheduleItemRequest.php`
**所要時間**: 15分

#### Task 1.5.4: UploadImageRequest作成 ⏳
```bash
php artisan make:request UploadImageRequest
```
**ファイル**: `app/Http/Requests/UploadImageRequest.php`
**所要時間**: 15分

---

### 1.6 API Resource作成
**優先度**: 🟡 中

#### Task 1.6.1: PlanResource作成 ⏳
```bash
php artisan make:resource PlanResource
```
**ファイル**: `app/Http/Resources/PlanResource.php`
**所要時間**: 15分

#### Task 1.6.2: DayResource作成 ⏳
```bash
php artisan make:resource DayResource
```
**ファイル**: `app/Http/Resources/DayResource.php`
**所要時間**: 10分

#### Task 1.6.3: ScheduleItemResource作成 ⏳
```bash
php artisan make:resource ScheduleItemResource
```
**ファイル**: `app/Http/Resources/ScheduleItemResource.php`
**所要時間**: 15分

#### Task 1.6.4: ImageResource作成 ⏳
```bash
php artisan make:resource ImageResource
```
**ファイル**: `app/Http/Resources/ImageResource.php`
**所要時間**: 10分

---

### 1.7 Service層作成
**優先度**: 🟡 中

#### Task 1.7.1: ImageService作成 ⏳
```bash
touch app/Services/ImageService.php
```
**ファイル**: `app/Services/ImageService.php`
**メソッド**: upload, resize, createThumbnail, delete
**所要時間**: 30分

#### Task 1.7.2: PdfService作成 ⏳
```bash
touch app/Services/PdfService.php
```
**ファイル**: `app/Services/PdfService.php`
**メソッド**: generate, formatData
**所要時間**: 25分

---

### 1.8 ルーティング設定
**優先度**: 🔴 最高

#### Task 1.8.1: API routes設定 ⏳
**ファイル**: `routes/api.php`
**内容**: 全APIエンドポイント定義
**所要時間**: 20分

#### Task 1.8.2: Web routes設定 ⏳
**ファイル**: `routes/web.php`
**内容**: SPAのルーティング（catchall）
**所要時間**: 5分

---

## Phase 2: フロントエンド開発

### 2.1 Vue.js セットアップ
**優先度**: 🔴 最高

#### Task 2.1.1: Vite設定 ⏳
**ファイル**: `vite.config.js`
**内容**: Vue plugin設定、alias設定
**所要時間**: 10分

#### Task 2.1.2: app.js設定 ⏳
**ファイル**: `resources/js/app.js`
**内容**: Vue初期化、Router/Pinia設定
**所要時間**: 15分

#### Task 2.1.3: Router設定 ⏳
**ファイル**: `resources/js/router/index.js`
**内容**: ルート定義
**所要時間**: 20分

#### Task 2.1.4: Pinia Store作成 ⏳
**ファイル**: 
- `resources/js/stores/planStore.js`
- `resources/js/stores/uiStore.js`
**所要時間**: 30分

---

### 2.2 レイアウトコンポーネント
**優先度**: 🔴 最高

#### Task 2.2.1: Header.vue作成 ⏳
**ファイル**: `resources/js/components/layout/Header.vue`
**所要時間**: 20分

#### Task 2.2.2: Footer.vue作成 ⏳
**ファイル**: `resources/js/components/layout/Footer.vue`
**所要時間**: 15分

#### Task 2.2.3: Navigation.vue作成 ⏳
**ファイル**: `resources/js/components/layout/Navigation.vue`
**所要時間**: 15分

---

### 2.3 ビューページ作成
**優先度**: 🔴 最高

#### Task 2.3.1: Home.vue作成 ⏳
**ファイル**: `resources/js/views/Home.vue`
**内容**: ヒーローセクション、機能紹介、CTA
**所要時間**: 40分

#### Task 2.3.2: PlanIndex.vue作成 ⏳
**ファイル**: `resources/js/views/PlanIndex.vue`
**内容**: 計画一覧、検索機能
**所要時間**: 30分

#### Task 2.3.3: PlanCreate.vue作成 ⏳
**ファイル**: `resources/js/views/PlanCreate.vue`
**内容**: 計画作成フォーム
**所要時間**: 35分

#### Task 2.3.4: PlanEdit.vue作成 ⏳
**ファイル**: `resources/js/views/PlanEdit.vue`
**内容**: 計画編集フォーム
**所要時間**: 30分

#### Task 2.3.5: PlanShow.vue作成 ⏳
**ファイル**: `resources/js/views/PlanShow.vue`
**内容**: 計画詳細表示（管理画面）
**所要時間**: 45分

#### Task 2.3.6: PlanPublic.vue作成 ⏳
**ファイル**: `resources/js/views/PlanPublic.vue`
**内容**: 計画公開ページ
**所要時間**: 40分

---

### 2.4 計画管理コンポーネント
**優先度**: 🔴 最高

#### Task 2.4.1: PlanList.vue作成 ⏳
**ファイル**: `resources/js/components/plans/PlanList.vue`
**所要時間**: 25分

#### Task 2.4.2: PlanCard.vue作成 ⏳
**ファイル**: `resources/js/components/plans/PlanCard.vue`
**所要時間**: 20分

#### Task 2.4.3: PlanForm.vue作成 ⏳
**ファイル**: `resources/js/components/plans/PlanForm.vue`
**所要時間**: 35分

---

### 2.5 スケジュール管理コンポーネント
**優先度**: 🔴 最高

#### Task 2.5.1: DayTabs.vue作成 ⏳
**ファイル**: `resources/js/components/schedule/DayTabs.vue`
**所要時間**: 25分

#### Task 2.5.2: Timeline.vue作成 ⏳
**ファイル**: `resources/js/components/schedule/Timeline.vue`
**所要時間**: 30分

#### Task 2.5.3: ScheduleItemForm.vue作成 ⏳
**ファイル**: `resources/js/components/schedule/ScheduleItemForm.vue`
**所要時間**: 35分

#### Task 2.5.4: ScheduleItemCard.vue作成 ⏳
**ファイル**: `resources/js/components/schedule/ScheduleItemCard.vue`
**所要時間**: 30分

---

### 2.6 画像管理コンポーネント
**優先度**: 🔴 最高

#### Task 2.6.1: ImageUploader.vue作成 ⏳
**ファイル**: `resources/js/components/images/ImageUploader.vue`
**所要時間**: 35分

#### Task 2.6.2: ImageGallery.vue作成 ⏳
**ファイル**: `resources/js/components/images/ImageGallery.vue`
**所要時間**: 30分

#### Task 2.6.3: ImageModal.vue作成 ⏳
**ファイル**: `resources/js/components/images/ImageModal.vue`
**所要時間**: 25分

---

### 2.7 その他機能コンポーネント
**優先度**: 🟡 中

#### Task 2.7.1: ParticipantList.vue作成 ⏳
**ファイル**: `resources/js/components/participants/ParticipantList.vue`
**所要時間**: 25分

#### Task 2.7.2: ParticipantForm.vue作成 ⏳
**ファイル**: `resources/js/components/participants/ParticipantForm.vue`
**所要時間**: 20分

#### Task 2.7.3: ChecklistList.vue作成 ⏳
**ファイル**: `resources/js/components/checklist/ChecklistList.vue`
**所要時間**: 25分

#### Task 2.7.4: ChecklistItem.vue作成 ⏳
**ファイル**: `resources/js/components/checklist/ChecklistItem.vue`
**所要時間**: 15分

---

### 2.8 共通コンポーネント
**優先度**: 🔴 最高

#### Task 2.8.1: Button.vue作成 ⏳
**ファイル**: `resources/js/components/common/Button.vue`
**所要時間**: 15分

#### Task 2.8.2: Modal.vue作成 ⏳
**ファイル**: `resources/js/components/common/Modal.vue`
**所要時間**: 25分

#### Task 2.8.3: Alert.vue作成 ⏳
**ファイル**: `resources/js/components/common/Alert.vue`
**所要時間**: 20分

#### Task 2.8.4: Loading.vue作成 ⏳
**ファイル**: `resources/js/components/common/Loading.vue`
**所要時間**: 15分

---

## Phase 3: PDF出力機能

### 3.1 PDFテンプレート作成
**優先度**: 🔴 最高

#### Task 3.1.1: plan.blade.php作成 ⏳
**ファイル**: `resources/views/pdf/plan.blade.php`
**内容**: PDF用HTMLテンプレート
**所要時間**: 60分

#### Task 3.1.2: PDF用CSS作成 ⏳
**ファイル**: `resources/views/pdf/plan.blade.php`内にスタイル記述
**所要時間**: 30分

#### Task 3.1.3: DomPDF設定 ⏳
**ファイル**: `config/dompdf.php`（publish後）
**所要時間**: 10分

---

## Phase 4: スタイリング（Tailwind CSS）

### 4.1 Tailwind設定
**優先度**: 🔴 最高

#### Task 4.1.1: tailwind.config.js設定 ⏳
**ファイル**: `tailwind.config.js`
**内容**: カラーパレット、フォント設定
**所要時間**: 20分

#### Task 4.1.2: app.css設定 ⏳
**ファイル**: `resources/css/app.css`
**内容**: Tailwindディレクティブ、カスタムスタイル
**所要時間**: 15分

---

### 4.2 レスポンシブ対応
**優先度**: 🟡 中

#### Task 4.2.1: モバイルレイアウト調整 ⏳
**対象**: 全コンポーネント
**所要時間**: 60分

#### Task 4.2.2: タブレットレイアウト調整 ⏳
**対象**: 全コンポーネント
**所要時間**: 40分

---

## Phase 5: データ投入・テスト

### 5.1 Seeder作成
**優先度**: 🟡 中

#### Task 5.1.1: SampleDataSeeder作成 ⏳
```bash
php artisan make:seeder SampleDataSeeder
```
**ファイル**: `database/seeders/SampleDataSeeder.php`
**内容**: サンプル計画データ
**所要時間**: 30分

#### Task 5.1.2: Seeder実行 ⏳
```bash
php artisan db:seed --class=SampleDataSeeder
```
**所要時間**: 2分

---

### 5.2 機能テスト
**優先度**: 🟢 低

#### Task 5.2.1: PlanControllerテスト作成 ⏳
```bash
php artisan make:test PlanControllerTest
```
**所要時間**: 40分

#### Task 5.2.2: ImageUploadテスト作成 ⏳
```bash
php artisan make:test ImageUploadTest
```
**所要時間**: 30分

#### Task 5.2.3: PDFGenerationテスト作成 ⏳
```bash
php artisan make:test PdfGenerationTest
```
**所要時間**: 25分

---

## Phase 6: 最終調整・ドキュメント

### 6.1 エラーハンドリング
**優先度**: 🟡 中

#### Task 6.1.1: バックエンドエラーハンドリング強化 ⏳
**ファイル**: `app/Exceptions/Handler.php`
**所要時間**: 30分

#### Task 6.1.2: フロントエンドエラーハンドリング強化 ⏳
**ファイル**: 各Vueコンポーネント
**所要時間**: 40分

---

### 6.2 パフォーマンス最適化
**優先度**: 🟢 低

#### Task 6.2.1: Eager Loading適用 ⏳
**対象**: 全Controller
**所要時間**: 20分

#### Task 6.2.2: 画像LazyLoading実装 ⏳
**対象**: ImageGalleryコンポーネント
**所要時間**: 15分

---

### 6.3 ドキュメント作成
**優先度**: 🟡 中

#### Task 6.3.1: README.md更新 ⏳
**ファイル**: `README.md`
**内容**: プロジェクト概要、セットアップ手順、使用方法
**所要時間**: 30分

#### Task 6.3.2: API仕様書作成 ⏳
**ファイル**: `docs/API.md`（新規作成）
**所要時間**: 45分

---

## 完了条件チェックリスト

### 機能面
- [ ] 計画のCRUD操作が正常に動作
- [ ] 日程・スケジュールの作成・編集が可能
- [ ] 画像アップロード・表示が正常動作
- [ ] サムネイル生成が機能
- [ ] PDF出力が期待通りのレイアウトで生成
- [ ] 参加者管理が機能
- [ ] 持ち物リストのチェック機能が動作
- [ ] 公開URLでの閲覧が可能

### 品質面
- [ ] PSR準拠のコード
- [ ] バリデーションエラーの適切な表示
- [ ] XSS対策済み
- [ ] CSRF対策済み
- [ ] 画像ファイルの適切なバリデーション
- [ ] エラー時の適切なフィードバック

### UI/UX面
- [ ] レスポンシブデザインが機能（スマホ・タブレット・PC）
- [ ] ローディング表示が適切
- [ ] 直感的な操作性
- [ ] エラーメッセージが分かりやすい

### パフォーマンス面
- [ ] ページ読み込み3秒以内
- [ ] N+1問題の解消
- [ ] 画像の適切な最適化

---

## 見積もり時間合計

### Phase 1: 基本機能
- 環境準備: 11分
- データベース: 72分
- モデル: 80分
- Controller: 170分
- Request: 55分
- Resource: 50分
- Service: 55分
- ルーティング: 25分
**Phase 1合計: 約8.5時間**

### Phase 2: フロントエンド
- セットアップ: 75分
- レイアウト: 50分
- ビュー: 220分
- コンポーネント: 370分
**Phase 2合計: 約12時間**

### Phase 3: PDF出力
- テンプレート: 100分
**Phase 3合計: 約1.7時間**

### Phase 4: スタイリング
- Tailwind: 35分
- レスポンシブ: 100分
**Phase 4合計: 約2.3時間**

### Phase 5: データ・テスト
- Seeder: 32分
- テスト: 95分
**Phase 5合計: 約2.1時間**

### Phase 6: 最終調整
- エラーハンドリング: 70分
- 最適化: 35分
- ドキュメント: 75分
**Phase 6合計: 約3時間**

---

**全体見積もり時間: 約30時間**

---

**作成日**: 2025-11-06
**バージョン**: 1.0
**ステータス**: 承認待ち
**前段階**: design.md読み込み完了

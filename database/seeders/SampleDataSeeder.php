<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a sample plan
        $plan = \App\Models\Plan::create([
            'title' => '鎌倉日帰りの旅',
            'description' => '友達と鎌倉の名所を巡る楽しい日帰り旅行',
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-01',
            'is_public' => true,
            'memo' => '集合時間に遅れないこと。天気予報をチェックしておく。',
        ]);

        // Create a day
        $day = $plan->days()->create([
            'date' => '2025-12-01',
            'day_number' => 1,
            'title' => '鎌倉観光',
        ]);

        // Create schedule items
        $day->scheduleItems()->create([
            'time' => '09:00',
            'title' => '鎌倉駅集合',
            'description' => '鎌倉駅東口改札前に集合',
            'location' => 'JR鎌倉駅',
            'order' => 1,
        ]);

        $day->scheduleItems()->create([
            'time' => '09:30',
            'title' => '鶴岡八幡宮参拝',
            'description' => '源頼朝ゆかりの神社を参拝。御朱印をいただく。',
            'location' => '鶴岡八幡宮',
            'order' => 2,
        ]);

        $day->scheduleItems()->create([
            'time' => '11:00',
            'title' => '小町通りで食べ歩き',
            'description' => '鎌倉名物のしらすや抹茶スイーツを楽しむ',
            'location' => '小町通り',
            'order' => 3,
        ]);

        $day->scheduleItems()->create([
            'time' => '13:00',
            'title' => '高徳院（鎌倉大仏）',
            'description' => '国宝の大仏を見学',
            'location' => '高徳院',
            'order' => 4,
        ]);

        $day->scheduleItems()->create([
            'time' => '15:00',
            'title' => '長谷寺',
            'description' => '紅葉の名所として有名。海が見える境内からの景色が絶景。',
            'location' => '長谷寺',
            'order' => 5,
        ]);

        $day->scheduleItems()->create([
            'time' => '17:00',
            'title' => '江ノ島へ移動',
            'description' => '江ノ電に乗って江ノ島へ',
            'location' => '江ノ島',
            'transport_type' => 'train',
            'transport_from' => '長谷駅',
            'transport_to' => '江ノ島駅',
            'transport_duration' => 15,
            'transport_cost' => 260,
            'order' => 6,
        ]);

        // Create participants
        $plan->participants()->createMany([
            ['name' => '田中太郎', 'contact' => 'tanaka@example.com'],
            ['name' => '佐藤花子', 'contact' => '090-1234-5678'],
            ['name' => '鈴木一郎'],
        ]);

        // Create checklist items
        $plan->checklistItems()->createMany([
            ['category' => '持ち物', 'item' => '財布', 'order' => 1],
            ['category' => '持ち物', 'item' => 'スマートフォン', 'order' => 2],
            ['category' => '持ち物', 'item' => 'モバイルバッテリー', 'order' => 3],
            ['category' => '持ち物', 'item' => 'カメラ', 'order' => 4],
            ['category' => '服装', 'item' => '歩きやすい靴', 'order' => 5],
            ['category' => '服装', 'item' => '帽子', 'order' => 6],
            ['category' => 'その他', 'item' => '折り畳み傘', 'order' => 7],
            ['category' => 'その他', 'item' => 'ハンカチ・ティッシュ', 'order' => 8],
        ]);

        // Create another plan
        $plan2 = \App\Models\Plan::create([
            'title' => '箱根温泉旅行 1泊2日',
            'description' => '友人たちと箱根の温泉でゆっくり',
            'start_date' => '2025-12-15',
            'end_date' => '2025-12-16',
            'is_public' => true,
            'memo' => '宿泊先: 箱根湯本温泉 旅館「湯の花」\n住所: 神奈川県足柄下郡箱根町湯本...\n電話: 0460-85-XXXX',
        ]);

        // Day 1
        $day1 = $plan2->days()->create([
            'date' => '2025-12-15',
            'day_number' => 1,
            'title' => '箱根到着・観光',
        ]);

        $day1->scheduleItems()->createMany([
            [
                'time' => '10:00',
                'title' => '箱根湯本駅集合',
                'location' => '箱根湯本駅',
                'order' => 1,
            ],
            [
                'time' => '10:30',
                'title' => '箱根登山鉄道で強羅へ',
                'transport_type' => 'train',
                'transport_from' => '箱根湯本駅',
                'transport_to' => '強羅駅',
                'transport_duration' => 40,
                'order' => 2,
            ],
            [
                'time' => '12:00',
                'title' => '昼食（強羅周辺）',
                'location' => '強羅',
                'order' => 3,
            ],
            [
                'time' => '14:00',
                'title' => '箱根彫刻の森美術館',
                'description' => '野外彫刻を楽しむ',
                'location' => '箱根彫刻の森美術館',
                'order' => 4,
            ],
            [
                'time' => '16:00',
                'title' => '旅館チェックイン',
                'description' => '温泉でゆっくり',
                'location' => '箱根湯本温泉 旅館「湯の花」',
                'order' => 5,
            ],
        ]);

        // Day 2
        $day2 = $plan2->days()->create([
            'date' => '2025-12-16',
            'day_number' => 2,
            'title' => '箱根観光・帰路',
        ]);

        $day2->scheduleItems()->createMany([
            [
                'time' => '09:00',
                'title' => '朝食',
                'order' => 1,
            ],
            [
                'time' => '10:00',
                'title' => 'チェックアウト',
                'order' => 2,
            ],
            [
                'time' => '10:30',
                'title' => '箱根神社参拝',
                'location' => '箱根神社',
                'order' => 3,
            ],
            [
                'time' => '12:00',
                'title' => '芦ノ湖周辺でランチ',
                'location' => '芦ノ湖',
                'order' => 4,
            ],
            [
                'time' => '14:00',
                'title' => '解散・帰路',
                'order' => 5,
            ],
        ]);

        $plan2->participants()->createMany([
            ['name' => '山田太郎'],
            ['name' => '高橋美咲'],
        ]);

        $this->command->info('Sample data created successfully!');
    }
}

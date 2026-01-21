<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Participant;
use Illuminate\Database\Seeder;

class SampleParticipantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 最初のプランを取得（またはIDを指定）
        $plan = Plan::first();

        if (!$plan) {
            $this->command->warn('プランが見つかりません。先にプランを作成してください。');
            return;
        }

        $participants = [
            ['id' => '20230001', 'name' => '桑原　遥希', 'furigana' => 'くわばら　はるき', 'class' => '3年 3特進', 'email' => '20230001@seiei.ac.jp'],
            ['id' => '20230002', 'name' => '田代　隼人', 'furigana' => 'たしろ　はやと', 'class' => '3年 3特進', 'email' => '20230002@seiei.ac.jp'],
            ['id' => '20230003', 'name' => '田浦　優', 'furigana' => 'たのうら　ゆう', 'class' => '3年 3特進', 'email' => '20230003@seiei.ac.jp'],
            ['id' => '20230004', 'name' => '寺内　暁人', 'furigana' => 'てらうち　あきひと', 'class' => '3年 3特進', 'email' => '20230004@seiei.ac.jp'],
            ['id' => '20230005', 'name' => '野村　悠月', 'furigana' => 'のむら　ゆづき', 'class' => '3年 3特進', 'email' => '20230005@seiei.ac.jp'],
            ['id' => '20230006', 'name' => '弘政　君輝', 'furigana' => 'ひろまさ　きみてる', 'class' => '3年 3特進', 'email' => '20230006@seiei.ac.jp'],
            ['id' => '20230007', 'name' => '三井　滉', 'furigana' => 'みつい　ひろし', 'class' => '3年 3特進', 'email' => '20230007@seiei.ac.jp'],
            ['id' => '20230009', 'name' => '大井　ひなた', 'furigana' => 'おおい　ひなた', 'class' => '3年 3特進', 'email' => '20230009@seiei.ac.jp'],
            ['id' => '20230010', 'name' => '熊毛　美月', 'furigana' => 'くまげ　みづき', 'class' => '3年 3特進', 'email' => '20230010@seiei.ac.jp'],
            ['id' => '20230011', 'name' => '藏本　美優', 'furigana' => 'くらもと　みゆ', 'class' => '3年 3特進', 'email' => '20230011@seiei.ac.jp'],
            ['id' => '20230012', 'name' => '清木　美来', 'furigana' => 'せいき　みき', 'class' => '3年 3特進', 'email' => '20230012@seiei.ac.jp'],
            ['id' => '20230013', 'name' => '藤井　蒼', 'furigana' => 'ふじい　あおい', 'class' => '3年 3特進', 'email' => '20230013@seiei.ac.jp'],
            ['id' => '20230014', 'name' => '白潟　侑磨', 'furigana' => 'しらかた　ゆうま', 'class' => '3年 3進学', 'email' => '20230014@seiei.ac.jp'],
            ['id' => '20230015', 'name' => '白川　陽太', 'furigana' => 'しらかわ　ひなた', 'class' => '3年 3進学', 'email' => '20230015@seiei.ac.jp'],
            ['id' => '20230016', 'name' => '田中　哲平', 'furigana' => 'たなか　てっぺい', 'class' => '3年 3進学', 'email' => '20230016@seiei.ac.jp'],
            ['id' => '20230017', 'name' => '松冨　隼大', 'furigana' => 'まつとみ　はやと', 'class' => '3年 3進学', 'email' => '20230017@seiei.ac.jp'],
            ['id' => '20230018', 'name' => '村上　巧', 'furigana' => 'むらかみ　たくみ', 'class' => '3年 3進学', 'email' => '20230018@seiei.ac.jp'],
            ['id' => '20230019', 'name' => '村田　駿平', 'furigana' => 'むらた　しゅんぺい', 'class' => '3年 3進学', 'email' => '20230019@seiei.ac.jp'],
            ['id' => '20230020', 'name' => '森重　綾斗', 'furigana' => 'もりしげ　あやと', 'class' => '3年 3進学', 'email' => '20230020@seiei.ac.jp'],
            ['id' => '20230021', 'name' => '相嶋　真紀', 'furigana' => 'あいじま　まき', 'class' => '3年 3進学', 'email' => '20230021@seiei.ac.jp'],
        ];

        foreach ($participants as $participantData) {
            Participant::create([
                'plan_id' => $plan->id,
                'name' => $participantData['name'] . "\n(" . $participantData['furigana'] . ")",
                'email' => $participantData['email'],
                'class_name' => $participantData['class'],
                'contact' => $participantData['id'], // 学籍番号を連絡先フィールドに保存
            ]);
        }

        $this->command->info('サンプル参加者データを追加しました。');
    }
}

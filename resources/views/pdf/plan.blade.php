<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $plan->title }}</title>
    <style>
        * {
            font-family: ipaexg, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body { 
            font-family: ipaexg, sans-serif;
            font-size: 10pt; 
            line-height: 1.5; 
            padding: 15mm;
        }
        
        /* ヘッダー - 旅のしおりスタイル */
        .header { 
            text-align: center; 
            margin-bottom: 15px;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 5px;
        }
        .header h1 { 
            font-family: ipaexg, sans-serif;
            font-size: 20pt; 
            margin: 0 0 8px 0; 
            font-weight: bold;
            letter-spacing: 2px;
        }
        .header .date-range {
            font-size: 11pt;
            margin-top: 5px;
            font-weight: bold;
        }
        .header .travel-info {
            font-size: 9pt;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid rgba(255,255,255,0.3);
        }
        
        /* 基本情報ボックス */
        .info-box {
            display: table;
            width: 100%;
            margin-bottom: 12px;
            border: 2px solid #667eea;
            border-radius: 5px;
            overflow: hidden;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            width: 25%;
            padding: 8px 10px;
            background-color: #f0f0f0;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
        }
        .info-value {
            display: table-cell;
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
            background-color: white;
            vertical-align: middle;
        }
        
        /* セクション */
        .section { 
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .section-title { 
            font-family: ipaexg, sans-serif;
            font-size: 13pt; 
            font-weight: bold; 
            margin: 10px 0 8px 0; 
            padding: 5px 10px;
            background-color: #667eea;
            color: white;
            border-radius: 3px;
        }
        
        /* スケジュール */
        .day-block { 
            margin: 8px 0; 
            padding: 0;
            background-color: #fff; 
            border: 2px solid #667eea;
            border-radius: 5px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .day-header { 
            font-family: ipaexg, sans-serif;
            font-weight: bold; 
            padding: 8px 12px;
            background-color: #667eea;
            color: white;
            font-size: 12pt;
        }
        .day-content {
            padding: 10px 12px;
        }
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .schedule-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: top;
        }
        .schedule-table tr:last-child td {
            border-bottom: none;
        }
        .time-cell {
            width: 60px;
            font-weight: bold;
            color: #667eea;
            white-space: nowrap;
        }
        .activity-cell {
            width: auto;
        }
        .activity-title {
            font-weight: bold;
            margin-bottom: 3px;
        }
        .activity-detail {
            font-size: 9pt;
            color: #555;
            margin: 2px 0;
        }
        .activity-transport {
            font-size: 9pt;
            color: #764ba2;
            margin-top: 4px;
            padding: 3px 6px;
            background-color: #f9f7fd;
            border-left: 3px solid #764ba2;
            display: inline-block;
        }
        
        /* チェックリスト */
        .checklist-group {
            margin: 10px 0;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        .checklist-category {
            font-weight: bold;
            margin-bottom: 6px;
            color: #667eea;
            font-size: 11pt;
        }
        .checklist-item {
            margin: 4px 0 4px 15px;
            font-size: 9.5pt;
        }
        .checkbox {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid #667eea;
            margin-right: 6px;
            vertical-align: middle;
            text-align: center;
            line-height: 12px;
        }
        .checkbox.checked {
            background-color: #667eea;
            color: white;
        }
        
        /* メモ欄 */
        .memo-box {
            padding: 12px;
            background-color: #fffef7;
            border: 2px solid #ffd700;
            border-radius: 5px;
            min-height: 80px;
        }
        
        /* 参加者リスト */
        .participants-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .participant-badge {
            padding: 5px 12px;
            background-color: #667eea;
            color: white;
            border-radius: 15px;
            font-size: 9.5pt;
            display: inline-block;
        }
        
        /* ページブレーク制御 */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- ヘッダー -->
    <div class="header">
        <h1>{{ $plan->title }}</h1>
        <div class="date-range">
            {{ date('Y年m月d日', strtotime($plan->start_date)) }} 
            〜 
            {{ date('Y年m月d日', strtotime($plan->end_date)) }}
            @php
                $start = new DateTime($plan->start_date);
                $end = new DateTime($plan->end_date);
                $diff = $start->diff($end);
                $days = $diff->days + 1;
            @endphp
            （{{ $days }}日間）
        </div>
        @if($plan->description)
        <div class="travel-info">{{ $plan->description }}</div>
        @endif
    </div>

    <!-- 基本情報 -->
    @if($plan->participants && count($plan->participants) > 0)
    <div class="section">
        <div class="section-title">参加者</div>
        <div style="padding: 10px;">
            @foreach($plan->participants as $participant)
                <span class="participant-badge">{{ $participant->name }}</span>
            @endforeach
        </div>
    </div>
    @endif

    <!-- スケジュール -->
    @if($plan->days && count($plan->days) > 0)
    <div class="section">
        <div class="section-title">旅のスケジュール</div>
        @foreach($plan->days as $day)
        <div class="day-block">
            <div class="day-header">
                {{ $day->day_number }}日目 - {{ date('n月j日', strtotime($day->date)) }}（{{ ['日','月','火','水','木','金','土'][date('w', strtotime($day->date))] }}）
            </div>
            <div class="day-content">
                @if($day->scheduleItems && count($day->scheduleItems) > 0)
                    <table class="schedule-table">
                        @foreach($day->scheduleItems as $item)
                        <tr>
                            <td class="time-cell">
                                @php
                                    // 時刻部分のみを抽出（HH:MM形式）
                                    if (strpos($item->time, ' ') !== false) {
                                        // "2025-11-10 09:00:00" の形式の場合
                                        $timeParts = explode(' ', $item->time);
                                        $timeOnly = substr($timeParts[1], 0, 5); // HH:MM
                                    } else {
                                        // 既に "09:00" のような形式の場合
                                        $timeOnly = substr($item->time, 0, 5);
                                    }
                                @endphp
                                {{ $timeOnly }}
                            </td>
                            <td class="activity-cell">
                                <div class="activity-title">{{ $item->title }}</div>
                                @if($item->location)
                                    <div class="activity-detail">📍 {{ $item->location }}</div>
                                @endif
                                @if($item->description)
                                    <div class="activity-detail">{{ $item->description }}</div>
                                @endif
                                @if($item->transport_type)
                                    <div class="activity-transport">
                                        🚃 {{ $item->transport_from ?? '' }}
                                        @if($item->transport_from && $item->transport_to) → @endif
                                        {{ $item->transport_to ?? '' }}
                                        @if($item->transport_duration)
                                            （{{ $item->transport_duration }}分）
                                        @endif
                                        @if($item->transport_cost)
                                            ¥{{ number_format($item->transport_cost) }}
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </table>
                @else
                    <div style="padding: 10px; color: #999; text-align: center;">予定なし</div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- チェックリスト -->
    @if(isset($checklistItems) && count($checklistItems) > 0)
    <div class="section">
        <div class="section-title">持ち物チェックリスト</div>
        @foreach($checklistItems as $category => $items)
            <div class="checklist-group">
                <div class="checklist-category">{{ $category }}</div>
                @php
                    $hasNonEmptyItems = false;
                @endphp
                @foreach($items as $item)
                    @if(!empty(trim($item->item ?? '')))
                        @php $hasNonEmptyItems = true; @endphp
                        <div class="checklist-item">
                            <span class="checkbox @if($item->is_checked) checked @endif">
                                @if($item->is_checked)✓@endif
                            </span>
                            {{ $item->item }}
                        </div>
                    @endif
                @endforeach
                @if(!$hasNonEmptyItems)
                    <div class="checklist-item" style="color: #999; font-style: italic;">項目が入力されていません</div>
                @endif
            </div>
        @endforeach
    </div>
    @endif

    <!-- メモ -->
    @if($plan->memo)
    <div class="section">
        <div class="section-title">メモ</div>
        <div class="memo-box">{{ $plan->memo }}</div>
    </div>
    @endif

    <!-- 添付ファイル一覧 -->
    @if(isset($attachments) && count($attachments) > 0)
    <div class="section">
        <div class="section-title">添付ファイル</div>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50%; text-align: left;">ファイル名</th>
                    <th style="width: 20%; text-align: center;">種類</th>
                    <th style="width: 15%; text-align: right;">サイズ</th>
                    <th style="width: 15%; text-align: center;">追加日</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attachments as $attachment)
                <tr>
                    <td style="text-align: left;">
                        <strong>{{ $attachment->original_name }}</strong>
                    </td>
                    <td style="text-align: center;">
                        @if($attachment->isImage())
                            📷 画像
                        @elseif($attachment->isPdf())
                            📄 PDF
                        @else
                            📎 {{ strtoupper($attachment->getExtension()) }}
                        @endif
                    </td>
                    <td style="text-align: right;">{{ $attachment->getFormattedSize() }}</td>
                    <td style="text-align: center;">{{ $attachment->created_at->format('Y/m/d') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</body>
</html>

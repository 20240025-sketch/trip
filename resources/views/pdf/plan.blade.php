<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>{{ $plan->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #333;
        }
        
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 3px solid #3b82f6;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 24pt;
            color: #1e40af;
            margin-bottom: 10px;
        }
        
        .header .dates {
            font-size: 14pt;
            color: #666;
        }
        
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 16pt;
            font-weight: bold;
            color: #1e40af;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        
        .day-title {
            font-size: 14pt;
            font-weight: bold;
            background-color: #dbeafe;
            padding: 8px 12px;
            margin: 20px 0 10px 0;
            border-left: 4px solid #3b82f6;
        }
        
        .schedule-item {
            margin-bottom: 15px;
            padding: 10px;
            border-left: 3px solid #e5e7eb;
            padding-left: 15px;
        }
        
        .schedule-time {
            font-weight: bold;
            color: #1e40af;
            font-size: 13pt;
        }
        
        .schedule-title {
            font-size: 13pt;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .schedule-location {
            color: #666;
            font-style: italic;
            margin: 3px 0;
        }
        
        .schedule-description {
            margin-top: 8px;
            line-height: 1.5;
        }
        
        .transport {
            background-color: #f0f9ff;
            padding: 8px;
            margin: 10px 0;
            border-radius: 4px;
        }
        
        .participants {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .participant {
            background-color: #f3f4f6;
            padding: 8px 12px;
            border-radius: 4px;
        }
        
        .checklist {
            list-style: none;
        }
        
        .checklist-item {
            padding: 5px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .checklist-category {
            font-weight: bold;
            color: #1e40af;
            margin-top: 15px;
            margin-bottom: 10px;
        }
        
        .memo {
            background-color: #fffbeb;
            padding: 15px;
            border-left: 4px solid #f59e0b;
            white-space: pre-wrap;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ $plan->title }}</h1>
        <div class="dates">
            {{ $plan->start_date->format('Y年m月d日') }} 〜 {{ $plan->end_date->format('Y年m月d日') }}
        </div>
        @if($plan->description)
            <p style="margin-top: 10px;">{{ $plan->description }}</p>
        @endif
    </div>

    <!-- Participants -->
    @if($participants->count() > 0)
        <div class="section">
            <div class="section-title">参加者</div>
            <div class="participants">
                @foreach($participants as $participant)
                    <div class="participant">
                        {{ $participant->name }}
                        @if($participant->contact)
                            <br><small>{{ $participant->contact }}</small>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Schedule -->
    <div class="section">
        <div class="section-title">スケジュール</div>
        
        @foreach($days as $day)
            <div class="day-title">
                {{ $day->date->format('m月d日') }} ({{ ['日', '月', '火', '水', '木', '金', '土'][$day->date->dayOfWeek] }})
                @if($day->title)
                    - {{ $day->title }}
                @endif
            </div>
            
            @foreach($day->scheduleItems as $item)
                <div class="schedule-item">
                    @if($item->time)
                        <div class="schedule-time">{{ $item->time->format('H:i') }}</div>
                    @endif
                    
                    <div class="schedule-title">{{ $item->title }}</div>
                    
                    @if($item->location)
                        <div class="schedule-location">📍 {{ $item->location }}</div>
                    @endif
                    
                    @if($item->description)
                        <div class="schedule-description">{{ $item->description }}</div>
                    @endif
                    
                    @if($item->transport_type)
                        <div class="transport">
                            🚃 {{ $item->transport_from }} → {{ $item->transport_to }}
                            @if($item->transport_duration)
                                (約{{ $item->transport_duration }}分)
                            @endif
                            @if($item->transport_cost)
                                | ¥{{ number_format($item->transport_cost) }}
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        @endforeach
    </div>

    <!-- Checklist -->
    @if($checklistItems->count() > 0)
        <div class="section page-break">
            <div class="section-title">持ち物チェックリスト</div>
            
            @foreach($checklistItems as $category => $items)
                @if($category)
                    <div class="checklist-category">{{ $category }}</div>
                @endif
                
                <ul class="checklist">
                    @foreach($items as $item)
                        <li class="checklist-item">
                            ☐ {{ $item->item }}
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </div>
    @endif

    <!-- Memo -->
    @if($plan->memo)
        <div class="section">
            <div class="section-title">メモ・注意事項</div>
            <div class="memo">{{ $plan->memo }}</div>
        </div>
    @endif
</body>
</html>

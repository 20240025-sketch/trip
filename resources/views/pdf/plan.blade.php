<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $plan->title }}</title>
    <style>
        * {
            font-family: ipaexg, sans-serif;
        }
        body { 
            font-family: ipaexg, sans-serif;
            font-size: 11pt; 
            line-height: 1.6; 
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            padding-bottom: 10px; 
            border-bottom: 2px solid #333; 
        }
        .header h1 { 
            font-family: ipaexg, sans-serif;
            font-size: 18pt; 
            margin: 0 0 10px 0; 
            font-weight: bold;
        }
        .section { margin-bottom: 15px; }
        .section-title { 
            font-family: ipaexg, sans-serif;
            font-size: 13pt; 
            font-weight: bold; 
            margin: 10px 0 5px 0; 
            padding-bottom: 3px;
            border-bottom: 1px solid #ccc; 
        }
        .day-block { 
            margin: 10px 0; 
            padding: 8px; 
            background-color: #f5f5f5; 
            border-left: 3px solid #666;
        }
        .day-title { 
            font-family: ipaexg, sans-serif;
            font-weight: bold; 
            margin-bottom: 5px; 
        }
        .item { 
            margin: 5px 0; 
            padding-left: 10px; 
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $plan->title }}</h1>
        <div>{{ date('Y年m月d日', strtotime($plan->start_date)) }} - {{ date('Y年m月d日', strtotime($plan->end_date)) }}</div>
    </div>

    @if($plan->description)
    <div class="section">
        <div class="section-title">説明</div>
        <div>{{ $plan->description }}</div>
    </div>
    @endif

    @if($plan->participants && count($plan->participants) > 0)
    <div class="section">
        <div class="section-title">参加者</div>
        <div>
            @foreach($plan->participants as $participant)
                {{ $participant->name }}@if(!$loop->last), @endif
            @endforeach
        </div>
    </div>
    @endif

    @if($plan->days && count($plan->days) > 0)
    <div class="section">
        <div class="section-title">スケジュール</div>
        @foreach($plan->days as $day)
        <div class="day-block">
            <div class="day-title">{{ $day->day_number }}日目: {{ date('Y/m/d', strtotime($day->date)) }}</div>
            @if($day->scheduleItems && count($day->scheduleItems) > 0)
                @foreach($day->scheduleItems as $item)
                <div class="item">
                    <strong>{{ $item->time }}</strong>
                    @if($item->location) - 場所: {{ $item->location }}@endif
                    @if($item->transport) - 移動: {{ $item->transport }}@endif
                    @if($item->description)<div>{{ $item->description }}</div>@endif
                    @if($item->cost)<div>費用: {{ number_format($item->cost) }}円</div>@endif
                </div>
                @endforeach
            @else
                <div>予定なし</div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    @if(isset($checklistItems) && count($checklistItems) > 0)
    <div class="section">
        <div class="section-title">チェックリスト</div>
        @foreach($checklistItems as $category => $items)
            <div style="margin: 10px 0;">
                <strong>{{ $category }}</strong>
                @foreach($items as $item)
                <div>[ ] {{ $item->item_name }}</div>
                @endforeach
            </div>
        @endforeach
    </div>
    @endif

    @if($plan->memo)
    <div class="section">
        <div class="section-title">メモ</div>
        <div>{{ $plan->memo }}</div>
    </div>
    @endif
</body>
</html>

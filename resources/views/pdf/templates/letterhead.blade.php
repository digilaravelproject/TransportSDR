<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
            margin: 0;
            padding: 0;
        }

        .letterhead-header {
            border-bottom: 3px solid #1a1a2e;
            padding: 20px 30px 14px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }

        .co-left {}

        .co-name {
            font-size: 22px;
            font-weight: bold;
            color: #1a1a2e;
        }

        .co-info {
            font-size: 10px;
            color: #666;
            margin-top: 4px;
            line-height: 1.6;
        }

        .co-right {
            text-align: right;
        }

        .content-area {
            padding: 0 30px;
            min-height: 500px;
        }

        .meta-row {
            margin-bottom: 16px;
        }

        .meta-row td {
            padding: 2px 8px 2px 0;
            font-size: 11px;
        }

        .subject-line {
            font-size: 13px;
            font-weight: bold;
            margin: 16px 0 20px;
            text-decoration: underline;
        }

        .letter-body {
            line-height: 1.8;
            font-size: 12px;
        }

        .letterhead-footer {
            border-top: 2px solid #1a1a2e;
            padding: 10px 30px;
            font-size: 9px;
            color: #888;
            text-align: center;
            margin-top: 40px;
        }

        .sig-area {
            margin-top: 50px;
        }
    </style>
</head>

<body>

    <div class="letterhead-header">
        <div class="co-left">
            <div class="co-name">{{ $tenant->company_name }}</div>
            <div class="co-info">
                @if (!empty($tenant->address))
                    {{ $tenant->address }}<br>
                @endif
                @if (!empty($tenant->phone))
                    Phone: {{ $tenant->phone }}
                @endif
                @if (!empty($tenant->email))
                    | Email: {{ $tenant->email }}
                @endif
                @if (!empty($tenant->gstin))
                    <br>GSTIN: {{ $tenant->gstin }}
                @endif
            </div>
        </div>
        <div class="co-right">
            <div style="font-size:11px;color:#666;margin-top:8px;">
                @if (!empty($ref))
                    <strong>Ref:</strong> {{ $ref }}<br>
                @endif
                <strong>Date:</strong> {{ $date }}
            </div>
        </div>
    </div>

    <div class="content-area">
        @if (!empty($to))
            <div class="meta-row">
                <strong>To,</strong><br>
                {!! nl2br(e($to)) !!}
            </div>
        @endif

        @if (!empty($subject))
            <div class="subject-line">Subject: {{ $subject }}</div>
        @endif

        <div class="letter-body">
            {!! nl2br(e($content)) !!}
        </div>

        <div class="sig-area">
            <div style="margin-top:50px;">
                <div style="border-top:1px solid #333; width:200px; padding-top:4px; font-size:11px;">
                    Authorized Signatory<br>
                    <strong>{{ $tenant->company_name }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="letterhead-footer">
        {{ $tenant->company_name }}
        @if (!empty($tenant->phone))
            | {{ $tenant->phone }}
        @endif
        @if (!empty($tenant->email))
            | {{ $tenant->email }}
        @endif
        @if (!empty($tenant->gstin))
            | GSTIN: {{ $tenant->gstin }}
        @endif
    </div>

</body>

</html>

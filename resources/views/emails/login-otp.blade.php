{{-- resources/views/emails/login-otp.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
</head>

<body style="font-family:Arial,sans-serif;background:#f5f5f5;padding:20px;">
    <div style="max-width:460px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;">
        <div style="background:#1a1a2e;padding:20px;text-align:center;">
            <h2 style="color:#fff;margin:0;">Transport SaaS</h2>
        </div>
        <div style="padding:30px;">
            <p>Hello <strong>{{ $userName }}</strong>,</p>
            <p>Your Login OTP:</p>
            <div
                style="background:#f0f4ff;border:2px dashed #4a6cf7;border-radius:8px;text-align:center;padding:20px;margin:20px 0;">
                <div style="font-size:38px;font-weight:bold;color:#1a1a2e;letter-spacing:10px;">{{ $otp }}</div>
                <div style="font-size:12px;color:#888;margin-top:6px;">This OTP will expire in 10 minutes</div>
            </div>
            <p style="color:#999;font-size:12px;">If you did not request this, please ignore this email.</p>
        </div>
    </div>
</body>

</html>

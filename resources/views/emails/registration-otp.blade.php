<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
</head>

<body style="font-family:Arial,sans-serif;background:#f5f5f5;padding:20px;">
    <div style="max-width:460px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;">
        <div style="background:#E8650A;padding:20px;text-align:center;">
            <h2 style="color:#fff;margin:0;">Vendor Registration</h2>
            <p style="color:#ffe0c4;margin:4px 0 0;font-size:13px;">Create your bus vendor account</p>
        </div>
        <div style="padding:30px;">
            <p style="color:#333;">Hello <strong>{{ $userName }}</strong>,</p>
            <p style="color:#555;">Please verify your email address to complete your registration.</p>
            <div
                style="background:#fff5ee;border:2px dashed #E8650A;border-radius:8px;text-align:center;padding:20px;margin:20px 0;">
                <div style="font-size:38px;font-weight:bold;color:#E8650A;letter-spacing:10px;">{{ $otp }}
                </div>
                <div style="font-size:12px;color:#888;margin-top:6px;">This OTP expires in 10 minutes</div>
            </div>
            <p style="color:#999;font-size:12px;">If you did not attempt to register, please ignore this email.</p>
        </div>
    </div>
</body>

</html>

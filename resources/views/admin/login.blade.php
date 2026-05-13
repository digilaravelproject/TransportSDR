<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - TransportSDR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/admin-dashboard.css') }}" rel="stylesheet">
    <style>
        html, body { height: 100%; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(90deg, #232526 0%, #414345 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 24px;
        }
        .login-shell {
            width: 460px;
            max-width: 96%;
        }
        .login-card {
            /* background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); */
            background: linear-gradient(90deg, #232526 0%, #414345 100%);
            border-radius: 12px;
            padding: 0;
            box-shadow: 0 12px 36px rgba(2,6,23,0.6);
            overflow: hidden;
        }
        .login-header {
            /* background: linear-gradient(90deg,#15303a,#1f4b66);
             */
            background: linear-gradient(90deg, #232526 0%, #414345 100%);
            color: #e6eef8;
            padding: 32px 24px;
            text-align: center;
        }
        .login-header h3 { margin: 0 0 6px; font-weight:700 }
        .login-body { padding: 20px; }
        .form-control { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.04); color: #e6eef8 }
        .btn-login { background: linear-gradient(90deg, #464a53, #757984); border: none; color:#fff }
        .login-footer { padding: 12px 20px 20px; text-align: center; color: #9aa7b7; font-size: 0.9rem }
    </style>
</head>
<body>
    <div class="login-shell">
        <div class="login-card">
            <div class="login-header">
                <h3>Admin Login</h3>
                <p style="margin:0; color:#9aa7b7">TransportSDR Admin Panel</p>
            </div>
            <div class="login-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.login') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-login">Login</button>
                            </div>
                        </form>
            </div>
            <div class="login-footer">Enter your admin credentials to continue</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
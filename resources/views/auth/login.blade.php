<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ចូលប្រព័ន្ធ | វិទ្យាស្ថាន សន្តប៉ូល</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('backend/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/dist/css/loading.css') }}">
    <!-- Custom Font for Khmer -->
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Battambang', sans-serif;
            background-color: #efefef;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .login-box {
            background-color: #ffffff;
            width: 100%;
            max-width: 550px;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-logo img {
            max-width: 150px;
            height: auto;
        }
        .login-subtitle {
            text-align: center;
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 30px;
        }
        .form-group label {
            font-size: 0.9rem;
            color: #333;
            font-weight: normal;
            margin-bottom: 5px;
        }
        .text-danger {
            color: #dc3545;
        }
        .form-control {
            background-color: #e8f0fe;
            border: 1px solid #ced4da;
            border-radius: 0;
            padding: 10px 12px;
            height: auto;
        }
        .form-control:focus {
            background-color: #e8f0fe;
            border-color: #80bdff;
            box-shadow: none;
        }
        .error-text {
            color: #dc3545;
            font-size: 0.8rem;
            margin-top: 5px;
            display: block;
        }
        .btn-login {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
            border-radius: 0;
            padding: 10px;
            font-size: 1rem;
            margin-top: 20px;
            transition: background-color 0.2s;
        }
        .btn-login:hover {
            background-color: #218838;
            border-color: #1e7e34;
            color: white;
        }
    </style>
</head>
<body>

<div class="login-box">
    <div class="login-logo">
        <img src="{{ asset('backend/dist/img/spilogo.png') }}" alt="SPI Logo">
    </div>
    
    <div class="login-subtitle">
        សូមបញ្ចូលព័ត៌មានអ្នកប្រើប្រាស់របស់អ្នក!!!
    </div>

    <form action="{{ route('login.post') }}" method="post">
        @csrf
        <div class="form-group mb-3">
            <label>ឈ្មោះគណនី (អ៊ីមែល) <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
            @error('email')
                <span class="error-text">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="form-group mb-3">
            <label>លេខសម្ងាត់ <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control" required>
            @error('password')
                <span class="error-text">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-login btn-block">ចូល</button>
    </form>
</div>

<script src="{{ asset('backend/dist/js/loading.js') }}"></script>
</body>
</html>

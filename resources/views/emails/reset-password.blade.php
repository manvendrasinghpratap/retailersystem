<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Reset Password</title>
</head>

<body style="margin:0; padding:0; background:#eef2f7; font-family:Arial;">

<table width="100%" cellpadding="0" cellspacing="0" style="padding:20px;">
<tr>
<td align="center">

<table width="600" style="background:#fff; border-radius:10px; overflow:hidden;">

<!-- Header -->
<tr>
<td style="background:#667eea; padding:20px; text-align:center; color:#fff;">
    <h2>{{ $app_name }}</h2>
</td>
</tr>

<!-- Body -->
<tr>
<td style="padding:30px;">
    <h3>Hello {{ $user->name }},</h3>

    <p>We received a request to reset your password.</p>

    <p style="text-align:center;">
        <a href="{{ $reset_link }}" 
           style="background:#667eea; color:#fff; padding:12px 20px;
           text-decoration:none; border-radius:5px;">
           Reset Password
        </a>
    </p>

    <p>This link will expire in {{ $expiry_time }}.</p>

    <p>If you didn't request this, ignore this email.</p>
</td>
</tr>

<!-- Footer -->
<tr>
<td style="background:#f5f5f5; padding:15px; text-align:center; font-size:12px;">
    © {{ date('Y') }} {{ $app_name }}
</td>
</tr>

</table>

</td>
</tr>
</table>

</body>
</html>
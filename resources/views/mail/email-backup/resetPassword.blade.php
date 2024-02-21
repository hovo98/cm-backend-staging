<h3>Hello</h3>

<p>You are receiving this email because we received a password reset request for your account.</p>

<a href="{{ $url }}">Reset Password</a>

<p>This password reset link will expire in {{ $count }} minutes.</p>

<p>If you did not request a password reset, no further action is required.</p>

<p>Regards,
Commercial Mortgage</p>

<hr>

<p style="font-size: smaller">If you’re having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:
    <a href="{{ $url }}">{{ $url }}</a></p>

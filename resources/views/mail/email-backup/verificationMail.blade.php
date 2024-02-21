<h3>Hello</h3>

<p>You are receiving this email because you created account.</p>

<a href="{{ $url }}">Verify Account</a>

<p>This confirmation link will expire in {{ $count }} minutes.</p>

<p>If you did not created account, no further action is required.</p>

<p>Regards,
    Commercial Mortgage</p>

<hr>

<p style="font-size: smaller">If you’re having trouble clicking the "Verify Account" button, copy and paste the URL below into your web browser:
    <a href="{{ $url }}">{{ $url }}</a></p>

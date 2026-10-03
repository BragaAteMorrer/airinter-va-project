<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body style="margin:0;padding:0;background:#eef3f8;font-family:Arial,Helvetica,sans-serif;color:#17345f">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#eef3f8">
  <tr><td>
    <table width="100%" cellpadding="0" cellspacing="0"><tr>
      <td width="33.33%" height="7" bgcolor="#1765e9"></td>
      <td width="33.33%" height="7" bgcolor="#e8384f"></td>
      <td width="33.33%" height="7" bgcolor="#0b2857"></td>
    </tr></table>
  </td></tr>
  <tr><td align="center" style="padding:28px 12px 20px;background:#fff">
    <a href="{{ url('/') }}" style="text-decoration:none;color:#0b2857">
      <img src="{{ url('/promethee-assets/logos/air-inter-1980.png') }}" alt="Air Inter" style="display:block;max-height:62px;width:auto;margin:0 auto 9px">
      <strong style="font-size:16px;letter-spacing:.12em">AIR INTER</strong>
      <span style="display:block;margin-top:4px;color:#6a7d98;font-size:9px;font-weight:bold;letter-spacing:.14em">VIRTUAL AIRLINES · PROMÉTHÉE</span>
    </a>
  </td></tr>
  <tr><td style="padding:28px 12px">
    <table width="600" align="center" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;max-width:600px;background:#fff;border:1px solid #d9e4f1;border-radius:14px">
      <tr><td style="padding:38px">
        <h1 style="margin:0 0 18px;color:#0b2857;font-size:24px">
          @if ($level === 'error') Information importante @else Bonjour, @endif
        </h1>
        @foreach ($introLines as $line)
          <p style="margin:0 0 16px;color:#526b8d;font-size:15px;line-height:1.65">{{ $line }}</p>
        @endforeach
        @if (isset($actionText))
          <p style="margin:28px 0;text-align:center">
            <a href="{{ $actionUrl }}" target="_blank" style="display:inline-block;padding:12px 22px;border-radius:8px;background:#1765e9;color:#fff;font-size:13px;font-weight:bold;text-decoration:none">{{ $actionText }}</a>
          </p>
        @endif
        @foreach ($outroLines as $line)
          <p style="margin:0 0 16px;color:#526b8d;font-size:15px;line-height:1.65">{{ $line }}</p>
        @endforeach
        <p style="margin:24px 0 0;color:#526b8d;font-size:14px;line-height:1.6"><strong>Direction de l’Exploitation Aérienne</strong><br>Air Inter Virtual Airlines</p>
        @if (isset($actionText))
          <p style="margin-top:26px;padding-top:18px;border-top:1px solid #e0e8f2;color:#8492a6;font-size:11px;line-height:1.5">
            Si le bouton ne fonctionne pas : <a href="{{ $actionUrl }}" style="color:#1765e9">{{ $actionUrl }}</a>
          </p>
        @endif
      </td></tr>
    </table>
  </td></tr>
  <tr><td align="center" style="padding:0 20px 34px;color:#8492a6;font-size:11px">
    © {{ date('Y') }} Air Inter Virtual Airlines · Pourquoi vivre sans ailes !
  </td></tr>
</table>
</body>
</html>

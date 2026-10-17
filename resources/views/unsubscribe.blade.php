<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $brand->serviceName() }}</title>
</head>
<body style="margin:0;padding:0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;">
  <tr>
    <td align="center">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px;font-family:'Apple SD Gothic Neo','Malgun Gothic','Noto Sans KR',sans-serif;">
        @include('kr-mail-theme::partials.header')
        <tr>
          <td style="padding:16px;">
            <p id="el-012" data-state="{{ $succeeded ? 'success' : 'error' }}">
              @if ($succeeded)
                수신거부가 처리되었습니다.
              @else
                수신거부 처리에 실패했습니다.
              @endif
            </p>
          </td>
        </tr>
        @include('kr-mail-theme::partials.footer')
      </table>
    </td>
  </tr>
</table>
</body>
</html>

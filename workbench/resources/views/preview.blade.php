<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SCR-001</title>
</head>
<body id="scr-001" data-state="initial">
<h1>SCR-001 미리보기</h1>

<section aria-label="selectors">
  <label>EL-001
    <select id="el-001">
      <option value="informational" selected>Informational Mail</option>
      <option value="advertising">Advertising Mail</option>
    </select>
  </label>

  <label>EL-002
    <select id="el-002">
      <option value="receipt" selected>receipt</option>
      <option value="button">button</option>
      <option value="long">long</option>
      <option value="quote">quote</option>
    </select>
  </label>

  <label>EL-003
    <select id="el-003">
      <option value="full" selected>Full width</option>
      <option value="mobile">Mobile width</option>
    </select>
  </label>

  <label>EL-004
    <select id="el-004">
      <option value="light" selected>Light</option>
      <option value="dark">Dark</option>
    </select>
  </label>
</section>

<p id="el-005"></p>

<iframe id="el-006" title="EL-006 HTML preview" style="width:100%;height:480px;border:1px solid #cccccc;background:#ffffff;"></iframe>

<pre id="el-007" style="white-space:pre-wrap;"></pre>

<section aria-label="test delivery">
  <label>EL-008
    <input id="el-008" type="email" placeholder="recipient@example.com">
  </label>

  <button id="el-009" type="button" disabled>Send Test</button>

  <span id="el-010" hidden></span>

  <a id="el-011" href="{{ $mailpitUrl }}" target="_blank" rel="noopener">Mailpit</a>
</section>

<script>
{!! $script !!}
</script>
</body>
</html>

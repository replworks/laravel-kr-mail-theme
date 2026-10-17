<tr>
  <td style="padding:16px;font-size:12px;color:#888888;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
      @if ($brand->companyName() !== null)
        <tr><td>회사명: {{ $brand->companyName() }}</td></tr>
      @endif
      @if ($brand->customerCenter() !== null)
        <tr><td>고객센터: {{ $brand->customerCenter() }}</td></tr>
      @endif
      @if ($brand->businessRegistrationNumber() !== null)
        <tr><td>사업자등록번호: {{ $brand->businessRegistrationNumber() }}</td></tr>
      @endif
      @if ($brand->address() !== null)
        <tr><td>주소: {{ $brand->address() }}</td></tr>
      @endif
      @if ($brand->termsUrl() !== null)
        <tr><td><a href="{{ $brand->termsUrl() }}">이용약관</a></td></tr>
      @endif
    </table>
  </td>
</tr>

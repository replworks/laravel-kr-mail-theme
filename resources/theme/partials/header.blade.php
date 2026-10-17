<tr>
  <td style="padding:16px;">
    @if ($brand->hasLogo())
      <img src="{{ $brand->logo() }}" alt="{{ $brand->serviceName() }}" style="max-width:200px;height:auto;">
    @else
      <span>{{ \Replworks\KrMailTheme\BrandInformation::LOGO_PLACEHOLDER }}</span>
    @endif
    <div style="font-size:18px;font-weight:bold;">{{ $brand->serviceName() }}</div>
  </td>
</tr>

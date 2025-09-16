<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>coachtech勤怠管理</title>
  <link rel="stylesheet" href="{{ asset('css/reset.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('css/common.css') }}?v={{ time() }}">
  @yield('css')
</head>

<body>
  <div class="header">
    <header class="header__logo">
      <img src="{{ asset('images/logo.svg') }}" alt="ヘッダーロゴ">
      @yield('link')
    </header>
    <div class="content">
      @yield('content')
    </div>
  </div>
</body>

</html>
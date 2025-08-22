@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify_code.css') }}?v={{ time() }}">
@endsection

@section('content')
@if(session('code_error'))
  <div class="alert-message">
    {{ session('code_error') }}
  </div>
@endif
<div class="verify-form">
  <p class="verify-form-guide">
    認証メールから認証コードを確認して入力してください。
  </p>

  <form action="{{ route('verification.code.submit') }}" method="POST" id="codeForm">
  @csrf
    <div class="code-input-wrapper">
      @for ($i=0; $i<4; $i++)
        <input type="text" maxlength="1" class="code-input" name="code[]">
      @endfor
    </div>

    <button class="verify-resubmit" type="submit">
      認証する
    </button>
  </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const inputs = document.querySelectorAll('.code-input');

    inputs.forEach((input, index) => {
        input.addEventListener('input', function() {
            if (this.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && this.value === '' && index > 0) {
                inputs[index - 1].focus();
            }
        });
    });
});
</script>
@endsection
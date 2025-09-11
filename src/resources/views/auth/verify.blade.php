@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify.css') }}?v={{ time() }}">
@endsection

@section('content')

@if(session('message'))
  <div class="alert-message">
    {{ session('message') }}
  </div>
@endif

<div class="mail-form">
  <p class="mail-form-guide">
    登録していただいたメールアドレスに認証メールを送付しました。
  </p>
  <p class="mail-form-guide">
    メール認証を完了してください。
  </p>

  <form action="{{ route('verification.code.form') }}" method="GET">
    <button class="mail-form-btn" type="submit">認証はこちらから</button>
  </form>

  <form action="{{ route('verification.send') }}" method="POST">
    @csrf
    <button class="mail-resubmit" type="submit">認証メールを再送する</button>
  </form>
</div>
@endsection
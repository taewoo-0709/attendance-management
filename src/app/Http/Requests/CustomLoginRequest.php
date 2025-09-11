<?php

namespace App\Http\Requests;

use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class CustomLoginRequest extends FortifyLoginRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $isAdminRoute = $this->is('admin/*');

        return [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = User::where('email', $this->email)->first();

            if (!$user) {
                return;
            }

            if (!Hash::check($this->password, $user->password)) {
                $validator->errors()->add('password', 'パスワードが正しくありません。');
            }
        });
    }

    public function credentials()
    {
        $credentials = $this->only('email', 'password');

        if ($this->is('admin/*')) {
            $credentials['is_admin'] = 1;
        } else {
            $credentials['is_admin'] = 0;
        }

        return $credentials;
    }

    public function messages()
    {
        return [
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メールアドレスはメール形式で入力してください',
            'email.exists' => 'ログイン情報が登録されていません',
            'password.required' => 'パスワードを入力してください',
            'password.min' => 'パスワードは8文字以上で入力してください',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceTimeRequest extends FormRequest
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
        return [
            'check_in_time'   => 'nullable|date_format:H:i|before:check_out_time',
            'check_out_time'  => 'nullable|date_format:H:i|after:check_in_time',
            'breaks.*.start'  => 'nullable|date_format:H:i',
            'breaks.*.end'    => 'nullable|date_format:H:i',
            'reason'         => 'required|string|max:20',
        ];
    }

    public function messages()
    {
        return [
            'check_in_time.date_format' => '出勤時間はH:i形式で入力してください。',
            'check_in_time.before'      => '出勤時間もしくは退勤時間が不適切な値です。',
            'check_out_time.date_format'=> '退勤時間はH:i形式で入力してください。',
            'check_out_time.after'      => '出勤時間もしくは退勤時間が不適切な値です。',

            'breaks.*.start.date_format'=> '休憩開始時間はH:i形式で入力してください。',
            'breaks.*.end.date_format'  => '休憩終了時間はH:i形式で入力してください。',

            'reason.required' => '備考を記入してください。',
            'reason.string'   => '備考は文字列で入力してください。',
            'reason.max'      => '備考は20文字以内で入力してください。',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $checkIn  = $this->input('check_in_time');
            $checkOut = $this->input('check_out_time');
            $breaks   = $this->input('breaks', []);

            foreach ($breaks as $i => $break) {
                $start = $break['start'] ?? null;
                $end   = $break['end'] ?? null;

                if ($start && $checkIn && $start < $checkIn) {
                    $validator->errors()->add("breaks.$i", '休憩時間が不適切な値です。');
                }
                if ($start && $checkOut && $start > $checkOut) {
                    $validator->errors()->add("breaks.$i", '休憩時間が不適切な値です。');
                }
                if ($end && $checkOut && $end > $checkOut) {
                    $validator->errors()->add("breaks.$i", '休憩時間もしくは退勤時間が不適切な値です。');
                }
                if ($start && $end && $start > $end) {
                    $validator->errors()->add("breaks.$i", '休憩時間が不適切な値です。');
                }
            }
        });
    }
}
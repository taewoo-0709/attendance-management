<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

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
        $today = now()->toDateString();
        $workDate = $this->input('work_date');

        if ($workDate && $workDate < $today) {
            $checkInRule  = ['required', 'before:check_out_time'];
            $checkOutRule = ['required', 'after:check_in_time'];
        } else {
            $checkInRule  = ['nullable'];
            $checkOutRule = ['nullable'];
        }

        return [
            'check_in_time'   => $checkInRule,
            'check_out_time'  => $checkOutRule,
            'breaks.*.start'  => 'nullable',
            'breaks.*.end'    => 'nullable',
            'reason'         => 'required|string|max:20',
        ];
    }

    public function messages()
    {
        return [
            'check_in_time.required' => '出勤時間を入力してください。',
            'check_in_time.before'      => '出勤時間もしくは退勤時間が不適切な値です。',
            'check_out_time.required' => '退勤時間を入力してください。',
            'check_out_time.after'      => '出勤時間もしくは退勤時間が不適切な値です。',
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

            if ($checkIn === '00:00') {
                $validator->errors()->add('check_in_time', '出勤時間もしくは退勤時間が不適切な値です。');
            }
            if ($checkOut === '00:00') {
                $validator->errors()->add('check_out_time', '出勤時間もしくは退勤時間が不適切な値です。');
            }

            $breaks = $this->input('breaks', []);
            $breakTimes = [];
            foreach ($breaks as $i => $break) {
                $start = $break['start'] ?? null;
                $end   = $break['end'] ?? null;

                if ($start && !$end) {
                    $validator->errors()->add("breaks.$i", "休憩終了時間を入力してください。");
                    continue;
                }
                if (!$start && $end) {
                    $validator->errors()->add("breaks.$i", "休憩開始時間を入力してください。");
                    continue;
                }

                if ($start && $end) {
                    $startTime = \Carbon\Carbon::parse($start);
                    $endTime   = \Carbon\Carbon::parse($end);

                    if ($checkIn && $startTime < \Carbon\Carbon::parse($checkIn)) {
                        $validator->errors()->add("breaks.$i", '休憩時間が不適切な値です。');
                    }
                    if ($checkOut && $startTime > \Carbon\Carbon::parse($checkOut)) {
                        $validator->errors()->add("breaks.$i", '休憩時間が不適切な値です。');
                    }
                    if ($checkOut && $endTime > \Carbon\Carbon::parse($checkOut)) {
                        $validator->errors()->add("breaks.$i", '休憩時間もしくは退勤時間が不適切な値です。');
                    }
                    if ($startTime > $endTime) {
                        $validator->errors()->add("breaks.$i", '休憩時間が不適切な値です。');
                    }

                    foreach ($breakTimes as $j => [$existingStart, $existingEnd]) {
                        if ($startTime < $existingEnd && $endTime > $existingStart) {
                            $validator->errors()->add(
                                "breaks.$i",
                                "休憩時間が休憩時間" . ($j + 1) . "と重複しています。"
                            );
                        }
                    }

                    $breakTimes[$i] = [$startTime, $endTime];
                }
            }
        });
    }
}
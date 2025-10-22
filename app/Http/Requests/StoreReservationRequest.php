<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pile_id' => ['required', 'integer', 'min:1'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $data = $this->all();

            if (!isset($data['start_time'], $data['end_time'])) {
                return;
            }

            try {
                // Normalize into Asia/Taipei for rule checks
                $tz = config('app.reservation_tz', 'Asia/Taipei');
                $start = Carbon::parse($data['start_time'])->setTimezone($tz)->second(0);
                $end = Carbon::parse($data['end_time'])->setTimezone($tz)->second(0);
            } catch (\Throwable $e) {
                $v->errors()->add('start_time', 'INVALID_DATETIME');
                return;
            }

            // start < end
            if (!$start->lt($end)) {
                $v->errors()->add('start_time', 'END_BEFORE_START');
                return;
            }

            // Advance requirement: removed 15-minute restriction, only check if not in the past
            $nowTz = now()->setTimezone($start->timezone)->second(0);
            if ($start->lt($nowTz)) {
                $v->errors()->add('start_time', 'START_IN_PAST');
            }

            // Step alignment: removed 15-minute restriction, allow any minute
            // No granularity check needed anymore

            // Duration bounds: 30 to 240 minutes
            $min = (int) config('app.reservation_min_minutes', 30);
            $max = (int) config('app.reservation_max_minutes', 240);
            $durationSigned = $start->diffInMinutes($end, false); // signed
            $duration = abs($durationSigned);
            if ($duration < $min || $duration > $max) {
                $v->errors()->add('end_time', 'DURATION_OUT_OF_RANGE');
            }

            // Bookable window: today to +14 days end of day
            $daysAhead = (int) config('app.reservation_days_ahead', 14);
            $last = $nowTz->copy()->startOfDay()->addDays($daysAhead)->endOfDay();
            if ($start->gt($last) || $end->gt($last)) {
                $v->errors()->add('start_time', 'OUT_OF_BOOKABLE_RANGE');
            }

            // Disallow cross-day by default
            $allowCrossDay = (bool) config('app.reservation_allow_cross_day', false);
            if (!$allowCrossDay && !$start->isSameDay($end)) {
                $v->errors()->add('start_time', 'CROSS_DAY_NOT_ALLOWED');
            }
        });
    }

    protected function failedValidation(ValidatorContract $validator)
    {
        // Provide helpful diagnostics for front-end debugging
        $tz = config('app.reservation_tz', 'Asia/Taipei');
        $startRaw = $this->input('start_time');
        $endRaw = $this->input('end_time');

        $parsed = [];
        try {
            $s = Carbon::parse((string) $startRaw)->setTimezone($tz)->second(0);
            $e = Carbon::parse((string) $endRaw)->setTimezone($tz)->second(0);
            $parsed = [
                'start_tz' => $s->toIso8601String(),
                'end_tz' => $e->toIso8601String(),
                'duration_min' => $e->diffInMinutes($s, false),
            ];
        } catch (\Throwable $e) {
            // ignore
        }

        $errors = $validator->errors();
        $first = $errors->first() ?: 'VALIDATION_FAILED';
        throw new HttpResponseException(response()->json([
            'message' => $first,
            'errors' => $errors->toArray(),
            'meta' => $parsed,
        ], 422));
    }
}



<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'event_id' => 'required|exists:events,id',
            'outcome'  => 'required|string',
            'amount'   => 'required|numeric|min:0.01',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $eventId = $this->input('event_id');
            $outcome = $this->input('outcome');

            if ($eventId && $outcome) {
                $event = Event::find($eventId);

                if (!$event) {
                    return;
                }

                $outcomes = json_decode($event->outcomes, true);
                if (!in_array($outcome, $outcomes, true)) {
                    $validator->errors()->add('outcome', 'Invalid outcome for this event.');
                }
            }
        });
    }
}

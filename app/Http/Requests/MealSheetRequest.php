<?php

namespace App\Http\Requests;

use App\Enums\MealType;
use App\Models\Client;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Confirm the meal sheet for a client and date.
 *
 * meals = { lunch: [employee ids], dinner: [employee ids] } with only the
 * meal types being saved. Client accounts always save for their own
 * organization, whatever client_id is sent.
 */
class MealSheetRequest extends FormRequest
{
    /**
     * How far ahead meals can be planned.
     */
    public const MAX_DAYS_AHEAD = 60;

    private ?Client $resolvedClient = null;

    public function authorize(): bool
    {
        $client = $this->client();

        return $client !== null && (bool) $this->user()?->can('manageMeals', $client);
    }

    protected function prepareForValidation(): void
    {
        if ($this->user()?->isClientAdmin()) {
            $this->merge(['client_id' => $this->user()->client_id]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer'],
            'date' => ['required', 'date_format:Y-m-d', 'before_or_equal:+'.self::MAX_DAYS_AHEAD.' days'],
            'meals' => ['required', 'array', 'min:1'],
            'meals.*' => ['present', 'array'],
            // No "distinct": it would compare across lunch and dinner, and the
            // same employee may take both. Repeats are removed when saving.
            'meals.*.*' => ['integer'],
            'override' => ['boolean'],
        ];
    }

    /**
     * Only meal types the client actually receives can be saved.
     *
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $client = $this->client();

                foreach (array_keys((array) $this->input('meals', [])) as $key) {
                    $type = MealType::tryFrom((string) $key);

                    if (! $type || ! $client?->serves($type)) {
                        $validator->errors()->add('meals', "This client does not receive \"{$key}\" meals.");
                    }
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date.before_or_equal' => 'Meals can be planned up to '.self::MAX_DAYS_AHEAD.' days ahead.',
        ];
    }

    public function client(): ?Client
    {
        return $this->resolvedClient ??= Client::find((int) $this->input('client_id'));
    }

    /**
     * @return array<string, list<int>>
     */
    public function selections(): array
    {
        return collect($this->validated('meals'))
            ->map(fn (array $ids) => array_values(array_map('intval', $ids)))
            ->all();
    }
}

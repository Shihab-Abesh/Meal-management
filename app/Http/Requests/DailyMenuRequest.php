<?php

namespace App\Http\Requests;

use App\Enums\MealType;
use App\Enums\Permission;
use App\Models\DailyMenu;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DailyMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(Permission::ManageMenu->value);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Resource routes name the parameter "daily_menu".
        $menu = $this->route('daily_menu');

        return [
            'menu_date' => ['required', 'date_format:Y-m-d'],
            'meal_type' => [
                'required', Rule::enum(MealType::class),
                Rule::unique('daily_menus', 'meal_type')
                    ->where('menu_date', $this->input('menu_date'))
                    ->ignore($menu instanceof DailyMenu ? $menu->id : null),
            ],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*' => ['integer', 'distinct', Rule::exists('menu_items', 'id')],
            'price' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999.99'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'meal_type.unique' => 'There is already a menu for this meal on this date. Edit that menu instead.',
            'items.required' => 'Choose at least one item.',
            'items.min' => 'Choose at least one item.',
        ];
    }

    /**
     * Whether the user may set the menu price; others keep the current one.
     */
    public function canSetPrice(): bool
    {
        return (bool) $this->user()?->can(Permission::ManagePricing->value);
    }
}

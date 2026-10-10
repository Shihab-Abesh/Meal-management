<?php

namespace App\Http\Requests;

use App\Enums\MenuCategory;
use App\Enums\Permission;
use App\Enums\RecordStatus;
use App\Models\MenuItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(Permission::ManageMenu->value);
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['name' => trim((string) $this->input('name'))]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $item = $this->route('menuItem');

        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('menu_items', 'name')->ignore($item instanceof MenuItem ? $item->id : null),
            ],
            'category' => ['required', Rule::enum(MenuCategory::class)],
            'description' => ['nullable', 'string', 'max:500'],
            'default_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999.99'],
            'status' => ['required', Rule::enum(RecordStatus::class)],
            'image' => [
                'nullable', 'image',
                'mimes:'.implode(',', config('catering.menu_image.mimes')),
                'max:'.config('catering.menu_image.max_kb'),
            ],
            'remove_image' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'A menu item with this name already exists.',
            'default_price.decimal' => 'Use at most two decimal places.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function itemAttributes(): array
    {
        return $this->safe()->except(['image', 'remove_image']);
    }
}

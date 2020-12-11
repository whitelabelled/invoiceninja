<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2020. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://opensource.org/licenses/AAL
 */

namespace App\Http\Requests\Expense;

use App\Http\Requests\Request;
use App\Http\ValidationRules\Expense\UniqueExpenseNumberRule;
use App\Models\Expense;
use App\Utils\Traits\MakesHash;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends Request
{
    use MakesHash;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() : bool
    {
        return auth()->user()->can('create', Expense::class);
    }

    public function rules()
    {
        $rules = [];

        if (isset($this->number)) {
            $rules['number'] = Rule::unique('expenses')->where('company_id', auth()->user()->company()->id);
        }
        
        // $rules['number'] = 'unique:expenses,number,'.$this->id.',id,company_id,'.auth()->user()->company()->id;
        // $rules['contacts.*.email'] = 'nullable|distinct';
        //$rules['number'] = new UniqueExpenseNumberRule($this->all());
        $rules['client_id'] = 'bail|sometimes|exists:clients,id,company_id,'.auth()->user()->company()->id;


        return $this->globalRules($rules);
    }

    protected function prepareForValidation()
    {
        $input = $this->all();

        $input = $this->decodePrimaryKeys($input);

        if (array_key_exists('category_id', $input) && is_string($input['category_id'])) {
            $input['category_id'] = $this->decodePrimaryKey($input['category_id']);
        }

        if (! array_key_exists('currency_id', $input)) {
            $input['currency_id'] = auth()->user()->company()->settings->currency_id;
        }

        $this->replace($input);
    }

    public function messages()
    {
        return [
            'unique' => ctrans('validation.unique', ['attribute' => 'email']),
        ];
    }
}

<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Support\Facades\Validator;
use Lorisleiva\Actions\ActionRequest;
use Illuminate\Support\Arr;

class PayAmountAction
{
    use AsAction;

    protected function pay($amount): bool
    {
        return $amount > 0;
    }

    public function handle(array $attribs): bool
    {
        $validated = Validator::validate($attribs, $this->rules());
        $amount = Arr::get($validated, 'amount');

        return $this->pay($amount);
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:100', 'max:10000'],
        ];
    }

    public function asController(ActionRequest $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validated();
        $this->handle($validated);

        return response()->json(Arr::only($validated, 'amount'));
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'amount'       => (float) $this->amount,
            'type'         => $this->type,
            'mode'         => $this->mode,
            'reference'    => $this->reference,
            'paid_on'      => $this->paid_on?->format('d-m-Y'),
            'collected_by' => $this->collected_by,
            'notes'        => $this->notes,
            'created_at'   => $this->created_at?->format('d-m-Y H:i'),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CategoryResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'amount'      => $this->amount,
            'date'        => $this->date->toDateString(),
            'description' => $this->description,
            'category'    => new CategoryResource($this->whenLoaded('category')),
            'category_id' => $this->category_id
        ];

    }
}

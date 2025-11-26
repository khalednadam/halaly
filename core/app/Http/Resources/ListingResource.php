<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'image' => $this->image,
            'status' => $this->status,
            'is_published' => $this->is_published,
            'is_featured' => $this->is_featured,
            'category_id' => $this->category_id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'vendor_subcategory' => $this->user?->vendor_subcategory,
            'vendor_subcategory_label' => $this->user && $this->user->isVendor() ? $this->user->getVendorSubcategoryLabel() : null,
            'view' => $this->view,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
        ];
    }
}

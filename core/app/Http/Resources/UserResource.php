<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'fullname' => $this->fullname,
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'image' => $this->image,
            'is_vendor' => $this->isVendor(),
            'vendor_subcategory' => $this->vendor_subcategory,
            'vendor_subcategory_label' => $this->isVendor() ? $this->getVendorSubcategoryLabel() : null,
            'location' => userProfileLocation($this),
            'listings_count' => $this->listings()->count(),
            'verified' => $this->is_verified,
            'created_at' => $this->created_at,
        ];
    }
}

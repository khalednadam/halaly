<?php

namespace App\Http\Controllers\Frontend\User;

use App\Http\Controllers\Controller;
use App\Models\Frontend\UserFollow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserFollowController extends Controller
{
    /**
     * Follow or unfollow a vendor
     */
    public function followUnfollow(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => __('Please log in to follow this vendor.')
            ]);
        }

        $currentUser = Auth::guard('web')->user();
        $vendorId = $request->user_id;

        // Check if trying to follow themselves
        if ($currentUser->id === $vendorId) {
            return response()->json([
                'status' => 'error',
                'message' => __('You cannot follow yourself.')
            ]);
        }

        // Get the vendor user
        $vendor = User::find($vendorId);
        
        if (!$vendor) {
            return response()->json([
                'status' => 'error',
                'message' => __('Vendor not found.')
            ]);
        }

        // Check if the user being followed is actually a vendor
        if (!$vendor->isVendor()) {
            return response()->json([
                'status' => 'error',
                'message' => __('You can only follow vendors.')
            ]);
        }

        // Check if current user is a customer
        if (!$currentUser->isCustomer()) {
            return response()->json([
                'status' => 'error',
                'message' => __('Only customers can follow vendors.')
            ]);
        }

        // Check if already following
        $follow = UserFollow::where('follower_id', $currentUser->id)
            ->where('following_id', $vendorId)
            ->first();

        if ($follow) {
            // Unfollow
            $deleted = $follow->delete();
            if ($deleted) {
                return response()->json([
                    'status' => 'unfollow_success',
                    'message' => __('You have unfollowed this vendor.'),
                    'is_following' => false
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => __('Failed to unfollow vendor.')
                ]);
            }
        } else {
            // Follow
            $follow = UserFollow::create([
                'follower_id' => $currentUser->id,
                'following_id' => $vendorId
            ]);

            if ($follow) {
                return response()->json([
                    'status' => 'follow_success',
                    'message' => __('You are now following this vendor.'),
                    'is_following' => true
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => __('Failed to follow vendor.')
                ]);
            }
        }
    }

    /**
     * Check if current user is following a vendor
     */
    public function checkFollowStatus(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'is_following' => false
            ]);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $currentUser = Auth::guard('web')->user();
        $vendorId = $request->user_id;

        $isFollowing = UserFollow::where('follower_id', $currentUser->id)
            ->where('following_id', $vendorId)
            ->exists();

        return response()->json([
            'status' => 'success',
            'is_following' => $isFollowing
        ]);
    }

    /**
     * Show all vendors that the authenticated customer is following
     * Only accessible by customers, and only shows their own followed vendors
     */
    public function followedVendors()
    {
        $currentUser = Auth::guard('web')->user();

        // Ensure only customers can access this page
        if (!$currentUser->isCustomer()) {
            abort(403, __('Access denied. Only customers can view followed vendors.'));
        }

        // Get all vendors the current user is following
        // Using with() to eager load relationships for better performance
        $followedVendors = UserFollow::with(['following.user_country', 'following.user_state', 'following.user_city', 'following.reviews'])
            ->where('follower_id', $currentUser->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('frontend.user.followed-vendors.all-followed', [
            'followedVendors' => $followedVendors
        ]);
    }
}


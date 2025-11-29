<?php

namespace App\Http\Controllers\Frontend\User;

use App\Http\Controllers\Controller;
use App\Mail\BasicMail;
use App\Models\Backend\AdminNotification;
use App\Models\Backend\Category;
use App\Models\Backend\ChildCategory;
use App\Models\Backend\IdentityVerification;
use App\Models\Backend\Listing;
use App\Models\Backend\ListingTag;
use App\Models\Backend\MetaData;
use App\Models\Backend\Page;
use App\Models\Backend\SubCategory;
use App\Models\Common\ListingReport;
use App\Models\Frontend\ListingFavorite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Blog\app\Models\Tag;
use Modules\Brand\app\Models\Brand;
use Modules\CountryManage\app\Models\City;
use Modules\CountryManage\app\Models\Country;
use Modules\CountryManage\app\Models\State;
use Modules\Membership\app\Models\UserMembership;

class ListingController extends Controller
{

    public function allListing(Request $request)
    {
        $listings = Listing::where('user_id', Auth::guard('web')->user()->id)->latest()->paginate(5);
        return view('frontend.user.listings.all-listings', compact('listings'));
    }

    // add listing page
    public function addListing(Request $request)
    {

        // Check Membership Status
        if (moduleExists('Membership') && membershipModuleExistsAndEnable('Membership')) {
            $user_membership_check = UserMembership::where('user_id', Auth::guard('web')->user()->id)->first();
            if ($user_membership_check && $user_membership_check->status === 0 || $user_membership_check->payment_status == 'pending') {
                toastr_error(__('Your membership plan is inactive. Please activate your plan before creating listings.'));
                return redirect()->back();
            }
        }

        if ($request->isMethod('post')) {
            //user Verify check
            if (get_static_option('listing_create_settings') == 'verified_user'){
                $user_identity = IdentityVerification::select('user_id','status')->where('user_id',Auth::guard('web')->user()->id)->first();
                $user_verified_status = $user_identity?->status ?? 0;
                if($user_verified_status != 1 ){
                    toastr_error(__('You are not verified. to add listings you must have to verify your account first'));
                    return redirect()->back();
                }
            }

            //check membership
            if(moduleExists('Membership')){
                if(membershipModuleExistsAndEnable('Membership')){
                    $user_membership = UserMembership::where('user_id', Auth::guard('web')->user()->id)->first();
                    // if user membership is null
                    if(is_null($user_membership)){
                        toastr_error(__('you have to membership a package to create listings'));
                        return redirect()->back();
                    }

                    $user_total_listing_count = Listing::where('user_id', Auth::guard('web')->user()->id)->count();


                    // check user membership all listing limit
                    if ($user_membership->listing_limit == 0 && $user_membership->expire_date <= Carbon::now()){
                        session()->flash('message', __('Your Membership is expired'));
                        return redirect()->back();
                    }elseif ($user_membership->listing_limit === 0){
                        toastr_error(__('Your membership listing limit is over!. please renew it'));
                        return redirect()->back();
                    }elseif ($user_membership->expire_date <= Carbon::now()){
                        toastr_error(__('Your Membership is expired'));
                        return redirect()->back();
                    }

                    // Check if the user has exceeded the allowed number of gallery images
                    $initial_gallery_images = $user_membership->initial_gallery_images;
                    $gallery_images = $request->gallery_images;
                    $gallery_images_input = explode('|', $gallery_images);
                    $gallery_images_input_count = count($gallery_images_input);

                    if ($gallery_images_input_count > $initial_gallery_images) {
                        toastr_error(__('You have exceeded the maximum number of gallery images allowed by your membership package.'));
                        return redirect()->back();
                    }

                    // Check featured listing
                    if (!empty($request->is_featured)){
                        if ($user_membership->initial_featured_listing != 0){
                            if ($user_membership->featured_listing === 0) {
                                toastr_error(__('You have exceeded the maximum number of featured listings allowed by your membership package.'));
                                return redirect()->back();
                            }
                        }
                    }

                }
            }

            // Validation start
            $request->validate([
                'category_id' => 'required',
                'title' => 'required|max:191',
                'description' => 'required|min:150',
                'slug' => 'required|max:255',
                'price' => 'required|numeric'
            ], [
                'title.required' => __('The title field is required.'),
                'title.max' => __('The title must not exceed 191 characters.'),
                'description.required' => __('The description field is required.'),
                'description.min' => __('The description must be at least 150 characters.'),
                'slug.required' => __('The slug field is required.'),
                'price.required' => __('The price field is required.'),
                'price.numeric' => __('The price must be a numeric value.')
            ]);

            $user = User::where('id', Auth::guard('web')->user()->id)->first();

            // Restrict customers to only 'halaly-souq' category
            if ($user->isCustomer()) {
                $halalyCategory = Category::where('slug', 'halaly-souq')->first();

                if (!$halalyCategory) {
                    toastr_error(__('halaly-souq category not found. Please contact administrator.'));
                    return redirect()->back()->withInput();
                }

                if ($request->category_id != $halalyCategory->id) {
                    toastr_error(__('As a customer, you can only add listings under halaly-souq category.'));
                    return redirect()->back()->withInput();
                }
            }
            $listing=Listing::all();
            $present=false;
            foreach($listing as $list)
            {
                if($request->slug == $list->slug)
                {
                    $present=true;
                    break;
                }
            }
            $slug=$request->slug;

            if($present==true)
            {
                $slug = $request->slug . uniqid() . random_int(1000, 9999);


            }
            $slug = !empty($slug) ? $slug : $request->title;

            if(get_static_option('listing_create_status_settings') == 'approved'){
                $status = 1;
            }else{
                $status = 0;
            }

            // video url
            $video_url = null;
            if(!empty($request->video_url)){
                $video_url = getYoutubeEmbedUrl($request->video_url);
            }

            // listing phone number
            $listing_phone = $request->country_code ?? $request->phone;

            // Create a new listing
            $listing = new Listing();
            $listing->user_id = $user->id;
            $listing->category_id = $request->category_id;
            $listing->sub_category_id = $request->sub_category_id;
            $listing->child_category_id = $request->child_category_id;
            $listing->country_id = $request->country_id;
            $listing->state_id = $request->state_id;
            $listing->city_id = $request->city_id;
            $listing->brand_id = $request->brand_id;
            $listing->title = $request->title;
            $listing->slug = Str::slug(purify_html($slug),'-',null);
            $listing->description = $request->description;
            $listing->price = $request->price;
            $listing->negotiable = $request->negotiable ?? 0;
            $listing->condition = $request->condition;
            $listing->authenticity = $request->authenticity;
            $listing->phone = $listing_phone;
            $listing->phone_hidden = $request->phone_hidden ?? 0;
            $listing->image = $request->image;
            $listing->gallery_images = $request->gallery_images;
            $listing->video_url = $video_url;
            $listing->address = $request->address;
            $listing->lat = $request->latitude;
            $listing->lon = $request->longitude;
            $listing->is_featured =  $request->is_featured ?? 0;
            $listing->status = $status;


            $tags_name = '';
            if (!empty($request->tags)) {
                $tags_name = Tag::whereIn('id', $request->tags)->pluck('name')->implode(', ');
            }
            $Metas = [
                'meta_title'=> purify_html($request->meta_title),
                'meta_tags'=> purify_html($request->meta_tags),
                'meta_description'=> purify_html($request->meta_description),

                'facebook_meta_tags'=> purify_html($request->facebook_meta_tags),
                'facebook_meta_description'=> purify_html($request->facebook_meta_description),
                'facebook_meta_image'=> $request->facebook_meta_image,

                'twitter_meta_tags'=> purify_html($request->twitter_meta_tags),
                'twitter_meta_description'=> purify_html($request->twitter_meta_description),
                'twitter_meta_image'=> $request->twitter_meta_image,
            ];
            $listing->save();
            $listing->metaData()->create($Metas);
            // Retrieve the last inserted ID
            $last_listing_id = $listing->id;

            // create tags
            if ($request->filled('tags')) {
                foreach ($request->tags as $tagId) {
                    ListingTag::create([
                        'listing_id' => $last_listing_id,
                        'tag_id' => $tagId,
                    ]);
                }
            }

            $user_id = Auth::guard('web')->user()->id;

            // if membership system decrement listing limit
            if(moduleExists('Membership')){
                if (membershipModuleExistsAndEnable('Membership')) {
                    // listing limit
                     UserMembership::where('user_id', $user_id)->update([
                        'listing_limit' => DB::raw(sprintf("listing_limit - %s", (int)strip_tags(1))),
                    ]);

                    // is featured listing
                    $user_membership_check = UserMembership::where('user_id', $user_id)->first();
                    if ($user_membership_check->initial_featured_listing != 0){
                        if (!empty($request->is_featured)){
                            UserMembership::where('user_id', $user_id)->update([
                                'featured_listing' => DB::raw(sprintf("featured_listing - %s", (int)strip_tags(1))),
                            ]);
                        }
                    }
                }
            }

            //create listing notification to admin
            AdminNotification::create([
                'identity'=> $last_listing_id,
                'user_id'=> $user_id,
                'type'=>'Create Listing',
                'message'=>__('A new project has been created'),
            ]);

            // sent email to admin
            if (get_static_option('listing_create_status_settings') == 'pending'){
                try {
                    $subject = get_static_option('listing_approve_subject') ?? __('New Listing Approve Request');
                    $message = get_static_option('listing_approve_message');
                    $message = str_replace(["@listing_id"], [$last_listing_id], $message);
                    Mail::to(get_static_option('site_global_email'))->send(new BasicMail([
                        'subject' => $subject,
                        'message' => $message
                    ]));
                } catch (\Exception $e) {
                    //
                }
            }

            return redirect()->route('user.all.listing')->with(toastr_success(__('Listing Added Success')));
        }


        //check membership
        if(moduleExists('Membership')){
            if(membershipModuleExistsAndEnable('Membership')){
                $user_membership = UserMembership::where('user_id', Auth::guard('web')->user()->id)->first();
                if(is_null($user_membership)){
                    toastr_error(__('you have to membership a package to create listings'));
                    return redirect()->back();
                }
            }
        }

        // Filter categories for customers - only show 'halaly-souq'
        $user = Auth::guard('web')->user();
        $halalyCategory = null;
        if ($user->isCustomer()) {
            $halalyCategory = Category::where('slug', 'halaly-souq')->where('status', 1)->first();
            $categories = $halalyCategory ? collect([$halalyCategory]) : collect([]);
        } else {
            $categories = Category::where('status', 1)->get();
        }

        $sub_categories = SubCategory::where('status', 1);
        $all_countries = Country::all_countries();
        $all_states = State::all_states();
        $all_cities = City::all_cities();
        $tags = Tag::where('status', 'publish')->get();
        $user = Auth::guard('web')->user();
        $brands = Brand::where('status', 1)->get();
        $user_identity_verifications = IdentityVerification::where('user_id', $user->id)->first();

        // if membership module exits
        $membership_page_url = get_static_option('membership_plan_page') ? Page::select('slug')->find(get_static_option('membership_plan_page'))->slug : '';
        $user_featured_listing_enable = false;
        $user_listing_limit_check = false;
        if(moduleExists('Membership')){
            if(membershipModuleExistsAndEnable('Membership')){
                $user_membership = UserMembership::where('user_id', $user->id)->first();
                if ($user_membership->featured_listing != 0){
                    $user_featured_listing_enable = true;
                }
                if ($user_membership->listing_limit === 0){
                    $user_listing_limit_check = true;
                }
            }
        }

        return view('frontend.user.listings.add-listing', compact(
            'membership_page_url',
            'user_featured_listing_enable',
            'user_listing_limit_check',
            'user',
            'brands',
            'categories',
            'halalyCategory',
            'sub_categories',
            'all_countries',
            'all_states',
            'all_cities',
            'tags',
            'user_identity_verifications'
        ));

    }

    // Edit listing page
    public function editListing(Request $request, $id)
    {
        if ($request->isMethod('post')) {

            // Validation start
            $request->validate([
                'category_id' => 'required',
                'title' => 'required|max:191',
                'description' => 'required|min:150',
                'slug' => 'required',
                'price' => 'required|numeric'
            ], [
                'title.required' => __('The title field is required.'),
                'title.max' => __('The title must not exceed 191 characters.'),
                'description.required' => __('The description field is required.'),
                'description.min' => __('The description must be at least 150 characters.'),
                'slug.required' => __('The slug field is required.'),
                'price.required' => __('The price field is required.'),
                'price.numeric' => __('The price must be a numeric value.')
            ]);

            // country, state, city
            $user = User::where('id', Auth::guard('web')->user()->id)->first();

            // Restrict customers to only 'halaly-souq' category
            if ($user->isCustomer()) {
                $halalyCategory = Category::where('slug', 'halaly-souq')->first();

                if (!$halalyCategory) {
                    toastr_error(__('halaly-souq category not found. Please contact administrator.'));
                    return redirect()->back()->withInput();
                }

                if ($request->category_id != $halalyCategory->id) {
                    toastr_error(__('As a customer, you can only add listings under halaly-souq category.'));
                    return redirect()->back()->withInput();
                }
            }
            $listing=Listing::whereNot("id",$id)->get();
            $present=false;
            foreach($listing as $list)
            {
                if($request->slug == $list->slug)
                {

                    $present=true;
                    break;
                }
            }
            $slug=$request->slug;

            if($present==true)
            {
                $slug = $request->slug . uniqid() . random_int(1000, 9999);


            }
            $slug = !empty($slug) ? $slug : $request->title;

            if(get_static_option('listing_create_status_settings') == 'approved'){
                $status = 1;
            }else{
                $status = 0;
            }

            // video url
            $video_url = null;
            if(!empty($request->video_url)){
                $video_url = getYoutubeEmbedUrl($request->video_url);
            }

            // listing phone number
            $listing_phone = $request->country_code ?? $request->phone;

            // Edit listing
            $listing = Listing::findOrFail($id);
            $listing->user_id = $user->id;
            $listing->category_id = $request->category_id;
            $listing->sub_category_id = $request->sub_category_id;
            $listing->child_category_id = $request->child_category_id;
            $listing->country_id = $request->country_id;
            $listing->state_id = $request->state_id;
            $listing->city_id = $request->city_id;
            $listing->brand_id = $request->brand_id;
            $listing->title = $request->title;
            $listing->slug = Str::slug(purify_html($slug),'-',null);
            $listing->description = $request->description;
            $listing->price = $request->price;
            $listing->negotiable = $request->negotiable ?? 0;
            $listing->condition = $request->condition;
            $listing->authenticity = $request->authenticity;
            $listing->phone = $listing_phone;
            $listing->phone_hidden = $request->phone_hidden ?? 0;
            $listing->image = $request->image;
            $listing->gallery_images = $request->gallery_images;
            $listing->video_url = $video_url;
            $listing->address = $request->address;
            $listing->lat = $request->latitude;
            $listing->lon = $request->longitude;
            $listing->is_featured = $request->is_featured ?? 0;
            $listing->status = $status;


            $tags_name = '';
            if (!empty($request->tags)) {
                $tags_name = Tag::whereIn('id', $request->tags)->pluck('name')->implode(', ');
            }
           $Metas = [
                'meta_title'=> purify_html($request->meta_title),
                'meta_tags'=> purify_html($request->meta_tags),
                'meta_description'=> purify_html($request->meta_description),

                'facebook_meta_tags'=> purify_html($request->facebook_meta_tags ),
                'facebook_meta_description'=> purify_html($request->facebook_meta_description),
                'facebook_meta_image'=> $request->facebook_meta_image,

                'twitter_meta_tags'=> purify_html($request->twitter_meta_tags),
                'twitter_meta_description'=> purify_html($request->twitter_meta_description),
                'twitter_meta_image'=> $request->twitter_meta_image,
            ];
            $listing->save();
            $metaData=MetaData::where("meta_taggable_id",$listing->id)->first();
            if($metaData)
            {
                $listing->metaData()->update($Metas);
            }
            else
            {
                $listing->metaData()->create($Metas);
            }

            // Retrieve the last inserted ID
            $last_listing_id = $listing->id;

            // Edit tags
            if ($request->filled('tags')) {
                $listing->tags()->detach();
                foreach ($request->tags as $tagId) {
                    ListingTag::create([
                        'listing_id' => $last_listing_id,
                        'tag_id' => $tagId,
                    ]);
                }
            }

            // send email to admin
            try {
                $message = get_static_option('service_approve_message');
                $message = str_replace(["@service_id"], [$last_listing_id], $message);
                Mail::to(get_static_option('site_global_email'))->send(new BasicMail([
                    'subject' => get_static_option('service_approve_subject') ?? __('New Listing Approve Request'),
                    'message' => $message
                ]));
            } catch (\Exception $e) {
                //
            }

            return back()->with(toastr_success(__('Listing Updated Success')));
        }


        $listing = Listing::findOrFail($id);

        // Filter categories for customers - only show 'halaly-souq'
        $user = Auth::guard('web')->user();
        $halalyCategory = null;
        if ($user->isCustomer()) {
            $halalyCategory = Category::where('slug', 'halaly-souq')->where('status', 1)->first();
            $categories = $halalyCategory ? collect([$halalyCategory]) : collect([]);
        } else {
            $categories = Category::where('status', 1)->get();
        }

        $sub_categories = SubCategory::where('status', 1)->get();
        $child_categories = ChildCategory::where('status', 1)->get();
        $all_countries = Country::all_countries();
        $all_states = State::all_states();
        $all_cities = City::all_cities();
        $brands = Brand::where('status', 1)->get();
        $tags = Tag::where('status', 'publish')->get();

        // if membership module exits
        $membership_page_url = get_static_option('membership_plan_page') ? Page::select('slug')->find(get_static_option('membership_plan_page'))->slug : '';
        $user_featured_listing_enable = false;
        $user_listing_limit_check = false;
        if(moduleExists('Membership')){
            if(membershipModuleExistsAndEnable('Membership')){
                $user_membership = UserMembership::where('user_id', Auth::guard('web')->user()->id)->first();
               if (!empty($user_membership)){
                   if ($user_membership->featured_listing != 0){
                       $user_featured_listing_enable = true;
                   }
                   if ($user_membership->listing_limit === 0){
                       $user_listing_limit_check = true;
                   }
               }

            }
        }

        return view('frontend.user.listings.edit-listing', compact(
            'membership_page_url',
            'user_featured_listing_enable',
            'user_listing_limit_check',
            'listing',
            'brands',
            'categories',
            'halalyCategory',
            'sub_categories',
            'child_categories',
            'all_countries',
            'all_states',
            'all_cities',
            'tags',
            'user'
        ));
    }

    public function deleteListing($id = null)
    {
        if (Listing::find($id)) {
            ListingTag::where('listing_id', $id)->delete();
            ListingFavorite::where('listing_id', $id)->delete();
            ListingReport::where('listing_id', $id)->delete();

            // Delete the main Listing record
            Listing::find($id)->delete();

            toastr_error(__('Listing Delete Success---'));
            return redirect()->back();
        } else {
            toastr_error(__('Listing not found'));
            return redirect()->back();
        }
    }

    public function listingPublishedStatus($id)
    {
        // First check if the listing exists
        $listing = Listing::find($id);
        if (!$listing) {
            $message = __('Listing not found.');
            toastr()->error($message);
            return redirect()->back();
        }

        // Check listing approval status
        if ($listing->status === 0) {
            $message = __('This listing is not yet approved. It will be published after approval.');
            toastr()->warning($message);
            return redirect()->back();
        }

        // Toggle listing publication status
        $listing->is_published = !$listing->is_published;
        $listing->save();

        // Show appropriate message
        if ($listing->is_published) {
            // Listing is published
            $message = __('Listing has been successfully published.');
            toastr()->success($message);
        } else {
            // Listing is unpublished
            $message = __('Listing has been successfully unpublished.');
            toastr()->warning($message);
        }

        return redirect()->back();
    }


}

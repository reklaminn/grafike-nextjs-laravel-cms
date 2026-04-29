<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\Site;
use App\Models\SiteSetting;

class SettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $site   = Site::resolve(request()->header('X-Site-Host'));
        $siteId = $site?->id;

        return response()->json([
            'settings' => [
                'site_title'  => SiteSetting::get('site.title',   config('cms.name', 'Grafike CMS'), $siteId),
                'logo_url'    => SiteSetting::get('design.logo_url',    '', $siteId),
                'favicon_url' => SiteSetting::get('design.favicon_url', '', $siteId),
                'footer_text' => SiteSetting::get('site.footer_text',   '', $siteId),

                'contact' => [
                    'phone'   => SiteSetting::get('contact.phone',   '', $siteId),
                    'email'   => SiteSetting::get('contact.email',   '', $siteId),
                    'address' => SiteSetting::get('contact.address', '', $siteId),
                ],

                'social' => [
                    'facebook'  => SiteSetting::get('social.facebook',  '', $siteId),
                    'instagram' => SiteSetting::get('social.instagram', '', $siteId),
                    'twitter'   => SiteSetting::get('social.twitter',   '', $siteId),
                    'youtube'   => SiteSetting::get('social.youtube',   '', $siteId),
                    'linkedin'  => SiteSetting::get('social.linkedin',  '', $siteId),
                ],

                'services' => [
                    'google_analytics_id'    => SiteSetting::get('services.google_analytics_id',    '', $siteId),
                    'google_tag_manager_id'  => SiteSetting::get('services.google_tag_manager_id',  '', $siteId),
                    'recaptcha_site_key'     => SiteSetting::get('services.recaptcha_site_key',     '', $siteId),
                    'google_site_verification' => SiteSetting::get('services.google_site_verification', '', $siteId),
                    'bing_site_verification'   => SiteSetting::get('services.bing_site_verification',   '', $siteId),
                    'indexnow_key'             => SiteSetting::get('services.indexnow_key',             '', $siteId),
                ],

                // Business / LocalBusiness structured data
                'business' => [
                    'name'                => SiteSetting::get('business.name',                 '', $siteId),
                    'type'                => SiteSetting::get('business.type',                 'Organization', $siteId),
                    'address_street'      => SiteSetting::get('business.address_street',       '', $siteId),
                    'address_city'        => SiteSetting::get('business.address_city',         '', $siteId),
                    'address_postal_code' => SiteSetting::get('business.address_postal_code',  '', $siteId),
                    'address_country'     => SiteSetting::get('business.address_country',      'TR', $siteId),
                    'telephone'           => SiteSetting::get('business.telephone',            '', $siteId),
                    'email'               => SiteSetting::get('business.email',                '', $siteId),
                    'geo_lat'             => SiteSetting::get('business.geo_lat',              '', $siteId),
                    'geo_lng'             => SiteSetting::get('business.geo_lng',              '', $siteId),
                    'opening_hours'       => SiteSetting::get('business.opening_hours',        '', $siteId),
                    'organization_json_ld'=> SiteSetting::get('business.organization_json_ld', '', $siteId),
                ],
            ],
        ]);
    }
}

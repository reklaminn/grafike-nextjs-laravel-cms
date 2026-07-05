<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function index(): JsonResponse
    {
        // Tenant context is established by stancl middleware.
        // SiteSetting queries run on the current tenant's DB — no site_id filter needed.
        // stancl's CacheTenancyBootstrapper automatically prefixes cache keys per tenant.

        return response()->json([
            'settings' => [
                'site_title'  => SiteSetting::get('site.title',   config('cms.name', 'Grafike CMS')),
                // Admin marka formu bu anahtarları ÖN-EKSİZ yazar (settings[logo_url]/[favicon_url]);
                // eski okuyucular design.* bekliyordu → ön-eksiz önce, design.* geriye-dönük fallback.
                'logo_url'    => SiteSetting::get('logo_url',    '') ?: SiteSetting::get('design.logo_url',    ''),
                'favicon_url' => SiteSetting::get('favicon_url', '') ?: SiteSetting::get('design.favicon_url', ''),
                'footer_text' => SiteSetting::get('site.footer_text',   ''),

                'contact' => [
                    'phone'   => SiteSetting::get('contact.phone',   ''),
                    'email'   => SiteSetting::get('contact.email',   ''),
                    'address' => SiteSetting::get('contact.address', ''),
                ],

                'social' => [
                    'facebook'  => SiteSetting::get('social.facebook',  ''),
                    'instagram' => SiteSetting::get('social.instagram', ''),
                    'twitter'   => SiteSetting::get('social.twitter',   ''),
                    'youtube'   => SiteSetting::get('social.youtube',   ''),
                    'linkedin'  => SiteSetting::get('social.linkedin',  ''),
                ],

                'services' => [
                    'google_analytics_id'      => SiteSetting::get('services.google_analytics_id',      ''),
                    'google_tag_manager_id'    => SiteSetting::get('services.google_tag_manager_id',    ''),
                    'recaptcha_site_key'       => SiteSetting::get('services.recaptcha_site_key',       ''),
                    'google_site_verification' => SiteSetting::get('services.google_site_verification', ''),
                    'bing_site_verification'   => SiteSetting::get('services.bing_site_verification',   ''),
                    'indexnow_key'             => SiteSetting::get('services.indexnow_key',             ''),
                ],

                // Business / LocalBusiness structured data
                'business' => [
                    'name'                => SiteSetting::get('business.name',                 ''),
                    'type'                => SiteSetting::get('business.type',                 'Organization'),
                    'address_street'      => SiteSetting::get('business.address_street',       ''),
                    'address_city'        => SiteSetting::get('business.address_city',         ''),
                    'address_postal_code' => SiteSetting::get('business.address_postal_code',  ''),
                    'address_country'     => SiteSetting::get('business.address_country',      'TR'),
                    'telephone'           => SiteSetting::get('business.telephone',            ''),
                    'email'               => SiteSetting::get('business.email',                ''),
                    'geo_lat'             => SiteSetting::get('business.geo_lat',              ''),
                    'geo_lng'             => SiteSetting::get('business.geo_lng',              ''),
                    'opening_hours'       => SiteSetting::get('business.opening_hours',        ''),
                    'organization_json_ld'=> SiteSetting::get('business.organization_json_ld', ''),
                ],
            ],
        ]);
    }
}

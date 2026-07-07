<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\SectionTemplate;
use App\Models\Theme;
use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Boblanlı Yapı — TENANT seeder (OtelVatan/Homeland deseni). TENANT CONTEXT'inde çalıştır.
 * ÖN KOŞUL: tenant 'boblanliyapi' + tenants:migrate +
 *   central: BoblanliThemeSeeder + BoblanliChromeSeeder + BoblanliFieldChromeSeeder.
 * Tek-sayfa kurumsal inşaat sitesi: tüm bölümler tek 'home' sayfasında (anchor-nav).
 * İletişim formu = tenant'a özel "boblanli-teklif" formu (FormSection bloğuyla render).
 */
class BoblanliTenantSeeder extends Seeder
{
    private const TENANT_ID = 'boblanliyapi';
    private const WA = '905326576271';

    private ?Theme $theme = null;

    private function fieldBlk(string $id, string $variation, int $sort): array
    {
        $tpl = SectionTemplate::where('theme_id',$this->theme?->id)->where('variation',$variation)->first();
        if (! $tpl) { $this->command?->warn("Alan şablonu yok: {$variation}"); }
        return ['id'=>$id,'type'=>'content-block','variation'=>$variation,'render_mode'=>'html',
            'section_template_id'=>$tpl?->id,'is_active'=>true,'sort_order'=>$sort,
            'content'=>($tpl?->default_content_json ?? []),'html_override'=>null];
    }
    private function formBlk(string $id, array $content, int $sort): array
    {
        return ['id'=>$id,'type'=>'form','variation'=>'','render_mode'=>'component',
            'section_template_id'=>null,'is_active'=>true,'sort_order'=>$sort,'content'=>$content,'html_override'=>null];
    }
    private function chromeBlk(string $id, string $type, string $variation, ?SectionTemplate $tpl, string $html): array
    {
        return ['id'=>$id,'type'=>$type,'variation'=>$variation,'render_mode'=>'html',
            'section_template_id'=>$tpl?->id,'is_active'=>true,'sort_order'=>1,'content'=>[],'html_override'=>$html];
    }
    private function regRow(string $r, array $blocks): array
    {
        return ['id'=>'row_'.$r.'_1','type'=>'row','is_active'=>true,
            'columns'=>[['id'=>'col_'.$r.'_1','width'=>12,'is_active'=>true,'blocks'=>$blocks]]];
    }

    /** Tenant'a özel "Teklif İsteyin" formu (tasarım alanları). İdempotent. */
    private function ensureTeklifForm(?int $langId): ?int
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('forms')) {
            $this->command?->warn('forms tablosu yok — tenants:migrate eksik.'); return null;
        }
        $now = now();
        $formId = DB::table('forms')->where('slug','boblanli-teklif')->value('id');
        $attrs = [
            'name'               => 'Teklif İsteyin',
            'description'        => 'Kuşadası ve Aydın için inşaat, tadilat ve elektrik hizmetlerinde ücretsiz keşif ve teklif.',
            'is_active'          => true,
            'requires_captcha'   => false,
            'notification_email' => 'info@boblanliyapi.com',
            'allow_submissions'  => true,
            'save_to_database'   => true,
            'language_id'        => $langId,
            'updated_at'         => $now,
        ];
        if ($formId) {
            DB::table('forms')->where('id',$formId)->update($attrs);
        } else {
            $formId = DB::table('forms')->insertGetId($attrs + ['slug'=>'boblanli-teklif','created_at'=>$now]);
        }
        // Alanları yeniden senkronize et
        DB::table('form_fields')->where('form_id',$formId)->delete();
        $fields = [
            ['label'=>'Ad Soyad','name'=>'ad_soyad','type'=>'text','is_required'=>true,'sort_order'=>1,'options'=>null],
            ['label'=>'Telefon','name'=>'telefon','type'=>'phone','is_required'=>true,'sort_order'=>2,'options'=>null],
            ['label'=>'Hizmet Seçimi','name'=>'hizmet','type'=>'select','is_required'=>false,'sort_order'=>3,
             'options'=>json_encode([
                ['label'=>'Elektrik Arıza ve Onarım','value'=>'Elektrik Arıza ve Onarım'],
                ['label'=>'Toptan Elektrik & Malzeme Satışı','value'=>'Toptan Elektrik & Malzeme Satışı'],
                ['label'=>'Dekorasyon ve Revizyon','value'=>'Dekorasyon ve Revizyon'],
                ['label'=>'İnşaat','value'=>'İnşaat'],
                ['label'=>'Diğer','value'=>'Diğer'],
             ], JSON_UNESCAPED_UNICODE)],
            ['label'=>'Mesajınız','name'=>'mesaj','type'=>'textarea','is_required'=>true,'sort_order'=>4,'options'=>null],
        ];
        foreach ($fields as $f) {
            DB::table('form_fields')->insert($f + ['form_id'=>$formId,'created_at'=>$now,'updated_at'=>$now]);
        }
        return (int) $formId;
    }

    public function run(): void
    {
        $theme = Theme::where('slug','boblanli')->first();
        if (! $theme) { $this->command?->warn('boblanli teması yok — önce BoblanliThemeSeeder.'); return; }
        $this->theme = $theme;
        $byvar = fn($v)=>SectionTemplate::where('theme_id',$theme->id)->where('variation',$v)->first();
        $cbTpl  = $byvar('free-html');
        $hdrTpl = $byvar('boblanli-header'); $ftrTpl = $byvar('boblanli-footer');
        if (! $cbTpl || ! $hdrTpl || ! $ftrTpl) { $this->command?->warn('Chrome yok — önce BoblanliChromeSeeder.'); return; }

        $tnt = \App\Models\Tenant::find(self::TENANT_ID);
        if ($tnt) { $tnt->theme_id = $theme->id; $tnt->save(); }
        $lang = Language::query()->where('code','tr')->first() ?? Language::query()->first();
        $langId = $lang?->id;

        $teklifFormId = $this->ensureTeklifForm($langId);

        $hdr = BoblanliChromeSeeder::headerHtml();
        $ftr = BoblanliChromeSeeder::footerHtml();

        // Tek sayfa: tüm bölümler sırayla + en sonda teklif formu.
        $bodyBlocks = [
            $this->fieldBlk('b_hero','bl-01-hero',1),
            $this->fieldBlk('b_guven','bl-02-guven',2),
            $this->fieldBlk('b_hizmet','bl-03-hizmetler',3),
            $this->fieldBlk('b_neden','bl-04-neden',4),
            $this->fieldBlk('b_surec','bl-05-surec',5),
            $this->fieldBlk('b_galeri','bl-06-galeri',6),
            $this->fieldBlk('b_iletisim','bl-07-iletisim',7),
        ];
        if ($teklifFormId) {
            $bodyBlocks[] = $this->formBlk('b_form',[
                'form_id'=>$teklifFormId,'title'=>'Teklif İsteyin',
                'description'=>'Bilgilerinizi bırakın, en kısa sürede ücretsiz keşif için sizi arayalım.',
                'submit_label'=>'Teklif İsteyin',
            ],8);
        }

        $pages = [ ['slug'=>'home','title'=>'Ana Sayfa','order'=>1,'menu'=>true] ];

        // Trashed çakışması + tenant açılışından kalan NULL-dil scaffold sayfalarını temizle.
        Page::onlyTrashed()->whereIn('slug', array_column($pages,'slug'))->forceDelete();
        Page::whereNull('language_id')->whereNotIn('slug',['404','500'])->forceDelete();

        foreach ($pages as $p) {
            Page::updateOrCreate(
                ['slug'=>$p['slug'],'language_id'=>$langId],
                ['title'=>$p['title'],'status'=>'published','show_in_menu'=>$p['menu'],
                 'sort_order'=>$p['order'],'show_breadcrumb'=>false,
                 'sections_json'=>['version'=>2,'regions'=>[
                    'header'=>[$this->regRow('header',[$this->chromeBlk('b_header','header','boblanli-header',$hdrTpl,$hdr)])],
                    'body'  =>[$this->regRow('body',$bodyBlocks)],
                    'footer'=>[$this->regRow('footer',[$this->chromeBlk('b_footer','footer','boblanli-footer',$ftrTpl,$ftr)])],
                 ]]]
            );
        }

        foreach ([
            ['site.title','Boblanlı Yapı','general'],
            ['site.footer_text','© 2026 Boblanlı Yapı — Tüm hakları saklıdır.','general'],
            ['contact.phone','+90 532 657 62 71','contact'],
            ['contact.email','info@boblanliyapi.com','contact'],
            ['contact.address','Türkmen Mahallesi, Bahçearası Sok. No:2 İç Kapı:3, Kuşadası / Aydın','contact'],
            ['social.whatsapp',self::WA,'social'],
            ['social.instagram','https://instagram.com/tahsinboblanli','social'],
        ] as $st) { SiteSetting::updateOrCreate(['key'=>$st[0]],['value'=>$st[1],'group'=>$st[2],'type'=>'text']); }

        $this->command?->info('BoblanliTenantSeeder: tek sayfa (7 bölüm) + teklif formu'
            .($teklifFormId ? '' : ' (UYARI: form kurulamadı)').' + ayarlar kuruldu.');
    }
}

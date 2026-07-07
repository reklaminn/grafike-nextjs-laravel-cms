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
 * Çok-sayfa kurumsal inşaat sitesi: menü öğeleri ayrı sayfalar (home + hizmetler +
 * neden-biz + calismalar + iletisim). Ana Sayfa zengin landing; alt sayfalar başlık banner + bölüm.
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
    /** Aynı fieldBlk ama content'i şablon default'una MERGE'ler (sayfa-özel başlık vb. için). */
    private function fieldBlkC(string $id, string $variation, array $content, int $sort): array
    {
        $tpl = SectionTemplate::where('theme_id',$this->theme?->id)->where('variation',$variation)->first();
        if (! $tpl) { $this->command?->warn("Alan şablonu yok: {$variation}"); }
        return ['id'=>$id,'type'=>'content-block','variation'=>$variation,'render_mode'=>'html',
            'section_template_id'=>$tpl?->id,'is_active'=>true,'sort_order'=>$sort,
            'content'=>array_merge($tpl?->default_content_json ?? [], $content),'html_override'=>null];
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

        // Çok-sayfa: her menü öğesi kendi sayfası. Ana Sayfa zengin landing; alt sayfalar
        // başlık banner (bl-page-hero) + kendi bölümü. Bölümler sayfalar arasında paylaşılır
        // (her blok şablon default'unun KOPYASINI taşır → admin'de bağımsız düzenlenebilir).
        $formBlk = $teklifFormId
            ? [$this->formBlk('b_form',[
                'form_id'=>$teklifFormId,'title'=>'Teklif İsteyin',
                'description'=>'Bilgilerinizi bırakın, en kısa sürede ücretsiz keşif için sizi arayalım.',
                'submit_label'=>'Teklif İsteyin',
              ],9)]
            : [];

        // Hizmet detay sayfası gövdesi (page-hero + SEO metin + CTA + form).
        $svcDetail = fn(string $eyebrow, string $title, string $crumb, string $sub, string $body) => array_merge([
            $this->fieldBlkC('b_ph','bl-page-hero',['eyebrow'=>$eyebrow,'title'=>$title,'crumb'=>$crumb,'subtitle'=>$sub],1),
            $this->fieldBlkC('b_body','bl-rich-text',['eyebrow'=>'HİZMET KAPSAMI','title'=>$title,'body_html'=>$body,'bg'=>'#ffffff'],2),
            $this->fieldBlkC('b_cta','bl-cta',['title'=>'Bu Hizmet İçin Ücretsiz Keşif','cta_label'=>'Hemen Arayın','cta_url'=>'tel:+905326576271'],3),
        ], $formBlk);

        $bodyByPage = [
            // Ana Sayfa — zengin landing
            'home' => [
                $this->fieldBlk('b_hero','bl-01-hero',1),
                $this->fieldBlk('b_guven','bl-02-guven',2),
                $this->fieldBlk('b_hizmet','bl-03-hizmetler',3),
                $this->fieldBlk('b_neden','bl-04-neden',4),
                $this->fieldBlk('b_galeri','bl-06-galeri',5),
                $this->fieldBlkC('b_cta','bl-cta',[
                    'title'=>'Projeniz İçin Ücretsiz Keşif','cta_label'=>'İletişime Geçin','cta_url'=>'/iletisim',
                ],6),
            ],
            // Hizmetler — SEO giriş + tıklanabilir kartlar + süreç
            'hizmetler' => [
                $this->fieldBlkC('b_ph','bl-page-hero',[
                    'eyebrow'=>'HİZMETLERİMİZ','title'=>'Sunduğumuz Hizmetler','crumb'=>'Hizmetler',
                    'subtitle'=>'Kuşadası ve Aydın genelinde elektrik, tadilat, dekorasyon ve inşaat — tek elden, uçtan uca çözüm.',
                ],1),
                $this->fieldBlkC('b_intro','bl-rich-text',[
                    'eyebrow'=>'TEK ELDEN ÇÖZÜM','title'=>'Kuşadası ve Aydın\'da Yapı Hizmetleri','bg'=>'#ffffff',
                    'body_html'=>'<p>Boblanlı Yapı; <strong>Kuşadası ve Aydın genelinde</strong> inşaat, iç dekorasyon, kapsamlı revizyon, elektrik arıza-onarım ve toptan elektrik malzemesi hizmetlerini tek çatı altında sunar. Küçük bir elektrik arızasından anahtar teslim inşaata kadar her ölçekte işi güvenilir işçilik ve şeffaf fiyatlandırma ile tamamlıyoruz.</p><p>Aşağıdaki hizmet alanlarından birine tıklayarak detayları inceleyebilir, ücretsiz keşif için bizimle iletişime geçebilirsiniz.</p>',
                ],2),
                $this->fieldBlk('b_hizmet','bl-03-hizmetler',3),
                $this->fieldBlk('b_surec','bl-05-surec',4),
            ],
            // Neden Biz — sayaçlar + avantajlar + SEO metin
            'neden-biz' => [
                $this->fieldBlkC('b_ph','bl-page-hero',[
                    'eyebrow'=>'NEDEN BİZ','title'=>'Neden Kuşadası\'nda Boblanlı Yapı?','crumb'=>'Neden Biz',
                    'subtitle'=>'Deneyimli ekip, zamanında teslim, şeffaf fiyat ve garantili işçilik — güveninizin karşılığı.',
                ],1),
                $this->fieldBlk('b_neden','bl-04-neden',2),
                $this->fieldBlkC('b_seo','bl-rich-text',[
                    'eyebrow'=>'GÜVENİN ADRESİ','title'=>'Kuşadası\'nda Neden Boblanlı Yapı?','bg'=>'#F4F4F4',
                    'body_html'=>'<p>Kuşadası ve Aydın\'da <strong>inşaat, tadilat ve elektrik</strong> alanında yılların saha tecrübesine sahip bir ekibiz. Her projeye aynı özenle yaklaşıyor; söz verdiğimiz tarihte, eksiksiz ve temiz teslim ediyoruz.</p><p>Sürpriz maliyet çıkarmayan <strong>şeffaf fiyatlandırma</strong>, net keşif ve açık teklif politikamızla güven veriyoruz. İnşaat, tadilat ve elektrik işlerimizde garantili işçilik sunuyor; Kuşadası, Söke, Davutlar ve Aydın genelinde hizmet veriyoruz.</p><h3>Neden Bizi Tercih Etmelisiniz?</h3><ul><li><strong>Deneyimli ekip:</strong> Aydın bölgesinde uzun yıllara dayanan saha tecrübesi.</li><li><strong>Zamanında teslim:</strong> Planlı süreç, gecikmesiz işçilik.</li><li><strong>Şeffaf fiyat:</strong> Net keşif, açık teklif, gizli maliyet yok.</li><li><strong>Garantili işçilik:</strong> Tüm işlerimizde garanti.</li><li><strong>Yerel bilgi:</strong> Kuşadası ve çevresini iyi tanıyan ekip.</li></ul>',
                ],3),
            ],
            // Çalışmalar — galeri + SEO metin
            'calismalar' => [
                $this->fieldBlkC('b_ph','bl-page-hero',[
                    'eyebrow'=>'REFERANSLAR','title'=>'Tamamlanan İşlerimiz','crumb'=>'Çalışmalar',
                    'subtitle'=>'Kuşadası ve çevresinde hayata geçirdiğimiz projelerden bir seçki.',
                ],1),
                $this->fieldBlk('b_galeri','bl-06-galeri',2),
                $this->fieldBlkC('b_seo','bl-rich-text',[
                    'eyebrow'=>'REFERANSLARIMIZ','title'=>'Kuşadası ve Aydın\'da Tamamladığımız Projeler','bg'=>'#F4F4F4',
                    'body_html'=>'<p>Kuşadası ve çevresinde <strong>daire tadilatından anahtar teslim inşaata</strong>, elektrik işlerinden mutfak-banyo yenilemeye kadar birçok projeyi başarıyla tamamladık. Her işte kaliteli malzeme, titiz işçilik ve müşteri memnuniyetini önceliğimiz olarak gördük.</p><p>Tamamladığımız işler; iç dekorasyon, banyo revizyonu, iş yeri tadilatı ve aydınlatma projelerinden oluşan geniş bir referans yelpazesini kapsar. Projeniz için örnek çalışmalarımızı inceleyebilir, benzer bir iş için <a href="/iletisim">bizimle iletişime geçebilirsiniz</a>.</p>',
                ],3),
            ],
            // İletişim — bilgi + harita + form
            'iletisim' => array_merge([
                $this->fieldBlkC('b_ph','bl-page-hero',[
                    'eyebrow'=>'İLETİŞİM','title'=>'Bize Ulaşın','crumb'=>'İletişim',
                    'subtitle'=>'Ücretsiz keşif için bir telefon uzağınızdayız. Ofisimize uğrayın ya da WhatsApp\'tan yazın.',
                ],1),
                $this->fieldBlk('b_iletisim','bl-07-iletisim',2),
            ], $formBlk),

            // ── Hizmet detay sayfaları (menüde değil; karttan tıklanır) ──
            'kusadasi-elektrik-ariza-onarim' => $svcDetail(
                'ELEKTRİK HİZMETİ','Kuşadası Elektrik Arıza ve Onarım','Elektrik Arıza ve Onarım',
                'Kuşadası ve Aydın\'da ev, ofis ve işyerlerinde hızlı ve garantili elektrik arıza-onarım.',
                '<p>Boblanlı Yapı olarak <strong>Kuşadası ve Aydın genelinde</strong> ev, ofis ve işyerlerinde profesyonel elektrik arıza tespiti ve onarımı yapıyoruz. Sigorta atması, kaçak akım, pano arızası, priz-anahtar ve aydınlatma sorunlarına hızlı müdahale ederek elektriğinizi en kısa sürede güvenle devreye alıyoruz.</p><p>Deneyimli elektrik ekibimiz arızanın kök nedenini tespit eder ve kalıcı çözüm sunar. Tesisat yenileme, sigorta panosu montajı ve topraklama işlerinde <strong>garantili işçilik</strong> veriyoruz.</p><h3>Elektrik Hizmetlerimiz</h3><ul><li>Elektrik arıza tespiti ve onarımı</li><li>Sigorta panosu montajı ve yenileme</li><li>Kaçak akım ve topraklama kontrolü</li><li>Priz, anahtar ve aydınlatma montajı</li><li>Ev ve işyeri elektrik tesisatı yenileme</li><li>LED ve aydınlatma çözümleri</li></ul><p>Kuşadası, Söke, Davutlar ve Aydın çevresinde elektrik arızalarınız için <a href="tel:+905326576271">bizi arayın</a> ya da WhatsApp\'tan yazın.</p>'
            ),
            'toptan-elektrik-malzemesi' => $svcDetail(
                'TOPTAN SATIŞ','Toptan Elektrik ve Malzeme Satışı','Toptan Elektrik Malzemesi',
                'Aydın bölgesinde toptan ve perakende elektrik malzemesi; geniş stok, uygun fiyat.',
                '<p>Aydın bölgesinde <strong>toptan ve perakende elektrik malzemesi</strong> satışında geniş ürün yelpazesi ve uygun fiyat sunuyoruz. Kablo, kablo makarası, pano, sigorta, priz-anahtar, LED ve aydınlatma ürünlerinde stoktan hızlı teslimat sağlıyoruz.</p><p>Müteahhitler, elektrikçiler ve işletmeler için düzenli tedarik ve proje bazlı fiyatlandırma imkanı sunuyoruz.</p><h3>Ürün Gruplarımız</h3><ul><li>Kablo ve kablo makaraları</li><li>Sigorta, pano ve şalt malzemeleri</li><li>Priz, anahtar ve tesisat malzemeleri</li><li>LED, armatür ve aydınlatma ürünleri</li></ul><p>Toptan elektrik malzemesi ihtiyaçlarınız için <a href="tel:+905326576271">bize ulaşın</a>.</p>'
            ),
            'kusadasi-dekorasyon-tadilat' => $svcDetail(
                'TADİLAT & DEKORASYON','Kuşadası Dekorasyon ve Tadilat','Dekorasyon ve Tadilat',
                'Daire, villa, ofis ve işyerleri için komple tadilat, iç dekorasyon ve anahtar teslim revizyon.',
                '<p>Daire, villa, ofis ve işyerleriniz için <strong>komple tadilat, iç dekorasyon ve kapsamlı revizyon</strong> hizmeti veriyoruz. Boya-badana, alçıpan, kartonpiyer, zemin döşeme, mutfak ve banyo yenileme işlerini tek elden, planlı bir süreçle yürütüyoruz.</p><p>Anahtar teslim daire tadilatında şeffaf fiyatlandırma ve zamanında teslim önceliğimizdir. Kuşadası ve Aydın genelinde estetik ve fonksiyonel yaşam alanları oluşturuyoruz.</p><h3>Tadilat & Dekorasyon Hizmetlerimiz</h3><ul><li>Komple daire ve ofis tadilatı</li><li>Boya, badana ve alçıpan uygulamaları</li><li>Mutfak ve banyo yenileme</li><li>Zemin döşeme (laminat, parke, seramik)</li><li>Kartonpiyer ve iç dekorasyon</li></ul><p>Tadilat ve dekorasyon projeniz için <a href="tel:+905326576271">ücretsiz keşif</a> talep edin.</p>'
            ),
            'kusadasi-aydin-insaat' => $svcDetail(
                'İNŞAAT','Kuşadası ve Aydın İnşaat Hizmetleri','İnşaat',
                'Projelendirmeden anahtar teslime konut, villa ve ticari yapı inşaatı.',
                '<p>Projelendirmeden anahtar teslime kadar <strong>profesyonel inşaat hizmeti</strong> sunuyoruz. Konut, villa ve ticari yapı projelerinde sağlam mühendislik, kaliteli malzeme ve zamanında teslim ile Kuşadası ve Aydın genelinde güvenilir bir çözüm ortağıyız.</p><p>Kaba yapı, ince yapı ve tadilat-güçlendirme işlerinde deneyimli ekibimizle her aşamada yanınızdayız.</p><h3>İnşaat Hizmetlerimiz</h3><ul><li>Anahtar teslim konut ve villa inşaatı</li><li>Ticari ve endüstriyel yapı inşaatı</li><li>Kaba ve ince yapı işleri</li><li>Yapı güçlendirme ve revizyon</li></ul><p>İnşaat projeniz için <a href="tel:+905326576271">bizimle iletişime geçin</a>.</p>'
            ),
        ];

        $pages = [
            ['slug'=>'home','title'=>'Ana Sayfa','order'=>1,'menu'=>true],
            ['slug'=>'hizmetler','title'=>'Hizmetler','order'=>2,'menu'=>true],
            ['slug'=>'neden-biz','title'=>'Neden Biz','order'=>3,'menu'=>true],
            ['slug'=>'calismalar','title'=>'Çalışmalar','order'=>4,'menu'=>true],
            ['slug'=>'iletisim','title'=>'İletişim','order'=>5,'menu'=>true],
            ['slug'=>'kusadasi-elektrik-ariza-onarim','title'=>'Kuşadası Elektrik Arıza ve Onarım','order'=>10,'menu'=>false],
            ['slug'=>'toptan-elektrik-malzemesi','title'=>'Toptan Elektrik ve Malzeme Satışı','order'=>11,'menu'=>false],
            ['slug'=>'kusadasi-dekorasyon-tadilat','title'=>'Kuşadası Dekorasyon ve Tadilat','order'=>12,'menu'=>false],
            ['slug'=>'kusadasi-aydin-insaat','title'=>'Kuşadası ve Aydın İnşaat Hizmetleri','order'=>13,'menu'=>false],
        ];

        // Trashed çakışması + tenant açılışından kalan NULL-dil scaffold sayfalarını temizle.
        Page::onlyTrashed()->whereIn('slug', array_column($pages,'slug'))->forceDelete();
        Page::whereNull('language_id')->whereNotIn('slug',['404','500'])->forceDelete();

        foreach ($pages as $p) {
            $bodyBlocks = $bodyByPage[$p['slug']] ?? [];
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

        $this->command?->info('BoblanliTenantSeeder: '.count($pages).' sayfa (çok-sayfa) + teklif formu'
            .($teklifFormId ? '' : ' (UYARI: form kurulamadı)').' + ayarlar kuruldu.');
    }
}

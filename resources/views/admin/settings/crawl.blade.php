@extends('admin.layouts.app')
@section('title', 'Tarama & AI Discovery Ayarları')

@section('content')
<div class="max-w-3xl" x-data="{
    previewRobots: false,
    robotsContent: '',
    async loadRobotsPreview() {
        this.previewRobots = true;
        const r = await fetch('{{ url('robots.txt') }}');
        this.robotsContent = await r.text();
    }
}">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Tarama & AI Discovery</h1>
            <p class="text-sm text-gray-500 mt-1">robots.txt, llms.txt ve arama motoru bot izinlerini yönetin.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ url('robots.txt') }}" target="_blank"
               class="text-xs px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 flex items-center gap-1">
                <i class="fas fa-external-link-alt text-[10px]"></i> robots.txt
            </a>
            <a href="{{ url('llms.txt') }}" target="_blank"
               class="text-xs px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 flex items-center gap-1">
                <i class="fas fa-external-link-alt text-[10px]"></i> llms.txt
            </a>
            <a href="{{ url('llms-full.txt') }}" target="_blank"
               class="text-xs px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 flex items-center gap-1">
                <i class="fas fa-external-link-alt text-[10px]"></i> llms-full.txt
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.crawl.update') }}" class="space-y-6">
        @csrf @method('PUT')

        {{-- ─── AI Bot İzinleri ──────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-1">AI Bot İzinleri</h2>
            <p class="text-xs text-gray-500 mb-4">
                robots.txt'ye eklenen User-agent kuralları. Kapalı = <code class="bg-gray-100 px-1 rounded">Disallow: /</code>
            </p>

            {{-- Global toggle --}}
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg mb-4">
                <div>
                    <p class="text-sm font-medium text-gray-700">Tüm AI Botlara İzin Ver (Varsayılan)</p>
                    <p class="text-xs text-gray-400 mt-0.5">Aşağıdaki botların varsayılan değerini bu toggle belirler.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="crawl[allow_ai_bots]" value="0">
                    <input type="checkbox" name="crawl[allow_ai_bots]" value="1" class="sr-only peer"
                           {{ ($settings['crawl.allow_ai_bots'] ?? '1') === '1' ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer
                                peer-checked:after:translate-x-full peer-checked:after:border-white
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all
                                peer-checked:bg-indigo-600"></div>
                </label>
            </div>

            {{-- Per-bot table --}}
            <div class="border rounded-lg overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-gray-600 font-medium">Bot (User-Agent)</th>
                            <th class="px-4 py-2.5 text-left text-gray-600 font-medium">Şirket / Amaç</th>
                            <th class="px-4 py-2.5 text-center text-gray-600 font-medium w-20">İzin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $botDescriptions = [
                            'GPTBot'            => ['OpenAI', 'Eğitim verisi tarama'],
                            'ChatGPT-User'      => ['OpenAI', 'ChatGPT canlı gezinme'],
                            'ClaudeBot'         => ['Anthropic', 'Claude tarama'],
                            'Claude-Web'        => ['Anthropic', 'Claude (eski)'],
                            'anthropic-ai'      => ['Anthropic', 'Anthropic genel'],
                            'PerplexityBot'     => ['Perplexity AI', 'AI arama motoru'],
                            'Google-Extended'   => ['Google', 'Google AI eğitim verisi'],
                            'CCBot'             => ['Common Crawl', 'Açık eğitim veri seti'],
                            'Bytespider'        => ['ByteDance', 'TikTok / Douyin AI'],
                            'Applebot-Extended' => ['Apple', 'Apple AI eğitim'],
                            'Amazonbot'         => ['Amazon', 'Alexa / Amazon AI'],
                            'FacebookBot'       => ['Meta', 'Meta sosyal medya'],
                            'Twitterbot'        => ['X (Twitter)', 'Link önizleme + AI'],
                            'LinkedInBot'       => ['LinkedIn', 'Link önizleme'],
                        ];
                        @endphp
                        @foreach($aiBots as $bot => $defaultAllow)
                            @php
                                $botKey  = 'crawl.bot_' . strtolower(str_replace(['-', ' '], '_', $bot));
                                $allowed = ($settings[$botKey] ?? ($defaultAllow ? '1' : '0')) === '1';
                                [$company, $desc] = $botDescriptions[$bot] ?? [$bot, ''];
                            @endphp
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-4 py-2.5 font-mono text-xs text-gray-800">{{ $bot }}</td>
                                <td class="px-4 py-2.5 text-xs text-gray-500">
                                    <span class="font-medium text-gray-700">{{ $company }}</span>
                                    @if($desc) <span>— {{ $desc }}</span> @endif
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <input type="checkbox" name="bot[{{ $bot }}]"
                                           {{ $allowed ? 'checked' : '' }}
                                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ─── llms.txt Açıklaması ─────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-1">llms.txt Açıklaması</h2>
            <p class="text-xs text-gray-500 mb-3">
                ChatGPT, Perplexity, Claude gibi AI araçlarının sitenizi tanıması için kısa açıklama.
                <a href="https://llmstxt.org" target="_blank" class="text-indigo-600 hover:underline">llmstxt.org</a>
            </p>
            <textarea name="crawl[llms_description]" rows="3" maxlength="500"
                      class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500"
                      placeholder="Örn: İstanbul merkezli özel üretim mobilya markası. 1985'ten beri ev/ofis mobilyaları üretiyoruz.">{{ $settings['crawl.llms_description'] ?? '' }}</textarea>
            <p class="text-xs text-gray-400 mt-1">Bu metin llms.txt'de <code>&gt; ...</code> satırı olarak görünür.</p>
        </div>

        {{-- ─── Gelişmiş ─────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 space-y-4">
            <h2 class="text-base font-semibold text-gray-800 mb-1">Gelişmiş robots.txt</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Crawl Delay (saniye)</label>
                <input type="number" name="crawl[crawl_delay]" min="0" max="60"
                       value="{{ $settings['crawl.crawl_delay'] ?? 0 }}"
                       class="w-32 px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500">
                <p class="text-xs text-gray-400 mt-1">0 = devre dışı. Tarama hızını yavaşlatır.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Manuel Kurallar</label>
                <textarea name="crawl[robots_custom]" rows="6"
                          class="w-full px-3 py-2 border rounded-lg text-sm font-mono focus:ring-indigo-500"
                          placeholder="# Özel kurallar&#10;User-agent: Googlebot&#10;Disallow: /private/"
                >{{ $settings['crawl.robots_custom'] ?? '' }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Bu blok robots.txt'nin sonuna, Sitemap satırının öncesine eklenir.</p>
            </div>

            {{-- Live preview --}}
            <div>
                <button type="button" @click="loadRobotsPreview()"
                        class="text-xs px-3 py-1.5 bg-gray-700 text-white rounded-lg hover:bg-gray-800">
                    <i class="fas fa-eye mr-1"></i> robots.txt Önizle
                </button>

                <div x-show="previewRobots" class="mt-3">
                    <pre x-text="robotsContent"
                         class="bg-gray-900 text-green-400 text-xs p-4 rounded-lg overflow-auto max-h-64 whitespace-pre-wrap"></pre>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                <i class="fas fa-save mr-1"></i> Kaydet
            </button>
            <a href="{{ route('admin.settings.index') }}" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">
                Genel Ayarlar
            </a>
        </div>
    </form>
</div>
@endsection

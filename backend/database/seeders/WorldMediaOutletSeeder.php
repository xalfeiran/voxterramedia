<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\MediaOutlet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * WorldMediaOutletSeeder
 *
 * Seeds ~250 of the most important news media outlets worldwide.
 * Cities are resolved by slug (must be created by GeographySeeder or
 * WorldGeographySeeder before running this seeder).
 *
 * Columns: name, url, type, language, description, founded_year, is_featured
 * type: national | newspaper | digital | tv | radio | magazine
 */
class WorldMediaOutletSeeder extends Seeder
{
    /** @var array<string, int> City slug → city ID cache */
    private array $cityCache = [];

    public function run(): void
    {
        $this->seed();
        $this->command->info('WorldMediaOutletSeeder: done.');
    }

    // ── helper ───────────────────────────────────────────────────────────────
    private function city(string $slug): ?int
    {
        if (!isset($this->cityCache[$slug])) {
            $city = City::where('slug', $slug)->first();
            if (!$city) {
                $this->command->warn("City not found: {$slug} — skipping its outlets.");
                $this->cityCache[$slug] = null;
            } else {
                $this->cityCache[$slug] = $city->id;
            }
        }
        return $this->cityCache[$slug];
    }

    private function outlet(
        string $citySlug,
        string $name,
        string $url,
        string $type,
        string $lang,
        string $description,
        int    $founded,
        bool   $featured = false
    ): void {
        $cityId = $this->city($citySlug);
        if (!$cityId) return;

        MediaOutlet::updateOrCreate(
            ['slug' => Str::slug($name)],
            [
                'city_id'      => $cityId,
                'name'         => $name,
                'url'          => $url,
                'type'         => $type,
                'language'     => $lang,
                'description'  => $description,
                'founded_year' => $founded,
                'is_featured'  => $featured,
                'is_active'    => true,
            ]
        );
    }

    // ── data ─────────────────────────────────────────────────────────────────
    private function seed(): void
    {
        // ════════════════════════════════════════════════════════════════════
        // UNITED KINGDOM
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('london', 'BBC News',          'https://www.bbc.com/news',             'national',  'en', 'The UK\'s public broadcaster, one of the world\'s most trusted news sources.',                1922, true);
        $this->outlet('london', 'The Guardian',      'https://www.theguardian.com',          'national',  'en', 'Independent British newspaper known for investigative and progressive journalism.',           1821, true);
        $this->outlet('london', 'The Times',         'https://www.thetimes.co.uk',           'newspaper', 'en', 'One of Britain\'s oldest daily newspapers, founded in 1785.',                                1785, false);
        $this->outlet('london', 'The Telegraph',     'https://www.telegraph.co.uk',          'newspaper', 'en', 'Conservative-leaning British broadsheet with strong political commentary.',                   1855, false);
        $this->outlet('london', 'Financial Times',   'https://www.ft.com',                   'national',  'en', 'The world\'s leading business and financial news publication.',                               1888, true);
        $this->outlet('london', 'The Independent',   'https://www.independent.co.uk',        'digital',   'en', 'Digital-first British news outlet with a centrist perspective.',                             1986, false);
        $this->outlet('london', 'Reuters',           'https://www.reuters.com',              'national',  'en', 'Global wire service providing real-time news to media organisations worldwide.',              1851, true);
        $this->outlet('london', 'Sky News',          'https://news.sky.com',                 'tv',        'en', 'British 24-hour news channel owned by Sky Group.',                                           1989, false);
        $this->outlet('london', 'The Economist',     'https://www.economist.com',            'magazine',  'en', 'Influential weekly magazine covering global politics, economics and business.',               1843, true);
        $this->outlet('edinburgh', 'The Scotsman',   'https://www.scotsman.com',             'newspaper', 'en', 'Scottish national daily newspaper based in Edinburgh.',                                      1817, false);

        // ════════════════════════════════════════════════════════════════════
        // FRANCE
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('paris', 'Le Monde',           'https://www.lemonde.fr',               'national',  'fr', 'France\'s newspaper of record — authoritative coverage of French and global affairs.',       1944, true);
        $this->outlet('paris', 'Le Figaro',          'https://www.lefigaro.fr',              'newspaper', 'fr', 'Conservative French daily, one of the oldest newspapers still in print.',                    1826, false);
        $this->outlet('paris', 'Libération',         'https://www.liberation.fr',            'newspaper', 'fr', 'Left-leaning French daily co-founded by Jean-Paul Sartre.',                                  1973, false);
        $this->outlet('paris', 'Les Échos',          'https://www.lesechos.fr',              'newspaper', 'fr', 'Leading French business and financial newspaper.',                                           1908, false);
        $this->outlet('paris', 'BFM TV',             'https://www.bfmtv.com',               'tv',        'fr', 'France\'s leading 24-hour TV news channel.',                                                 2005, false);
        $this->outlet('paris', 'France 24',          'https://www.france24.com',             'tv',        'fr', 'International news channel broadcasting in French, English and Arabic.',                     2006, true);
        $this->outlet('paris', 'L\'Express',         'https://www.lexpress.fr',              'magazine',  'fr', 'Major French news weekly covering politics, economy and culture.',                           1953, false);
        $this->outlet('paris', 'Le Point',           'https://www.lepoint.fr',               'magazine',  'fr', 'Conservative French news weekly with strong political and economic coverage.',               1972, false);

        // ════════════════════════════════════════════════════════════════════
        // GERMANY
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('hamburg',   'Der Spiegel',          'https://www.spiegel.de',          'magazine',  'de', 'Germany\'s most influential news magazine, known for investigative reporting.',             1947, true);
        $this->outlet('hamburg',   'Die Zeit',             'https://www.zeit.de',             'newspaper', 'de', 'Prestigious German weekly newspaper covering politics, culture and science.',                1946, false);
        $this->outlet('hamburg',   'Bild',                 'https://www.bild.de',             'newspaper', 'de', 'Germany\'s highest-circulation newspaper, tabloid style.',                                  1952, false);
        $this->outlet('hamburg',   'ARD Tagesschau',       'https://www.tagesschau.de',       'tv',        'de', 'Germany\'s main public TV news programme, broadcast by ARD.',                               1952, true);
        $this->outlet('munich',    'Süddeutsche Zeitung',  'https://www.sueddeutsche.de',     'newspaper', 'de', 'One of Germany\'s largest quality daily newspapers.',                                       1945, false);
        $this->outlet('berlin',    'Der Tagesspiegel',     'https://www.tagesspiegel.de',     'newspaper', 'de', 'Berlin\'s major daily newspaper with strong political coverage.',                           1945, false);
        $this->outlet('berlin',    'Deutsche Welle',       'https://www.dw.com',              'national',  'de', 'Germany\'s international broadcaster, reaching 250 million people worldwide.',              1953, true);
        $this->outlet('frankfurt', 'Frankfurter Allgemeine Zeitung', 'https://www.faz.net',   'newspaper', 'de', 'Conservative German daily known as the newspaper for Germany\'s elites.',                   1949, false);

        // ════════════════════════════════════════════════════════════════════
        // SPAIN
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('madrid',    'El País',          'https://elpais.com',                  'national',  'es', 'Spain\'s most-read newspaper, influential across the Spanish-speaking world.',               1976, true);
        $this->outlet('madrid',    'El Mundo',         'https://www.elmundo.es',              'newspaper', 'es', 'Spain\'s second-largest newspaper, centre-right in orientation.',                           1989, false);
        $this->outlet('madrid',    'ABC',              'https://www.abc.es',                  'newspaper', 'es', 'Conservative Spanish daily with the longest uninterrupted run.',                             1903, false);
        $this->outlet('madrid',    'RTVE Noticias',    'https://www.rtve.es/noticias',        'tv',        'es', 'Spain\'s public broadcaster, offering comprehensive domestic and international news.',       1956, false);
        $this->outlet('madrid',    'Cadena SER',       'https://cadenaser.com',               'radio',     'es', 'Spain\'s most-listened-to radio network, flagship of Prisa Radio.',                        1924, false);
        $this->outlet('barcelona', 'La Vanguardia',    'https://www.lavanguardia.com',        'newspaper', 'es', 'Barcelona\'s leading daily, the oldest in continuous publication in Spain.',                1881, false);
        $this->outlet('barcelona', 'El Periódico',     'https://www.elperiodico.com',         'newspaper', 'es', 'Catalan daily newspaper with editions in Catalan and Spanish.',                             1978, false);

        // ════════════════════════════════════════════════════════════════════
        // ITALY
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('rome',  'La Repubblica',        'https://www.repubblica.it',           'national',  'it', 'Italy\'s most-read online news outlet, centre-left in orientation.',                        1976, true);
        $this->outlet('rome',  'Corriere della Sera',  'https://www.corriere.it',             'newspaper', 'it', 'Italy\'s oldest and most influential daily newspaper.',                                     1876, true);
        $this->outlet('rome',  'RAI News',             'https://www.rainews.it',              'tv',        'it', 'Italy\'s public broadcaster\'s 24-hour news channel.',                                     1954, false);
        $this->outlet('milan', 'Il Sole 24 Ore',       'https://www.ilsole24ore.com',         'newspaper', 'it', 'Italy\'s leading business and financial daily newspaper.',                                  1865, false);
        $this->outlet('milan', 'La Stampa',            'https://www.lastampa.it',             'newspaper', 'it', 'One of Italy\'s oldest newspapers, founded in Turin.',                                      1867, false);

        // ════════════════════════════════════════════════════════════════════
        // NETHERLANDS
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('amsterdam', 'NRC Handelsblad',  'https://www.nrc.nl',                 'newspaper', 'nl', 'Netherlands\' quality broadsheet, known for cultural and political coverage.',               1970, false);
        $this->outlet('amsterdam', 'De Volkskrant',    'https://www.volkskrant.nl',           'newspaper', 'nl', 'Centre-left Dutch daily with strong arts and society coverage.',                             1919, false);
        $this->outlet('amsterdam', 'Telegraaf',        'https://www.telegraaf.nl',            'newspaper', 'nl', 'The Netherlands\' largest-circulation daily, tabloid style.',                               1893, false);
        $this->outlet('amsterdam', 'NOS Nieuws',       'https://nos.nl',                     'tv',        'nl', 'The Netherlands\' public broadcaster news service.',                                        1951, false);

        // ════════════════════════════════════════════════════════════════════
        // PORTUGAL
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('lisbon', 'Público',             'https://www.publico.pt',              'newspaper', 'pt', 'Portugal\'s leading quality daily, centrist in orientation.',                               1990, false);
        $this->outlet('lisbon', 'Expresso',            'https://expresso.pt',                 'newspaper', 'pt', 'Portugal\'s most-read weekly newspaper.',                                                   1973, false);
        $this->outlet('lisbon', 'Jornal de Notícias',  'https://www.jn.pt',                  'newspaper', 'pt', 'One of Portugal\'s oldest and most widely-read regional newspapers.',                       1888, false);

        // ════════════════════════════════════════════════════════════════════
        // POLAND
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('warsaw', 'Gazeta Wyborcza',     'https://wyborcza.pl',                 'newspaper', 'pl', 'Poland\'s leading liberal daily, founded at the end of communist rule.',                    1989, false);
        $this->outlet('warsaw', 'Rzeczpospolita',      'https://www.rp.pl',                   'newspaper', 'pl', 'Conservative Polish broadsheet covering politics and business.',                            1920, false);
        $this->outlet('warsaw', 'TVN24',               'https://tvn24.pl',                    'tv',        'pl', 'Poland\'s leading independent news channel.',                                               2001, false);

        // ════════════════════════════════════════════════════════════════════
        // SWEDEN
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('stockholm', 'Dagens Nyheter',   'https://www.dn.se',                   'newspaper', 'sv', 'Sweden\'s largest morning newspaper, politically independent.',                             1864, false);
        $this->outlet('stockholm', 'Svenska Dagbladet','https://www.svd.se',                  'newspaper', 'sv', 'Conservative Swedish daily focused on politics and business.',                              1884, false);
        $this->outlet('stockholm', 'SVT Nyheter',      'https://www.svt.se/nyheter',          'tv',        'sv', 'Sweden\'s public television news service.',                                                 1956, false);

        // ════════════════════════════════════════════════════════════════════
        // SWITZERLAND
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('zurich',  'NZZ',                'https://www.nzz.ch',                  'newspaper', 'de', 'Neue Zürcher Zeitung — Switzerland\'s leading newspaper, known for precise analysis.',     1780, false);
        $this->outlet('geneva',  'Le Temps',           'https://www.letemps.ch',              'newspaper', 'fr', 'Switzerland\'s leading French-language newspaper.',                                         1998, false);
        $this->outlet('zurich',  'SRF News',           'https://www.srf.ch/news',             'tv',        'de', 'Swiss public broadcaster\'s news division.',                                                1953, false);

        // ════════════════════════════════════════════════════════════════════
        // RUSSIA
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('moscow',          'TASS',             'https://tass.com',              'national',  'ru', 'Russia\'s main state-owned wire service, one of the world\'s largest news agencies.',      1904, false);
        $this->outlet('moscow',          'RT',               'https://www.rt.com',            'tv',        'ru', 'Russia Today — international TV channel funded by the Russian government.',                2005, false);
        $this->outlet('moscow',          'Meduza',           'https://meduza.io',             'digital',   'ru', 'Independent Russian-language outlet based in Latvia, known for objective reporting.',       2014, false);
        $this->outlet('moscow',          'Novaya Gazeta',    'https://novayagazeta.ru',       'newspaper', 'ru', 'Independent Russian investigative newspaper, multiple journalists killed.',                  1993, false);
        $this->outlet('saint-petersburg','Fontanka.ru',      'https://www.fontanka.ru',       'digital',   'ru', 'Leading St. Petersburg online news outlet.',                                                2000, false);

        // ════════════════════════════════════════════════════════════════════
        // UKRAINE
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('kyiv', 'Ukrinform',             'https://www.ukrinform.ua',            'national',  'uk', 'Ukraine\'s national news agency, founded in 1918.',                                        1918, false);
        $this->outlet('kyiv', 'Ukrainska Pravda',      'https://www.pravda.com.ua',           'digital',   'uk', 'Ukraine\'s leading independent investigative news outlet.',                                2000, false);

        // ════════════════════════════════════════════════════════════════════
        // QATAR
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('doha', 'Al Jazeera',            'https://www.aljazeera.com',           'national',  'ar', 'Qatar\'s international news network, broadcasting globally in Arabic and English.',        1996, true);
        $this->outlet('doha', 'Al Jazeera English',    'https://www.aljazeera.com/news',      'tv',        'en', 'English-language channel of Al Jazeera, covering the developing world.',                   2006, false);

        // ════════════════════════════════════════════════════════════════════
        // UNITED ARAB EMIRATES
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('dubai',     'Gulf News',         'https://gulfnews.com',               'newspaper', 'en', 'UAE\'s largest English-language daily newspaper.',                                          1978, false);
        $this->outlet('dubai',     'Khaleej Times',     'https://www.khaleejtimes.com',       'newspaper', 'en', 'English-language UAE daily founded in 1978.',                                               1978, false);
        $this->outlet('abu-dhabi', 'The National',      'https://www.thenationalnews.com',    'newspaper', 'en', 'Abu Dhabi\'s premium English-language newspaper.',                                          2008, false);
        $this->outlet('abu-dhabi', 'Sky News Arabia',   'https://www.skynewsarabia.com',     'tv',        'ar', 'Arabic-language 24-hour news channel, a joint venture of Sky and Abu Dhabi Media.',        2012, false);

        // ════════════════════════════════════════════════════════════════════
        // SAUDI ARABIA
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('riyadh', 'Arab News',            'https://www.arabnews.com',            'newspaper', 'en', 'Saudi Arabia\'s leading English-language newspaper.',                                      1975, false);
        $this->outlet('riyadh', 'Al Arabiya',           'https://www.alarabiya.net',           'tv',        'ar', 'Pan-Arab news channel headquartered in Dubai Media City, Saudi-owned.',                   2003, true);
        $this->outlet('riyadh', 'Saudi Gazette',        'https://saudigazette.com.sa',         'newspaper', 'en', 'Saudi Arabia\'s English-language daily newspaper.',                                       1976, false);

        // ════════════════════════════════════════════════════════════════════
        // EGYPT
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('cairo', 'Al-Ahram',             'https://www.ahram.org.eg',            'national',  'ar', 'Egypt\'s oldest and most widely circulated newspaper, founded in 1875.',                   1875, false);
        $this->outlet('cairo', 'Egypt Independent',    'https://egyptindependent.com',        'digital',   'en', 'English-language Egyptian news portal focused on independent journalism.',                  2009, false);
        $this->outlet('cairo', 'Al-Masry Al-Youm',    'https://www.almasryalyoum.com',        'newspaper', 'ar', 'Egypt\'s highest-circulation independent newspaper.',                                      2004, false);

        // ════════════════════════════════════════════════════════════════════
        // SOUTH AFRICA
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('johannesburg', 'Mail & Guardian',    'https://mg.co.za',              'newspaper', 'en', 'South Africa\'s premier investigative and analytical weekly newspaper.',                    1985, true);
        $this->outlet('johannesburg', 'Daily Maverick',     'https://www.dailymaverick.co.za','digital',  'en', 'South Africa\'s leading independent digital news outlet.',                                  2009, false);
        $this->outlet('johannesburg', 'Sowetan',            'https://www.sowetanlive.co.za', 'newspaper', 'en', 'South African daily targeting Black urban communities.',                                    1981, false);
        $this->outlet('cape-town',    'Cape Times',         'https://www.capetimes.co.za',   'newspaper', 'en', 'Oldest daily newspaper in Cape Town, founded in 1876.',                                    1876, false);

        // ════════════════════════════════════════════════════════════════════
        // NIGERIA
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('lagos', 'Punch Nigeria',        'https://punchng.com',                 'newspaper', 'en', 'Nigeria\'s highest-circulation daily newspaper.',                                           1973, false);
        $this->outlet('lagos', 'Vanguard Nigeria',     'https://www.vanguardngr.com',         'digital',   'en', 'One of Nigeria\'s leading national daily newspapers.',                                     1984, false);
        $this->outlet('abuja', 'The Nation Nigeria',   'https://thenationonline.net',         'newspaper', 'en', 'Nigerian national daily with strong political analysis.',                                   2006, false);

        // ════════════════════════════════════════════════════════════════════
        // KENYA
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('nairobi', 'Daily Nation',       'https://nation.africa',               'national',  'en', 'Kenya\'s and East Africa\'s most widely-read newspaper group.',                             1960, true);
        $this->outlet('nairobi', 'The Standard',       'https://www.standardmedia.co.ke',     'newspaper', 'en', 'Kenya\'s oldest existing newspaper, founded in 1902.',                                     1902, false);
        $this->outlet('nairobi', 'NTV Kenya',          'https://www.ntv.co.ke',               'tv',        'en', 'Kenya\'s leading independent TV news channel.',                                            1999, false);

        // ════════════════════════════════════════════════════════════════════
        // MOROCCO
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('casablanca', 'Le Matin',        'https://www.lematin.ma',              'newspaper', 'fr', 'Morocco\'s government-aligned French-language daily newspaper.',                            1971, false);
        $this->outlet('casablanca', 'L\'Économiste',   'https://www.leconomiste.com',         'newspaper', 'fr', 'Morocco\'s leading French-language business daily.',                                       1991, false);

        // ════════════════════════════════════════════════════════════════════
        // TURKEY
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('istanbul', 'Hürriyet',          'https://www.hurriyet.com.tr',         'newspaper', 'tr', 'Turkey\'s highest-circulation daily newspaper.',                                           1948, false);
        $this->outlet('istanbul', 'Sabah',             'https://www.sabah.com.tr',            'newspaper', 'tr', 'Turkish daily newspaper with strong pro-government orientation.',                          1985, false);
        $this->outlet('istanbul', 'Cumhuriyet',        'https://www.cumhuriyet.com.tr',       'newspaper', 'tr', 'Turkey\'s oldest newspaper, founded in 1924, known for secularist views.',                 1924, false);
        $this->outlet('istanbul', 'Bianet',            'https://bianet.org',                  'digital',   'tr', 'Turkey\'s independent online news portal focusing on human rights.',                       2000, false);

        // ════════════════════════════════════════════════════════════════════
        // ISRAEL
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('tel-aviv', 'Haaretz',           'https://www.haaretz.com',             'newspaper', 'he', 'Israel\'s oldest daily newspaper, known for liberal and critical reporting.',              1919, false);
        $this->outlet('tel-aviv', 'The Jerusalem Post','https://www.jpost.com',               'newspaper', 'en', 'Israel\'s leading English-language newspaper.',                                            1932, false);
        $this->outlet('tel-aviv', 'Ynet News',         'https://www.ynetnews.com',            'digital',   'he', 'Israel\'s most-visited news website.',                                                     1995, false);

        // ════════════════════════════════════════════════════════════════════
        // JAPAN
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('tokyo', 'Asahi Shimbun',        'https://www.asahi.com',               'national',  'ja', 'Japan\'s second-largest newspaper, known for liberal editorial stance.',                   1879, true);
        $this->outlet('tokyo', 'Yomiuri Shimbun',      'https://www.yomiuri.co.jp',           'national',  'ja', 'World\'s highest-circulation daily newspaper with ~7 million copies.',                    1874, true);
        $this->outlet('tokyo', 'Nikkei',               'https://www.nikkei.com',              'newspaper', 'ja', 'Japan\'s leading business and financial newspaper.',                                       1876, false);
        $this->outlet('tokyo', 'NHK World',            'https://www3.nhk.or.jp/nhkworld',     'tv',        'ja', 'Japan\'s public broadcaster\'s international service.',                                   1925, true);
        $this->outlet('tokyo', 'The Japan Times',      'https://www.japantimes.co.jp',        'newspaper', 'en', 'Japan\'s oldest English-language newspaper.',                                              1897, false);
        $this->outlet('tokyo', 'Mainichi Shimbun',     'https://mainichi.jp',                 'national',  'ja', 'One of Japan\'s five national newspapers, centrist in orientation.',                       1872, false);
        $this->outlet('osaka', 'Sankei Shimbun',       'https://www.sankei.com',              'newspaper', 'ja', 'Japan\'s conservative national daily newspaper.',                                         1933, false);

        // ════════════════════════════════════════════════════════════════════
        // CHINA
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('beijing',  'People\'s Daily',        'https://en.people.cn',           'national',  'zh', 'Official newspaper of the Central Committee of the Chinese Communist Party.',              1948, false);
        $this->outlet('beijing',  'Xinhua News Agency',     'https://www.xinhuanet.com',      'national',  'zh', 'China\'s official state news agency and the world\'s largest.',                           1931, false);
        $this->outlet('beijing',  'China Daily',            'https://www.chinadaily.com.cn',  'national',  'en', 'China\'s official English-language newspaper targeting international audiences.',          1981, false);
        $this->outlet('beijing',  'Global Times',           'https://www.globaltimes.cn',     'newspaper', 'en', 'English-language Chinese tabloid known for nationalist commentary.',                       2009, false);
        $this->outlet('shanghai', 'Caixin Media',           'https://www.caixinglobal.com',   'digital',   'zh', 'China\'s leading independent business and financial news outlet.',                        2009, true);
        $this->outlet('beijing',  'CGTN',                   'https://www.cgtn.com',           'tv',        'zh', 'China Global Television Network — China\'s international broadcaster.',                    2016, false);

        // ════════════════════════════════════════════════════════════════════
        // INDIA
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('new-delhi', 'The Hindu',         'https://www.thehindu.com',           'national',  'en', 'One of India\'s most respected English-language newspapers, founded in 1878.',             1878, true);
        $this->outlet('new-delhi', 'Hindustan Times',   'https://www.hindustantimes.com',     'national',  'en', 'One of India\'s largest English dailies based in New Delhi.',                             1924, false);
        $this->outlet('new-delhi', 'NDTV',              'https://www.ndtv.com',               'tv',        'en', 'India\'s leading English-language 24-hour news channel.',                                  1988, true);
        $this->outlet('mumbai',    'Times of India',    'https://timesofindia.indiatimes.com','national',  'en', 'India\'s largest-selling English-language newspaper.',                                     1838, true);
        $this->outlet('mumbai',    'Economic Times',    'https://economictimes.indiatimes.com','newspaper','en', 'India\'s largest business and financial newspaper.',                                        1961, false);
        $this->outlet('bangalore', 'The Wire',          'https://thewire.in',                 'digital',   'en', 'India\'s leading independent investigative news portal.',                                  2015, false);
        $this->outlet('new-delhi', 'India Today',       'https://www.indiatoday.in',          'magazine',  'en', 'India\'s most widely-read English-language news magazine.',                               1975, false);
        $this->outlet('new-delhi', 'Scroll.in',         'https://scroll.in',                  'digital',   'en', 'Independent Indian digital news outlet with a liberal perspective.',                      2014, false);

        // ════════════════════════════════════════════════════════════════════
        // AUSTRALIA
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('sydney',    'Sydney Morning Herald',  'https://www.smh.com.au',        'national',  'en', 'Australia\'s oldest newspaper and flagship of Nine Publishing.',                          1831, true);
        $this->outlet('sydney',    'The Australian',         'https://www.theaustralian.com.au','national','en', 'Australia\'s national broadsheet, owned by News Corp.',                                    1964, false);
        $this->outlet('sydney',    'News.com.au',            'https://www.news.com.au',       'digital',   'en', 'Australia\'s highest-traffic news website.',                                               2000, false);
        $this->outlet('melbourne', 'The Age',                'https://www.theage.com.au',     'newspaper', 'en', 'Melbourne\'s major daily newspaper, founded in 1854.',                                    1854, false);
        $this->outlet('canberra',  'ABC News Australia',     'https://www.abc.net.au/news',   'national',  'en', 'Australia\'s public broadcaster, largest news organisation in the Pacific.',              1932, true);
        $this->outlet('sydney',    'The Guardian Australia', 'https://www.theguardian.com/au','digital',   'en', 'Australian edition of The Guardian, with a progressive editorial approach.',               2013, false);

        // ════════════════════════════════════════════════════════════════════
        // SOUTH KOREA
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('seoul', 'JoongAng Ilbo',        'https://www.joongang.co.kr',          'national',  'ko', 'One of South Korea\'s three major broadsheet newspapers.',                                 1965, false);
        $this->outlet('seoul', 'Chosun Ilbo',          'https://www.chosun.com',              'national',  'ko', 'South Korea\'s highest-circulation newspaper, conservative in orientation.',              1920, false);
        $this->outlet('seoul', 'Hankyoreh',            'https://www.hani.co.kr',              'newspaper', 'ko', 'South Korea\'s leading progressive daily newspaper.',                                      1988, false);
        $this->outlet('seoul', 'Korea Herald',         'https://www.koreaherald.com',         'newspaper', 'en', 'South Korea\'s leading English-language newspaper.',                                       1953, false);
        $this->outlet('seoul', 'KBS World',            'https://world.kbs.co.kr',             'tv',        'ko', 'Korean Broadcasting System\'s international service.',                                     1947, false);

        // ════════════════════════════════════════════════════════════════════
        // SINGAPORE
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('singapore', 'The Straits Times', 'https://www.straitstimes.com',       'national',  'en', 'Singapore\'s flagship English-language newspaper and Southeast Asia\'s most read.',        1845, true);
        $this->outlet('singapore', 'CNA',               'https://www.channelnewsasia.com',    'tv',        'en', 'Channel NewsAsia — Singapore\'s international English-language news channel.',             1999, false);
        $this->outlet('singapore', 'The Business Times','https://www.businesstimes.com.sg',   'newspaper', 'en', 'Singapore\'s leading business and financial newspaper.',                                   1976, false);
        $this->outlet('singapore', 'Today Online',      'https://www.todayonline.com',        'digital',   'en', 'Singapore\'s free daily newspaper and digital news outlet.',                               2000, false);

        // ════════════════════════════════════════════════════════════════════
        // INDONESIA
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('jakarta', 'Kompas',              'https://www.kompas.com',              'national',  'id', 'Indonesia\'s most-read newspaper and digital news portal.',                                1965, true);
        $this->outlet('jakarta', 'Tempo',               'https://en.tempo.co',                 'magazine',  'id', 'Indonesia\'s most influential investigative news magazine.',                               1971, false);
        $this->outlet('jakarta', 'Detik.com',           'https://www.detik.com',               'digital',   'id', 'Indonesia\'s highest-traffic news website.',                                              1998, false);
        $this->outlet('jakarta', 'The Jakarta Post',    'https://www.thejakartapost.com',      'newspaper', 'en', 'Indonesia\'s leading English-language daily.',                                            1983, false);

        // ════════════════════════════════════════════════════════════════════
        // PAKISTAN
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('lahore',  'Dawn',               'https://www.dawn.com',                 'national',  'en', 'Pakistan\'s oldest and most widely-read English-language newspaper.',                     1941, true);
        $this->outlet('karachi', 'Geo News',           'https://www.geo.tv',                   'tv',        'ur', 'Pakistan\'s leading Urdu-language 24-hour TV news channel.',                              2002, false);
        $this->outlet('lahore',  'The News International','https://www.thenews.com.pk',        'newspaper', 'en', 'Pakistan\'s second-largest English-language daily.',                                      1991, false);

        // ════════════════════════════════════════════════════════════════════
        // BRAZIL
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('sao-paulo',    'Folha de S.Paulo',  'https://www.folha.uol.com.br',    'national',  'pt', 'Brazil\'s highest-circulation daily newspaper.',                                           1921, true);
        $this->outlet('sao-paulo',    'Estadão',           'https://www.estadao.com.br',      'national',  'pt', 'O Estado de S. Paulo — one of Brazil\'s most important newspapers.',                      1875, false);
        $this->outlet('sao-paulo',    'UOL Notícias',      'https://noticias.uol.com.br',     'digital',   'pt', 'Brazil\'s largest internet company news portal.',                                         1996, false);
        $this->outlet('rio-de-janeiro','O Globo',          'https://oglobo.globo.com',        'national',  'pt', 'Rio de Janeiro\'s major daily and one of Brazil\'s most influential.',                    1925, true);
        $this->outlet('sao-paulo',    'Veja',              'https://veja.abril.com.br',       'magazine',  'pt', 'Brazil\'s most widely read weekly news magazine.',                                         1968, false);
        $this->outlet('brasilia',     'Agência Brasil',    'https://agenciabrasil.ebc.com.br','national',  'pt', 'Brazil\'s official government news agency.',                                               2003, false);
        $this->outlet('sao-paulo',    'CNN Brasil',        'https://www.cnnbrasil.com.br',    'tv',        'pt', 'Brazilian affiliate of CNN International, launched in 2020.',                              2020, false);

        // ════════════════════════════════════════════════════════════════════
        // ARGENTINA
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('buenos-aires', 'La Nación',      'https://www.lanacion.com.ar',         'national',  'es', 'Argentina\'s oldest major daily newspaper, founded by Bartolomé Mitre.',                  1870, true);
        $this->outlet('buenos-aires', 'Clarín',         'https://www.clarin.com',              'national',  'es', 'Argentina\'s highest-circulation newspaper and media conglomerate.',                      1945, true);
        $this->outlet('buenos-aires', 'Infobae',        'https://www.infobae.com',             'digital',   'es', 'Argentina\'s leading digital news portal, with extensive Latin America coverage.',        2002, false);
        $this->outlet('buenos-aires', 'Página/12',      'https://www.pagina12.com.ar',         'newspaper', 'es', 'Argentine left-wing daily known for its investigative journalism.',                       1987, false);
        $this->outlet('buenos-aires', 'TN',             'https://tn.com.ar',                   'tv',        'es', 'Todo Noticias — Argentina\'s leading 24-hour news channel.',                              1993, false);

        // ════════════════════════════════════════════════════════════════════
        // COLOMBIA
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('bogota',   'El Tiempo',          'https://www.eltiempo.com',             'national',  'es', 'Colombia\'s largest and most influential newspaper.',                                     1911, true);
        $this->outlet('bogota',   'El Espectador',      'https://www.elespectador.com',         'newspaper', 'es', 'Colombia\'s second-oldest major newspaper, known for investigative work.',               1887, false);
        $this->outlet('bogota',   'Semana',             'https://www.semana.com',               'magazine',  'es', 'Colombia\'s most influential weekly news magazine.',                                      1982, false);
        $this->outlet('medellin', 'El Colombiano',      'https://www.elcolombiano.com',         'newspaper', 'es', 'Medellín\'s leading newspaper and one of Colombia\'s oldest.',                          1912, false);

        // ════════════════════════════════════════════════════════════════════
        // CHILE
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('santiago', 'El Mercurio',        'https://www.emol.com',                 'national',  'es', 'Chile\'s oldest major newspaper and most influential media group.',                       1900, false);
        $this->outlet('santiago', 'La Tercera',         'https://www.latercera.com',             'newspaper', 'es', 'Chile\'s second-largest circulation daily newspaper.',                                   1950, false);
        $this->outlet('santiago', 'CIPER Chile',        'https://www.ciperchile.cl',            'digital',   'es', 'Chile\'s leading investigative journalism centre.',                                      2007, false);

        // ════════════════════════════════════════════════════════════════════
        // PERU
        // ════════════════════════════════════════════════════════════════════
        $this->outlet('lima', 'El Comercio',            'https://elcomercio.pe',                 'national',  'es', 'Peru\'s oldest and most influential newspaper, founded in 1839.',                        1839, false);
        $this->outlet('lima', 'La República',           'https://larepublica.pe',                'newspaper', 'es', 'Peru\'s leading centre-left daily newspaper.',                                           1981, false);
        $this->outlet('lima', 'RPP Noticias',           'https://rpp.pe',                        'radio',     'es', 'Peru\'s most-listened-to radio news network.',                                           1949, false);
    }
}

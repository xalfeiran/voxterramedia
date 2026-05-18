<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\MediaOutlet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MediaOutletSeeder extends Seeder
{
    public function run(): void
    {
        // Look up cities by slug — no cache dependency
        $city = fn(string $slug) => City::where('slug', $slug)->firstOrFail();

        $outlets = [
            // ── CANADA (14) ──────────────────────────────────────────────
            ['slug' => 'toronto',   'name' => 'The Globe and Mail',      'url' => 'https://www.theglobeandmail.com',    'type' => 'national',  'language' => 'en', 'featured' => true,  'founded' => 1844],
            ['slug' => 'toronto',   'name' => 'Toronto Star',            'url' => 'https://www.thestar.com',            'type' => 'newspaper', 'language' => 'en', 'featured' => true,  'founded' => 1892],
            ['slug' => 'toronto',   'name' => 'National Post',           'url' => 'https://nationalpost.com',           'type' => 'national',  'language' => 'en', 'featured' => false, 'founded' => 1998],
            ['slug' => 'toronto',   'name' => 'CBC News',                'url' => 'https://www.cbc.ca/news',            'type' => 'tv',        'language' => 'en', 'featured' => true,  'founded' => 1936],
            ['slug' => 'toronto',   'name' => 'CTV News',                'url' => 'https://www.ctvnews.ca',             'type' => 'tv',        'language' => 'en', 'featured' => false, 'founded' => 1961],
            ['slug' => 'montreal',  'name' => 'Le Devoir',               'url' => 'https://www.ledevoir.com',           'type' => 'newspaper', 'language' => 'fr', 'featured' => false, 'founded' => 1910],
            ['slug' => 'montreal',  'name' => 'La Presse',               'url' => 'https://www.lapresse.ca',            'type' => 'digital',   'language' => 'fr', 'featured' => false, 'founded' => 1884],
            ['slug' => 'montreal',  'name' => 'Montreal Gazette',        'url' => 'https://montrealgazette.com',        'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1778],
            ['slug' => 'vancouver', 'name' => 'Vancouver Sun',           'url' => 'https://vancouversun.com',           'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1912],
            ['slug' => 'calgary',   'name' => 'Calgary Herald',          'url' => 'https://calgaryherald.com',          'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1883],
            ['slug' => 'edmonton',  'name' => 'Edmonton Journal',        'url' => 'https://edmontonjournal.com',        'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1903],
            ['slug' => 'ottawa',    'name' => 'Ottawa Citizen',          'url' => 'https://ottawacitizen.com',          'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1845],
            ['slug' => 'winnipeg',  'name' => 'Winnipeg Free Press',     'url' => 'https://www.winnipegfreepress.com',  'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1872],
            ['slug' => 'halifax',   'name' => 'The Chronicle Herald',    'url' => 'https://www.saltwire.com',           'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1875],

            // ── USA (28) ─────────────────────────────────────────────────
            ['slug' => 'new-york',      'name' => 'The New York Times',          'url' => 'https://www.nytimes.com',          'type' => 'national',  'language' => 'en', 'featured' => true,  'founded' => 1851],
            ['slug' => 'new-york',      'name' => 'The Wall Street Journal',     'url' => 'https://www.wsj.com',              'type' => 'national',  'language' => 'en', 'featured' => true,  'founded' => 1889],
            ['slug' => 'new-york',      'name' => 'New York Post',               'url' => 'https://nypost.com',               'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1801],
            ['slug' => 'washington-dc', 'name' => 'The Washington Post',         'url' => 'https://www.washingtonpost.com',   'type' => 'national',  'language' => 'en', 'featured' => true,  'founded' => 1877],
            ['slug' => 'washington-dc', 'name' => 'Politico',                   'url' => 'https://www.politico.com',         'type' => 'digital',   'language' => 'en', 'featured' => false, 'founded' => 2007],
            ['slug' => 'mclean',        'name' => 'USA Today',                  'url' => 'https://www.usatoday.com',         'type' => 'national',  'language' => 'en', 'featured' => false, 'founded' => 1982],
            ['slug' => 'los-angeles',   'name' => 'Los Angeles Times',          'url' => 'https://www.latimes.com',          'type' => 'newspaper', 'language' => 'en', 'featured' => true,  'founded' => 1881],
            ['slug' => 'san-francisco', 'name' => 'San Francisco Chronicle',    'url' => 'https://www.sfchronicle.com',      'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1865],
            ['slug' => 'chicago',       'name' => 'Chicago Tribune',            'url' => 'https://www.chicagotribune.com',   'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1847],
            ['slug' => 'chicago',       'name' => 'Chicago Sun-Times',          'url' => 'https://chicago.suntimes.com',     'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1948],
            ['slug' => 'boston',        'name' => 'The Boston Globe',           'url' => 'https://www.bostonglobe.com',      'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1872],
            ['slug' => 'philadelphia',  'name' => 'The Philadelphia Inquirer',  'url' => 'https://www.inquirer.com',         'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1829],
            ['slug' => 'atlanta',       'name' => 'The Atlanta Journal-Constitution', 'url' => 'https://www.ajc.com',        'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1868],
            ['slug' => 'miami',         'name' => 'Miami Herald',               'url' => 'https://www.miamiherald.com',      'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1903],
            ['slug' => 'tampa',         'name' => 'Tampa Bay Times',            'url' => 'https://www.tampabay.com',         'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1884],
            ['slug' => 'houston',       'name' => 'The Houston Chronicle',      'url' => 'https://www.houstonchronicle.com', 'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1901],
            ['slug' => 'dallas',        'name' => 'The Dallas Morning News',    'url' => 'https://www.dallasnews.com',       'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1885],
            ['slug' => 'austin',        'name' => 'Austin American-Statesman',  'url' => 'https://www.statesman.com',        'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1871],
            ['slug' => 'seattle',       'name' => 'The Seattle Times',          'url' => 'https://www.seattletimes.com',     'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1891],
            ['slug' => 'denver',        'name' => 'The Denver Post',            'url' => 'https://www.denverpost.com',       'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1892],
            ['slug' => 'phoenix',       'name' => 'The Arizona Republic',       'url' => 'https://www.azcentral.com',        'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1890],
            ['slug' => 'detroit',       'name' => 'The Detroit Free Press',     'url' => 'https://www.freep.com',            'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1831],
            ['slug' => 'cleveland',     'name' => 'The Plain Dealer',           'url' => 'https://www.cleveland.com',        'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1842],
            ['slug' => 'minneapolis',   'name' => 'The Star Tribune',           'url' => 'https://www.startribune.com',      'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1867],
            ['slug' => 'portland',      'name' => 'The Oregonian',              'url' => 'https://www.oregonlive.com',       'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1850],
            ['slug' => 'honolulu',      'name' => 'Honolulu Star-Advertiser',   'url' => 'https://www.staradvertiser.com',   'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1882],
            ['slug' => 'anchorage',     'name' => 'Anchorage Daily News',       'url' => 'https://www.adn.com',              'type' => 'newspaper', 'language' => 'en', 'featured' => false, 'founded' => 1946],

            // ── MEXICO (24) ──────────────────────────────────────────────
            ['slug' => 'ciudad-de-mexico', 'name' => 'Reforma',               'url' => 'https://www.reforma.com',              'type' => 'national',  'language' => 'es', 'featured' => true,  'founded' => 1993],
            ['slug' => 'ciudad-de-mexico', 'name' => 'El Universal',          'url' => 'https://www.eluniversal.com.mx',       'type' => 'national',  'language' => 'es', 'featured' => true,  'founded' => 1916],
            ['slug' => 'ciudad-de-mexico', 'name' => 'Milenio',               'url' => 'https://www.milenio.com',              'type' => 'national',  'language' => 'es', 'featured' => false, 'founded' => 2000],
            ['slug' => 'ciudad-de-mexico', 'name' => 'La Jornada',            'url' => 'https://www.jornada.com.mx',           'type' => 'national',  'language' => 'es', 'featured' => false, 'founded' => 1984],
            ['slug' => 'ciudad-de-mexico', 'name' => 'Excélsior',             'url' => 'https://www.excelsior.com.mx',         'type' => 'national',  'language' => 'es', 'featured' => false, 'founded' => 1917],
            ['slug' => 'ciudad-de-mexico', 'name' => 'El Financiero',         'url' => 'https://www.elfinanciero.com.mx',      'type' => 'national',  'language' => 'es', 'featured' => false, 'founded' => 1981],
            ['slug' => 'ciudad-de-mexico', 'name' => 'Animal Político',       'url' => 'https://www.animalpolitico.com',       'type' => 'digital',   'language' => 'es', 'featured' => true,  'founded' => 2010],
            ['slug' => 'ciudad-de-mexico', 'name' => 'Aristegui Noticias',    'url' => 'https://aristeguinoticias.com',        'type' => 'digital',   'language' => 'es', 'featured' => false, 'founded' => 2013],
            ['slug' => 'monterrey',        'name' => 'El Norte',              'url' => 'https://www.elnorte.com',              'type' => 'newspaper', 'language' => 'es', 'featured' => false, 'founded' => 1938],
            ['slug' => 'monterrey',        'name' => 'Milenio Monterrey',     'url' => 'https://www.milenio.com/monterrey',    'type' => 'newspaper', 'language' => 'es', 'featured' => false, 'founded' => 2000],
            ['slug' => 'guadalajara',      'name' => 'Mural',                 'url' => 'https://www.mural.com.mx',             'type' => 'newspaper', 'language' => 'es', 'featured' => false, 'founded' => 1999],
            ['slug' => 'guadalajara',      'name' => 'El Informador',         'url' => 'https://www.informador.mx',            'type' => 'newspaper', 'language' => 'es', 'featured' => false, 'founded' => 1917],
            ['slug' => 'merida',           'name' => 'Diario de Yucatán',     'url' => 'https://www.yucatan.com.mx',           'type' => 'newspaper', 'language' => 'es', 'featured' => true,  'founded' => 1925],
            ['slug' => 'merida',           'name' => 'Por Esto!',             'url' => 'https://www.poresto.net',              'type' => 'newspaper', 'language' => 'es', 'featured' => false, 'founded' => 1991],
            ['slug' => 'cancun',           'name' => 'Novedades Quintana Roo','url' => 'https://sipse.com/novedades',          'type' => 'newspaper', 'language' => 'es', 'featured' => false, 'founded' => 1974],
            ['slug' => 'cancun',           'name' => 'La Jornada Maya',       'url' => 'https://www.lajornadamaya.mx',         'type' => 'newspaper', 'language' => 'es', 'featured' => false, 'founded' => 2013],
            ['slug' => 'hermosillo',       'name' => 'El Imparcial',          'url' => 'https://www.elimparcial.com',          'type' => 'newspaper', 'language' => 'es', 'featured' => false, 'founded' => 1937],
            ['slug' => 'ciudad-juarez',    'name' => 'El Diario de Juárez',   'url' => 'https://diario.mx',                   'type' => 'newspaper', 'language' => 'es', 'featured' => false, 'founded' => 1976],
            ['slug' => 'tijuana',          'name' => 'Frontera',              'url' => 'https://www.frontera.info',            'type' => 'newspaper', 'language' => 'es', 'featured' => false, 'founded' => 2000],
            ['slug' => 'puebla',           'name' => 'El Sol de Puebla',      'url' => 'https://www.elsoldepuebla.com.mx',    'type' => 'newspaper', 'language' => 'es', 'featured' => false, 'founded' => 1944],
            ['slug' => 'veracruz',         'name' => 'El Diario de Veracruz', 'url' => 'https://www.diariodexalapa.com.mx',   'type' => 'newspaper', 'language' => 'es', 'featured' => false, 'founded' => 1921],
            ['slug' => 'culiacan',         'name' => 'Noroeste',              'url' => 'https://www.noroeste.com.mx',          'type' => 'newspaper', 'language' => 'es', 'featured' => false, 'founded' => 1973],
            ['slug' => 'queretaro',        'name' => 'AM Querétaro',          'url' => 'https://amqueretaro.com',              'type' => 'newspaper', 'language' => 'es', 'featured' => false, 'founded' => 1993],
            ['slug' => 'oaxaca',           'name' => 'El Universal Oaxaca',   'url' => 'https://oaxaca.eluniversal.com.mx',   'type' => 'newspaper', 'language' => 'es', 'featured' => false, 'founded' => 1916],
        ];

        foreach ($outlets as $data) {
            MediaOutlet::create([
                'city_id'      => $city($data['slug'])->id,
                'name'         => $data['name'],
                'slug'         => Str::slug($data['name']),
                'url'          => $data['url'],
                'type'         => $data['type'],
                'language'     => $data['language'],
                'is_active'    => true,
                'is_featured'  => $data['featured'],
                'founded_year' => $data['founded'] ?? null,
            ]);
        }
    }
}

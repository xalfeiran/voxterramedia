<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use Illuminate\Database\Seeder;

/**
 * WorldGeographySeeder
 *
 * Seeds countries, regions, and key cities for ~28 countries outside of
 * North America (CA/US/MX are handled by GeographySeeder).
 *
 * Covers: Europe, Middle East, Africa, Asia-Pacific, Latin America.
 */
class WorldGeographySeeder extends Seeder
{
    public function run(): void
    {
        // ── UNITED KINGDOM ──────────────────────────────────────────────────
        $gb = Country::firstOrCreate(['code' => 'GB'], [
            'name' => 'United Kingdom', 'name_es' => 'Reino Unido',
            'slug' => 'united-kingdom', 'flag_emoji' => '🇬🇧',
            'latitude' => 55.3781, 'longitude' => -3.4360, 'default_zoom' => 5,
        ]);
        $england = Region::firstOrCreate(['country_id' => $gb->id, 'code' => 'ENG'], [
            'name' => 'England', 'name_es' => 'Inglaterra',
            'slug' => 'england', 'latitude' => 52.3555, 'longitude' => -1.1743,
        ]);
        $scotland = Region::firstOrCreate(['country_id' => $gb->id, 'code' => 'SCT'], [
            'name' => 'Scotland', 'name_es' => 'Escocia',
            'slug' => 'scotland', 'latitude' => 56.4907, 'longitude' => -4.2026,
        ]);
        City::firstOrCreate(['slug' => 'london'],       ['region_id' => $england->id,  'name' => 'London',    'latitude' =>  51.5074, 'longitude' =>  -0.1278]);
        City::firstOrCreate(['slug' => 'manchester'],   ['region_id' => $england->id,  'name' => 'Manchester','latitude' =>  53.4808, 'longitude' =>  -2.2426]);
        City::firstOrCreate(['slug' => 'edinburgh'],    ['region_id' => $scotland->id, 'name' => 'Edinburgh', 'latitude' =>  55.9533, 'longitude' =>  -3.1883]);

        // ── FRANCE ──────────────────────────────────────────────────────────
        $fr = Country::firstOrCreate(['code' => 'FR'], [
            'name' => 'France', 'name_es' => 'Francia',
            'slug' => 'france', 'flag_emoji' => '🇫🇷',
            'latitude' => 46.2276, 'longitude' => 2.2137, 'default_zoom' => 5,
        ]);
        $idf = Region::firstOrCreate(['country_id' => $fr->id, 'code' => 'IDF'], [
            'name' => 'Île-de-France', 'name_es' => 'Isla de Francia',
            'slug' => 'ile-de-france', 'latitude' => 48.8499, 'longitude' => 2.6370,
        ]);
        $aura = Region::firstOrCreate(['country_id' => $fr->id, 'code' => 'ARA'], [
            'name' => 'Auvergne-Rhône-Alpes', 'name_es' => 'Auvernia-Ródano-Alpes',
            'slug' => 'auvergne-rhone-alpes', 'latitude' => 45.4473, 'longitude' => 4.3852,
        ]);
        City::firstOrCreate(['slug' => 'paris'], ['region_id' => $idf->id,  'name' => 'Paris', 'latitude' => 48.8566, 'longitude' =>  2.3522]);
        City::firstOrCreate(['slug' => 'lyon'],  ['region_id' => $aura->id, 'name' => 'Lyon',  'latitude' => 45.7640, 'longitude' =>  4.8357]);

        // ── GERMANY ─────────────────────────────────────────────────────────
        $de = Country::firstOrCreate(['code' => 'DE'], [
            'name' => 'Germany', 'name_es' => 'Alemania',
            'slug' => 'germany', 'flag_emoji' => '🇩🇪',
            'latitude' => 51.1657, 'longitude' => 10.4515, 'default_zoom' => 5,
        ]);
        $berlin_state = Region::firstOrCreate(['country_id' => $de->id, 'code' => 'BE'], [
            'name' => 'Berlin', 'name_es' => 'Berlín',
            'slug' => 'berlin', 'latitude' => 52.5200, 'longitude' => 13.4050,
        ]);
        $bavaria = Region::firstOrCreate(['country_id' => $de->id, 'code' => 'BY'], [
            'name' => 'Bavaria', 'name_es' => 'Baviera',
            'slug' => 'bavaria', 'latitude' => 48.7904, 'longitude' => 11.4979,
        ]);
        $hamburg_state = Region::firstOrCreate(['country_id' => $de->id, 'code' => 'HH'], [
            'name' => 'Hamburg', 'name_es' => 'Hamburgo',
            'slug' => 'hamburg', 'latitude' => 53.5511, 'longitude' =>  9.9937,
        ]);
        $hesse = Region::firstOrCreate(['country_id' => $de->id, 'code' => 'HE'], [
            'name' => 'Hesse', 'name_es' => 'Hesse',
            'slug' => 'hesse', 'latitude' => 50.6521, 'longitude' => 9.1624,
        ]);
        City::firstOrCreate(['slug' => 'berlin'],    ['region_id' => $berlin_state->id,  'name' => 'Berlin',    'latitude' => 52.5200, 'longitude' => 13.4050]);
        City::firstOrCreate(['slug' => 'munich'],    ['region_id' => $bavaria->id,       'name' => 'Munich',    'latitude' => 48.1351, 'longitude' => 11.5820]);
        City::firstOrCreate(['slug' => 'hamburg'],   ['region_id' => $hamburg_state->id, 'name' => 'Hamburg',   'latitude' => 53.5511, 'longitude' =>  9.9937]);
        City::firstOrCreate(['slug' => 'frankfurt'], ['region_id' => $hesse->id,         'name' => 'Frankfurt', 'latitude' => 50.1109, 'longitude' =>  8.6821]);

        // ── SPAIN ────────────────────────────────────────────────────────────
        $es = Country::firstOrCreate(['code' => 'ES'], [
            'name' => 'Spain', 'name_es' => 'España',
            'slug' => 'spain', 'flag_emoji' => '🇪🇸',
            'latitude' => 40.4637, 'longitude' => -3.7492, 'default_zoom' => 5,
        ]);
        $madrid_com = Region::firstOrCreate(['country_id' => $es->id, 'code' => 'MD'], [
            'name' => 'Community of Madrid', 'name_es' => 'Comunidad de Madrid',
            'slug' => 'comunidad-de-madrid', 'latitude' => 40.4168, 'longitude' => -3.7038,
        ]);
        $catalonia = Region::firstOrCreate(['country_id' => $es->id, 'code' => 'CT'], [
            'name' => 'Catalonia', 'name_es' => 'Cataluña',
            'slug' => 'catalonia', 'latitude' => 41.5912, 'longitude' =>  1.5209,
        ]);
        City::firstOrCreate(['slug' => 'madrid'],    ['region_id' => $madrid_com->id, 'name' => 'Madrid',    'latitude' => 40.4168, 'longitude' => -3.7038]);
        City::firstOrCreate(['slug' => 'barcelona'], ['region_id' => $catalonia->id,  'name' => 'Barcelona', 'latitude' => 41.3851, 'longitude' =>  2.1734]);

        // ── ITALY ────────────────────────────────────────────────────────────
        $it = Country::firstOrCreate(['code' => 'IT'], [
            'name' => 'Italy', 'name_es' => 'Italia',
            'slug' => 'italy', 'flag_emoji' => '🇮🇹',
            'latitude' => 41.8719, 'longitude' => 12.5674, 'default_zoom' => 5,
        ]);
        $lazio = Region::firstOrCreate(['country_id' => $it->id, 'code' => 'LAZ'], [
            'name' => 'Lazio', 'name_es' => 'Lacio',
            'slug' => 'lazio', 'latitude' => 41.8719, 'longitude' => 12.5674,
        ]);
        $lombardia = Region::firstOrCreate(['country_id' => $it->id, 'code' => 'LOM'], [
            'name' => 'Lombardy', 'name_es' => 'Lombardía',
            'slug' => 'lombardy', 'latitude' => 45.4654, 'longitude' =>  9.1859,
        ]);
        City::firstOrCreate(['slug' => 'rome'],  ['region_id' => $lazio->id,     'name' => 'Rome',  'latitude' => 41.9028, 'longitude' => 12.4964]);
        City::firstOrCreate(['slug' => 'milan'], ['region_id' => $lombardia->id, 'name' => 'Milan', 'latitude' => 45.4642, 'longitude' =>  9.1900]);

        // ── NETHERLANDS ──────────────────────────────────────────────────────
        $nl = Country::firstOrCreate(['code' => 'NL'], [
            'name' => 'Netherlands', 'name_es' => 'Países Bajos',
            'slug' => 'netherlands', 'flag_emoji' => '🇳🇱',
            'latitude' => 52.1326, 'longitude' => 5.2913, 'default_zoom' => 6,
        ]);
        $nh = Region::firstOrCreate(['country_id' => $nl->id, 'code' => 'NH'], [
            'name' => 'North Holland', 'name_es' => 'Holanda del Norte',
            'slug' => 'north-holland', 'latitude' => 52.5205, 'longitude' => 4.7683,
        ]);
        City::firstOrCreate(['slug' => 'amsterdam'], ['region_id' => $nh->id, 'name' => 'Amsterdam', 'latitude' => 52.3676, 'longitude' => 4.9041]);

        // ── PORTUGAL ─────────────────────────────────────────────────────────
        $pt = Country::firstOrCreate(['code' => 'PT'], [
            'name' => 'Portugal', 'name_es' => 'Portugal',
            'slug' => 'portugal', 'flag_emoji' => '🇵🇹',
            'latitude' => 39.3999, 'longitude' => -8.2245, 'default_zoom' => 6,
        ]);
        $lisboa_reg = Region::firstOrCreate(['country_id' => $pt->id, 'code' => 'LIS'], [
            'name' => 'Lisbon Metropolitan Area', 'name_es' => 'Área Metropolitana de Lisboa',
            'slug' => 'lisbon-metropolitan', 'latitude' => 38.7223, 'longitude' => -9.1393,
        ]);
        City::firstOrCreate(['slug' => 'lisbon'], ['region_id' => $lisboa_reg->id, 'name' => 'Lisbon', 'latitude' => 38.7223, 'longitude' => -9.1393]);

        // ── POLAND ───────────────────────────────────────────────────────────
        $pl = Country::firstOrCreate(['code' => 'PL'], [
            'name' => 'Poland', 'name_es' => 'Polonia',
            'slug' => 'poland', 'flag_emoji' => '🇵🇱',
            'latitude' => 51.9194, 'longitude' => 19.1451, 'default_zoom' => 5,
        ]);
        $masovian = Region::firstOrCreate(['country_id' => $pl->id, 'code' => 'MZ'], [
            'name' => 'Masovian', 'name_es' => 'Mazovia',
            'slug' => 'masovian', 'latitude' => 52.2297, 'longitude' => 21.0122,
        ]);
        City::firstOrCreate(['slug' => 'warsaw'], ['region_id' => $masovian->id, 'name' => 'Warsaw', 'latitude' => 52.2297, 'longitude' => 21.0122]);

        // ── SWEDEN ───────────────────────────────────────────────────────────
        $se = Country::firstOrCreate(['code' => 'SE'], [
            'name' => 'Sweden', 'name_es' => 'Suecia',
            'slug' => 'sweden', 'flag_emoji' => '🇸🇪',
            'latitude' => 60.1282, 'longitude' => 18.6435, 'default_zoom' => 5,
        ]);
        $stockholm_reg = Region::firstOrCreate(['country_id' => $se->id, 'code' => 'AB'], [
            'name' => 'Stockholm County', 'name_es' => 'Condado de Estocolmo',
            'slug' => 'stockholm-county', 'latitude' => 59.3293, 'longitude' => 18.0686,
        ]);
        City::firstOrCreate(['slug' => 'stockholm'], ['region_id' => $stockholm_reg->id, 'name' => 'Stockholm', 'latitude' => 59.3293, 'longitude' => 18.0686]);

        // ── SWITZERLAND ──────────────────────────────────────────────────────
        $ch = Country::firstOrCreate(['code' => 'CH'], [
            'name' => 'Switzerland', 'name_es' => 'Suiza',
            'slug' => 'switzerland', 'flag_emoji' => '🇨🇭',
            'latitude' => 46.8182, 'longitude' => 8.2275, 'default_zoom' => 6,
        ]);
        $ch_ge = Region::firstOrCreate(['country_id' => $ch->id, 'code' => 'GE'], [
            'name' => 'Geneva', 'name_es' => 'Ginebra',
            'slug' => 'canton-geneva', 'latitude' => 46.2044, 'longitude' => 6.1432,
        ]);
        $ch_zh = Region::firstOrCreate(['country_id' => $ch->id, 'code' => 'ZH'], [
            'name' => 'Zurich', 'name_es' => 'Zúrich',
            'slug' => 'canton-zurich', 'latitude' => 47.3769, 'longitude' => 8.5417,
        ]);
        City::firstOrCreate(['slug' => 'geneva'], ['region_id' => $ch_ge->id, 'name' => 'Geneva', 'latitude' => 46.2044, 'longitude' => 6.1432]);
        City::firstOrCreate(['slug' => 'zurich'], ['region_id' => $ch_zh->id, 'name' => 'Zurich', 'latitude' => 47.3769, 'longitude' => 8.5417]);

        // ── RUSSIA ───────────────────────────────────────────────────────────
        $ru = Country::firstOrCreate(['code' => 'RU'], [
            'name' => 'Russia', 'name_es' => 'Rusia',
            'slug' => 'russia', 'flag_emoji' => '🇷🇺',
            'latitude' => 61.5240, 'longitude' => 105.3188, 'default_zoom' => 3,
        ]);
        $moscow_obl = Region::firstOrCreate(['country_id' => $ru->id, 'code' => 'MOW'], [
            'name' => 'Moscow', 'name_es' => 'Moscú',
            'slug' => 'moscow-region', 'latitude' => 55.7558, 'longitude' => 37.6173,
        ]);
        $spb_obl = Region::firstOrCreate(['country_id' => $ru->id, 'code' => 'SPE'], [
            'name' => 'Saint Petersburg', 'name_es' => 'San Petersburgo',
            'slug' => 'saint-petersburg-region', 'latitude' => 59.9311, 'longitude' => 30.3609,
        ]);
        City::firstOrCreate(['slug' => 'moscow'],          ['region_id' => $moscow_obl->id, 'name' => 'Moscow',           'latitude' => 55.7558, 'longitude' => 37.6173]);
        City::firstOrCreate(['slug' => 'saint-petersburg'],['region_id' => $spb_obl->id,    'name' => 'Saint Petersburg', 'latitude' => 59.9311, 'longitude' => 30.3609]);

        // ── UKRAINE ──────────────────────────────────────────────────────────
        $ua = Country::firstOrCreate(['code' => 'UA'], [
            'name' => 'Ukraine', 'name_es' => 'Ucrania',
            'slug' => 'ukraine', 'flag_emoji' => '🇺🇦',
            'latitude' => 48.3794, 'longitude' => 31.1656, 'default_zoom' => 5,
        ]);
        $kyiv_reg = Region::firstOrCreate(['country_id' => $ua->id, 'code' => 'KV'], [
            'name' => 'Kyiv', 'name_es' => 'Kiev',
            'slug' => 'kyiv-region', 'latitude' => 50.4501, 'longitude' => 30.5234,
        ]);
        City::firstOrCreate(['slug' => 'kyiv'], ['region_id' => $kyiv_reg->id, 'name' => 'Kyiv', 'latitude' => 50.4501, 'longitude' => 30.5234]);

        // ── QATAR ────────────────────────────────────────────────────────────
        $qa = Country::firstOrCreate(['code' => 'QA'], [
            'name' => 'Qatar', 'name_es' => 'Catar',
            'slug' => 'qatar', 'flag_emoji' => '🇶🇦',
            'latitude' => 25.3548, 'longitude' => 51.1839, 'default_zoom' => 8,
        ]);
        $doha_reg = Region::firstOrCreate(['country_id' => $qa->id, 'code' => 'DA'], [
            'name' => 'Ad Dawhah', 'name_es' => 'Ad Dawha',
            'slug' => 'ad-dawhah', 'latitude' => 25.2854, 'longitude' => 51.5310,
        ]);
        City::firstOrCreate(['slug' => 'doha'], ['region_id' => $doha_reg->id, 'name' => 'Doha', 'latitude' => 25.2854, 'longitude' => 51.5310]);

        // ── UNITED ARAB EMIRATES ─────────────────────────────────────────────
        $ae = Country::firstOrCreate(['code' => 'AE'], [
            'name' => 'United Arab Emirates', 'name_es' => 'Emiratos Árabes Unidos',
            'slug' => 'uae', 'flag_emoji' => '🇦🇪',
            'latitude' => 23.4241, 'longitude' => 53.8478, 'default_zoom' => 7,
        ]);
        $dubai_reg = Region::firstOrCreate(['country_id' => $ae->id, 'code' => 'DU'], [
            'name' => 'Dubai', 'name_es' => 'Dubái',
            'slug' => 'dubai-emirate', 'latitude' => 25.2048, 'longitude' => 55.2708,
        ]);
        $abudhabi_reg = Region::firstOrCreate(['country_id' => $ae->id, 'code' => 'AZ'], [
            'name' => 'Abu Dhabi', 'name_es' => 'Abu Dabi',
            'slug' => 'abu-dhabi-emirate', 'latitude' => 24.4539, 'longitude' => 54.3773,
        ]);
        City::firstOrCreate(['slug' => 'dubai'],     ['region_id' => $dubai_reg->id,   'name' => 'Dubai',     'latitude' => 25.2048, 'longitude' => 55.2708]);
        City::firstOrCreate(['slug' => 'abu-dhabi'], ['region_id' => $abudhabi_reg->id,'name' => 'Abu Dhabi', 'latitude' => 24.4539, 'longitude' => 54.3773]);

        // ── SAUDI ARABIA ─────────────────────────────────────────────────────
        $sa = Country::firstOrCreate(['code' => 'SA'], [
            'name' => 'Saudi Arabia', 'name_es' => 'Arabia Saudita',
            'slug' => 'saudi-arabia', 'flag_emoji' => '🇸🇦',
            'latitude' => 23.8859, 'longitude' => 45.0792, 'default_zoom' => 5,
        ]);
        $riyadh_reg = Region::firstOrCreate(['country_id' => $sa->id, 'code' => 'RI'], [
            'name' => 'Riyadh Region', 'name_es' => 'Región de Riad',
            'slug' => 'riyadh-region', 'latitude' => 24.7136, 'longitude' => 46.6753,
        ]);
        City::firstOrCreate(['slug' => 'riyadh'], ['region_id' => $riyadh_reg->id, 'name' => 'Riyadh', 'latitude' => 24.7136, 'longitude' => 46.6753]);

        // ── EGYPT ────────────────────────────────────────────────────────────
        $eg = Country::firstOrCreate(['code' => 'EG'], [
            'name' => 'Egypt', 'name_es' => 'Egipto',
            'slug' => 'egypt', 'flag_emoji' => '🇪🇬',
            'latitude' => 26.8206, 'longitude' => 30.8025, 'default_zoom' => 5,
        ]);
        $cairo_reg = Region::firstOrCreate(['country_id' => $eg->id, 'code' => 'C'], [
            'name' => 'Cairo Governorate', 'name_es' => 'Gobernación de El Cairo',
            'slug' => 'cairo-governorate', 'latitude' => 30.0444, 'longitude' => 31.2357,
        ]);
        City::firstOrCreate(['slug' => 'cairo'], ['region_id' => $cairo_reg->id, 'name' => 'Cairo', 'latitude' => 30.0444, 'longitude' => 31.2357]);

        // ── SOUTH AFRICA ─────────────────────────────────────────────────────
        $za = Country::firstOrCreate(['code' => 'ZA'], [
            'name' => 'South Africa', 'name_es' => 'Sudáfrica',
            'slug' => 'south-africa', 'flag_emoji' => '🇿🇦',
            'latitude' => -30.5595, 'longitude' => 22.9375, 'default_zoom' => 5,
        ]);
        $gauteng = Region::firstOrCreate(['country_id' => $za->id, 'code' => 'GT'], [
            'name' => 'Gauteng', 'name_es' => 'Gauteng',
            'slug' => 'gauteng', 'latitude' => -26.2708, 'longitude' => 28.1123,
        ]);
        $western_cape = Region::firstOrCreate(['country_id' => $za->id, 'code' => 'WC'], [
            'name' => 'Western Cape', 'name_es' => 'Cabo Occidental',
            'slug' => 'western-cape', 'latitude' => -33.2278, 'longitude' => 21.8569,
        ]);
        City::firstOrCreate(['slug' => 'johannesburg'], ['region_id' => $gauteng->id,     'name' => 'Johannesburg', 'latitude' => -26.2041, 'longitude' => 28.0473]);
        City::firstOrCreate(['slug' => 'cape-town'],    ['region_id' => $western_cape->id,'name' => 'Cape Town',    'latitude' => -33.9249, 'longitude' => 18.4241]);

        // ── NIGERIA ──────────────────────────────────────────────────────────
        $ng = Country::firstOrCreate(['code' => 'NG'], [
            'name' => 'Nigeria', 'name_es' => 'Nigeria',
            'slug' => 'nigeria', 'flag_emoji' => '🇳🇬',
            'latitude' => 9.0820, 'longitude' => 8.6753, 'default_zoom' => 5,
        ]);
        $lagos_state = Region::firstOrCreate(['country_id' => $ng->id, 'code' => 'LA'], [
            'name' => 'Lagos State', 'name_es' => 'Estado de Lagos',
            'slug' => 'lagos-state', 'latitude' => 6.5244, 'longitude' => 3.3792,
        ]);
        $fct = Region::firstOrCreate(['country_id' => $ng->id, 'code' => 'FC'], [
            'name' => 'Federal Capital Territory', 'name_es' => 'Territorio de la Capital Federal',
            'slug' => 'fct-nigeria', 'latitude' => 9.0765, 'longitude' => 7.3986,
        ]);
        City::firstOrCreate(['slug' => 'lagos'], ['region_id' => $lagos_state->id, 'name' => 'Lagos', 'latitude' =>  6.5244, 'longitude' =>  3.3792]);
        City::firstOrCreate(['slug' => 'abuja'], ['region_id' => $fct->id,         'name' => 'Abuja', 'latitude' =>  9.0765, 'longitude' =>  7.3986]);

        // ── KENYA ────────────────────────────────────────────────────────────
        $ke = Country::firstOrCreate(['code' => 'KE'], [
            'name' => 'Kenya', 'name_es' => 'Kenia',
            'slug' => 'kenya', 'flag_emoji' => '🇰🇪',
            'latitude' => -0.0236, 'longitude' => 37.9062, 'default_zoom' => 5,
        ]);
        $nairobi_co = Region::firstOrCreate(['country_id' => $ke->id, 'code' => 'NBI'], [
            'name' => 'Nairobi County', 'name_es' => 'Condado de Nairobi',
            'slug' => 'nairobi-county', 'latitude' => -1.2921, 'longitude' => 36.8219,
        ]);
        City::firstOrCreate(['slug' => 'nairobi'], ['region_id' => $nairobi_co->id, 'name' => 'Nairobi', 'latitude' => -1.2921, 'longitude' => 36.8219]);

        // ── MOROCCO ──────────────────────────────────────────────────────────
        $ma = Country::firstOrCreate(['code' => 'MA'], [
            'name' => 'Morocco', 'name_es' => 'Marruecos',
            'slug' => 'morocco', 'flag_emoji' => '🇲🇦',
            'latitude' => 31.7917, 'longitude' => -7.0926, 'default_zoom' => 5,
        ]);
        $casa_reg = Region::firstOrCreate(['country_id' => $ma->id, 'code' => 'CAS'], [
            'name' => 'Casablanca-Settat', 'name_es' => 'Casablanca-Settat',
            'slug' => 'casablanca-settat', 'latitude' => 33.5731, 'longitude' => -7.5898,
        ]);
        City::firstOrCreate(['slug' => 'casablanca'], ['region_id' => $casa_reg->id, 'name' => 'Casablanca', 'latitude' => 33.5731, 'longitude' => -7.5898]);

        // ── JAPAN ────────────────────────────────────────────────────────────
        $jp = Country::firstOrCreate(['code' => 'JP'], [
            'name' => 'Japan', 'name_es' => 'Japón',
            'slug' => 'japan', 'flag_emoji' => '🇯🇵',
            'latitude' => 36.2048, 'longitude' => 138.2529, 'default_zoom' => 5,
        ]);
        $tokyo_metro = Region::firstOrCreate(['country_id' => $jp->id, 'code' => 'TK'], [
            'name' => 'Tokyo Metropolis', 'name_es' => 'Metrópolis de Tokio',
            'slug' => 'tokyo-metropolis', 'latitude' => 35.6762, 'longitude' => 139.6503,
        ]);
        $osaka_pref = Region::firstOrCreate(['country_id' => $jp->id, 'code' => 'OS'], [
            'name' => 'Osaka Prefecture', 'name_es' => 'Prefectura de Osaka',
            'slug' => 'osaka-prefecture', 'latitude' => 34.6937, 'longitude' => 135.5022,
        ]);
        City::firstOrCreate(['slug' => 'tokyo'], ['region_id' => $tokyo_metro->id, 'name' => 'Tokyo', 'latitude' => 35.6762, 'longitude' => 139.6503]);
        City::firstOrCreate(['slug' => 'osaka'], ['region_id' => $osaka_pref->id,  'name' => 'Osaka', 'latitude' => 34.6937, 'longitude' => 135.5022]);

        // ── CHINA ────────────────────────────────────────────────────────────
        $cn = Country::firstOrCreate(['code' => 'CN'], [
            'name' => 'China', 'name_es' => 'China',
            'slug' => 'china', 'flag_emoji' => '🇨🇳',
            'latitude' => 35.8617, 'longitude' => 104.1954, 'default_zoom' => 4,
        ]);
        $beijing_mun = Region::firstOrCreate(['country_id' => $cn->id, 'code' => 'BJ'], [
            'name' => 'Beijing Municipality', 'name_es' => 'Municipio de Pekín',
            'slug' => 'beijing-municipality', 'latitude' => 39.9042, 'longitude' => 116.4074,
        ]);
        $shanghai_mun = Region::firstOrCreate(['country_id' => $cn->id, 'code' => 'SH'], [
            'name' => 'Shanghai Municipality', 'name_es' => 'Municipio de Shanghái',
            'slug' => 'shanghai-municipality', 'latitude' => 31.2304, 'longitude' => 121.4737,
        ]);
        City::firstOrCreate(['slug' => 'beijing'],  ['region_id' => $beijing_mun->id,  'name' => 'Beijing',  'latitude' => 39.9042, 'longitude' => 116.4074]);
        City::firstOrCreate(['slug' => 'shanghai'], ['region_id' => $shanghai_mun->id, 'name' => 'Shanghai', 'latitude' => 31.2304, 'longitude' => 121.4737]);

        // ── INDIA ────────────────────────────────────────────────────────────
        $in = Country::firstOrCreate(['code' => 'IN'], [
            'name' => 'India', 'name_es' => 'India',
            'slug' => 'india', 'flag_emoji' => '🇮🇳',
            'latitude' => 20.5937, 'longitude' => 78.9629, 'default_zoom' => 4,
        ]);
        $delhi_ut = Region::firstOrCreate(['country_id' => $in->id, 'code' => 'DL'], [
            'name' => 'Delhi', 'name_es' => 'Delhi',
            'slug' => 'delhi', 'latitude' => 28.7041, 'longitude' => 77.1025,
        ]);
        $maharashtra = Region::firstOrCreate(['country_id' => $in->id, 'code' => 'MH'], [
            'name' => 'Maharashtra', 'name_es' => 'Maharashtra',
            'slug' => 'maharashtra', 'latitude' => 19.7515, 'longitude' => 75.7139,
        ]);
        $karnataka = Region::firstOrCreate(['country_id' => $in->id, 'code' => 'KA'], [
            'name' => 'Karnataka', 'name_es' => 'Karnataka',
            'slug' => 'karnataka', 'latitude' => 15.3173, 'longitude' => 75.7139,
        ]);
        $tamil_nadu = Region::firstOrCreate(['country_id' => $in->id, 'code' => 'TN'], [
            'name' => 'Tamil Nadu', 'name_es' => 'Tamil Nadu',
            'slug' => 'tamil-nadu', 'latitude' => 11.1271, 'longitude' => 78.6569,
        ]);
        City::firstOrCreate(['slug' => 'new-delhi'], ['region_id' => $delhi_ut->id,    'name' => 'New Delhi',  'latitude' => 28.6139, 'longitude' => 77.2090]);
        City::firstOrCreate(['slug' => 'mumbai'],    ['region_id' => $maharashtra->id, 'name' => 'Mumbai',     'latitude' => 19.0760, 'longitude' => 72.8777]);
        City::firstOrCreate(['slug' => 'bangalore'], ['region_id' => $karnataka->id,   'name' => 'Bangalore',  'latitude' => 12.9716, 'longitude' => 77.5946]);
        City::firstOrCreate(['slug' => 'chennai'],   ['region_id' => $tamil_nadu->id,  'name' => 'Chennai',    'latitude' => 13.0827, 'longitude' => 80.2707]);

        // ── AUSTRALIA ────────────────────────────────────────────────────────
        $au = Country::firstOrCreate(['code' => 'AU'], [
            'name' => 'Australia', 'name_es' => 'Australia',
            'slug' => 'australia', 'flag_emoji' => '🇦🇺',
            'latitude' => -25.2744, 'longitude' => 133.7751, 'default_zoom' => 4,
        ]);
        $nsw = Region::firstOrCreate(['country_id' => $au->id, 'code' => 'NSW'], [
            'name' => 'New South Wales', 'name_es' => 'Nueva Gales del Sur',
            'slug' => 'new-south-wales', 'latitude' => -33.8688, 'longitude' => 151.2093,
        ]);
        $victoria = Region::firstOrCreate(['country_id' => $au->id, 'code' => 'VIC'], [
            'name' => 'Victoria', 'name_es' => 'Victoria',
            'slug' => 'victoria-au', 'latitude' => -37.8136, 'longitude' => 144.9631,
        ]);
        $act = Region::firstOrCreate(['country_id' => $au->id, 'code' => 'ACT'], [
            'name' => 'Australian Capital Territory', 'name_es' => 'Territorio de la Capital Australiana',
            'slug' => 'act', 'latitude' => -35.2809, 'longitude' => 149.1300,
        ]);
        City::firstOrCreate(['slug' => 'sydney'],   ['region_id' => $nsw->id,      'name' => 'Sydney',   'latitude' => -33.8688, 'longitude' => 151.2093]);
        City::firstOrCreate(['slug' => 'melbourne'],['region_id' => $victoria->id, 'name' => 'Melbourne','latitude' => -37.8136, 'longitude' => 144.9631]);
        City::firstOrCreate(['slug' => 'canberra'], ['region_id' => $act->id,      'name' => 'Canberra', 'latitude' => -35.2809, 'longitude' => 149.1300]);

        // ── SOUTH KOREA ──────────────────────────────────────────────────────
        $kr = Country::firstOrCreate(['code' => 'KR'], [
            'name' => 'South Korea', 'name_es' => 'Corea del Sur',
            'slug' => 'south-korea', 'flag_emoji' => '🇰🇷',
            'latitude' => 35.9078, 'longitude' => 127.7669, 'default_zoom' => 6,
        ]);
        $seoul_metro = Region::firstOrCreate(['country_id' => $kr->id, 'code' => 'SO'], [
            'name' => 'Seoul Metropolitan', 'name_es' => 'Área Metropolitana de Seúl',
            'slug' => 'seoul-metropolitan', 'latitude' => 37.5665, 'longitude' => 126.9780,
        ]);
        City::firstOrCreate(['slug' => 'seoul'], ['region_id' => $seoul_metro->id, 'name' => 'Seoul', 'latitude' => 37.5665, 'longitude' => 126.9780]);

        // ── SINGAPORE ────────────────────────────────────────────────────────
        $sg = Country::firstOrCreate(['code' => 'SG'], [
            'name' => 'Singapore', 'name_es' => 'Singapur',
            'slug' => 'singapore', 'flag_emoji' => '🇸🇬',
            'latitude' => 1.3521, 'longitude' => 103.8198, 'default_zoom' => 10,
        ]);
        $sg_central = Region::firstOrCreate(['country_id' => $sg->id, 'code' => 'CR'], [
            'name' => 'Central Region', 'name_es' => 'Región Central',
            'slug' => 'singapore-central', 'latitude' => 1.3521, 'longitude' => 103.8198,
        ]);
        City::firstOrCreate(['slug' => 'singapore'], ['region_id' => $sg_central->id, 'name' => 'Singapore', 'latitude' => 1.3521, 'longitude' => 103.8198]);

        // ── INDONESIA ────────────────────────────────────────────────────────
        $id = Country::firstOrCreate(['code' => 'ID'], [
            'name' => 'Indonesia', 'name_es' => 'Indonesia',
            'slug' => 'indonesia', 'flag_emoji' => '🇮🇩',
            'latitude' => -0.7893, 'longitude' => 113.9213, 'default_zoom' => 4,
        ]);
        $dki = Region::firstOrCreate(['country_id' => $id->id, 'code' => 'JK'], [
            'name' => 'DKI Jakarta', 'name_es' => 'Yakarta',
            'slug' => 'dki-jakarta', 'latitude' => -6.2088, 'longitude' => 106.8456,
        ]);
        City::firstOrCreate(['slug' => 'jakarta'], ['region_id' => $dki->id, 'name' => 'Jakarta', 'latitude' => -6.2088, 'longitude' => 106.8456]);

        // ── PAKISTAN ─────────────────────────────────────────────────────────
        $pk = Country::firstOrCreate(['code' => 'PK'], [
            'name' => 'Pakistan', 'name_es' => 'Pakistán',
            'slug' => 'pakistan', 'flag_emoji' => '🇵🇰',
            'latitude' => 30.3753, 'longitude' => 69.3451, 'default_zoom' => 5,
        ]);
        $punjab_pk = Region::firstOrCreate(['country_id' => $pk->id, 'code' => 'PB'], [
            'name' => 'Punjab', 'name_es' => 'Punyab',
            'slug' => 'punjab-pk', 'latitude' => 31.1704, 'longitude' => 72.7097,
        ]);
        $sindh = Region::firstOrCreate(['country_id' => $pk->id, 'code' => 'SD'], [
            'name' => 'Sindh', 'name_es' => 'Sindh',
            'slug' => 'sindh', 'latitude' => 25.8943, 'longitude' => 68.5247,
        ]);
        City::firstOrCreate(['slug' => 'lahore'],  ['region_id' => $punjab_pk->id, 'name' => 'Lahore',  'latitude' => 31.5497, 'longitude' => 74.3436]);
        City::firstOrCreate(['slug' => 'karachi'], ['region_id' => $sindh->id,     'name' => 'Karachi', 'latitude' => 24.8607, 'longitude' => 67.0011]);

        // ── BRAZIL ───────────────────────────────────────────────────────────
        $br = Country::firstOrCreate(['code' => 'BR'], [
            'name' => 'Brazil', 'name_es' => 'Brasil',
            'slug' => 'brazil', 'flag_emoji' => '🇧🇷',
            'latitude' => -14.2350, 'longitude' => -51.9253, 'default_zoom' => 4,
        ]);
        $sp_state = Region::firstOrCreate(['country_id' => $br->id, 'code' => 'SP'], [
            'name' => 'São Paulo', 'name_es' => 'São Paulo',
            'slug' => 'sao-paulo-state', 'latitude' => -23.5505, 'longitude' => -46.6333,
        ]);
        $rj_state = Region::firstOrCreate(['country_id' => $br->id, 'code' => 'RJ'], [
            'name' => 'Rio de Janeiro', 'name_es' => 'Río de Janeiro',
            'slug' => 'rio-de-janeiro-state', 'latitude' => -22.9068, 'longitude' => -43.1729,
        ]);
        $df_br = Region::firstOrCreate(['country_id' => $br->id, 'code' => 'DF'], [
            'name' => 'Federal District', 'name_es' => 'Distrito Federal',
            'slug' => 'distrito-federal-br', 'latitude' => -15.7801, 'longitude' => -47.9292,
        ]);
        City::firstOrCreate(['slug' => 'sao-paulo'],   ['region_id' => $sp_state->id, 'name' => 'São Paulo',   'latitude' => -23.5505, 'longitude' => -46.6333]);
        City::firstOrCreate(['slug' => 'rio-de-janeiro'],['region_id' => $rj_state->id,'name'=> 'Rio de Janeiro','latitude'=> -22.9068, 'longitude' => -43.1729]);
        City::firstOrCreate(['slug' => 'brasilia'],    ['region_id' => $df_br->id,    'name' => 'Brasília',    'latitude' => -15.7801, 'longitude' => -47.9292]);

        // ── ARGENTINA ────────────────────────────────────────────────────────
        $ar = Country::firstOrCreate(['code' => 'AR'], [
            'name' => 'Argentina', 'name_es' => 'Argentina',
            'slug' => 'argentina', 'flag_emoji' => '🇦🇷',
            'latitude' => -38.4161, 'longitude' => -63.6167, 'default_zoom' => 4,
        ]);
        $ba_prov = Region::firstOrCreate(['country_id' => $ar->id, 'code' => 'BA'], [
            'name' => 'Buenos Aires Province', 'name_es' => 'Provincia de Buenos Aires',
            'slug' => 'buenos-aires-province', 'latitude' => -36.6769, 'longitude' => -60.5588,
        ]);
        $cordoba_prov = Region::firstOrCreate(['country_id' => $ar->id, 'code' => 'CBA'], [
            'name' => 'Córdoba Province', 'name_es' => 'Provincia de Córdoba',
            'slug' => 'cordoba-province-ar', 'latitude' => -31.4135, 'longitude' => -64.1811,
        ]);
        City::firstOrCreate(['slug' => 'buenos-aires'], ['region_id' => $ba_prov->id,    'name' => 'Buenos Aires', 'latitude' => -34.6037, 'longitude' => -58.3816]);
        City::firstOrCreate(['slug' => 'cordoba-ar'],   ['region_id' => $cordoba_prov->id,'name' => 'Córdoba',     'latitude' => -31.4135, 'longitude' => -64.1811]);

        // ── COLOMBIA ─────────────────────────────────────────────────────────
        $co = Country::firstOrCreate(['code' => 'CO'], [
            'name' => 'Colombia', 'name_es' => 'Colombia',
            'slug' => 'colombia', 'flag_emoji' => '🇨🇴',
            'latitude' => 4.5709, 'longitude' => -74.2973, 'default_zoom' => 5,
        ]);
        $bogota_dc = Region::firstOrCreate(['country_id' => $co->id, 'code' => 'DC'], [
            'name' => 'Bogotá D.C.', 'name_es' => 'Bogotá D.C.',
            'slug' => 'bogota-dc', 'latitude' => 4.7110, 'longitude' => -74.0721,
        ]);
        $antioquia = Region::firstOrCreate(['country_id' => $co->id, 'code' => 'ANT'], [
            'name' => 'Antioquia', 'name_es' => 'Antioquia',
            'slug' => 'antioquia', 'latitude' => 7.1986, 'longitude' => -75.3412,
        ]);
        City::firstOrCreate(['slug' => 'bogota'],   ['region_id' => $bogota_dc->id, 'name' => 'Bogotá',   'latitude' =>  4.7110, 'longitude' => -74.0721]);
        City::firstOrCreate(['slug' => 'medellin'], ['region_id' => $antioquia->id, 'name' => 'Medellín', 'latitude' =>  6.2442, 'longitude' => -75.5812]);

        // ── CHILE ────────────────────────────────────────────────────────────
        $cl = Country::firstOrCreate(['code' => 'CL'], [
            'name' => 'Chile', 'name_es' => 'Chile',
            'slug' => 'chile', 'flag_emoji' => '🇨🇱',
            'latitude' => -35.6751, 'longitude' => -71.5430, 'default_zoom' => 4,
        ]);
        $santiago_reg = Region::firstOrCreate(['country_id' => $cl->id, 'code' => 'RM'], [
            'name' => 'Metropolitan Region', 'name_es' => 'Región Metropolitana',
            'slug' => 'region-metropolitana-cl', 'latitude' => -33.4489, 'longitude' => -70.6693,
        ]);
        City::firstOrCreate(['slug' => 'santiago'], ['region_id' => $santiago_reg->id, 'name' => 'Santiago', 'latitude' => -33.4489, 'longitude' => -70.6693]);

        // ── PERU ─────────────────────────────────────────────────────────────
        $pe = Country::firstOrCreate(['code' => 'PE'], [
            'name' => 'Peru', 'name_es' => 'Perú',
            'slug' => 'peru', 'flag_emoji' => '🇵🇪',
            'latitude' => -9.1900, 'longitude' => -75.0152, 'default_zoom' => 5,
        ]);
        $lima_reg = Region::firstOrCreate(['country_id' => $pe->id, 'code' => 'LIM'], [
            'name' => 'Lima Region', 'name_es' => 'Región Lima',
            'slug' => 'lima-region', 'latitude' => -12.0464, 'longitude' => -77.0428,
        ]);
        City::firstOrCreate(['slug' => 'lima'], ['region_id' => $lima_reg->id, 'name' => 'Lima', 'latitude' => -12.0464, 'longitude' => -77.0428]);

        // ── TURKEY ───────────────────────────────────────────────────────────
        $tr = Country::firstOrCreate(['code' => 'TR'], [
            'name' => 'Turkey', 'name_es' => 'Turquía',
            'slug' => 'turkey', 'flag_emoji' => '🇹🇷',
            'latitude' => 38.9637, 'longitude' => 35.2433, 'default_zoom' => 5,
        ]);
        $istanbul_prov = Region::firstOrCreate(['country_id' => $tr->id, 'code' => 'IS'], [
            'name' => 'Istanbul Province', 'name_es' => 'Provincia de Estambul',
            'slug' => 'istanbul-province', 'latitude' => 41.0082, 'longitude' => 28.9784,
        ]);
        City::firstOrCreate(['slug' => 'istanbul'], ['region_id' => $istanbul_prov->id, 'name' => 'Istanbul', 'latitude' => 41.0082, 'longitude' => 28.9784]);

        // ── ISRAEL ───────────────────────────────────────────────────────────
        $il = Country::firstOrCreate(['code' => 'IL'], [
            'name' => 'Israel', 'name_es' => 'Israel',
            'slug' => 'israel', 'flag_emoji' => '🇮🇱',
            'latitude' => 31.0461, 'longitude' => 34.8516, 'default_zoom' => 7,
        ]);
        $tel_aviv_reg = Region::firstOrCreate(['country_id' => $il->id, 'code' => 'TA'], [
            'name' => 'Tel Aviv District', 'name_es' => 'Distrito de Tel Aviv',
            'slug' => 'tel-aviv-district', 'latitude' => 32.0853, 'longitude' => 34.7818,
        ]);
        City::firstOrCreate(['slug' => 'tel-aviv'], ['region_id' => $tel_aviv_reg->id, 'name' => 'Tel Aviv', 'latitude' => 32.0853, 'longitude' => 34.7818]);

        $this->command->info('WorldGeographySeeder: done — countries, regions, and cities created.');
    }
}

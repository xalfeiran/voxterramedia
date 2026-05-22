<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GeographySeeder extends Seeder
{
    public function run(): void
    {
        // ── CANADA ──────────────────────────────────────────────────────────
        $ca = Country::firstOrCreate(['code' => 'CA'], [
            'name' => 'Canada', 'name_es' => 'Canadá',
            'slug' => 'canada', 'flag_emoji' => '🇨🇦',
            'latitude' => 56.1304, 'longitude' => -106.3468, 'default_zoom' => 4,
        ]);

        $ontario = Region::create([
            'country_id' => $ca->id, 'code' => 'ON',
            'name' => 'Ontario', 'name_es' => 'Ontario',
            'slug' => 'ontario', 'latitude' => 51.2538, 'longitude' => -85.3232,
        ]);
        $quebec = Region::create([
            'country_id' => $ca->id, 'code' => 'QC',
            'name' => 'Quebec', 'name_es' => 'Quebec',
            'slug' => 'quebec', 'latitude' => 52.9399, 'longitude' => -73.5491,
        ]);
        $bc = Region::create([
            'country_id' => $ca->id, 'code' => 'BC',
            'name' => 'British Columbia', 'name_es' => 'Columbia Británica',
            'slug' => 'british-columbia', 'latitude' => 53.7267, 'longitude' => -127.6476,
        ]);
        $alberta = Region::create([
            'country_id' => $ca->id, 'code' => 'AB',
            'name' => 'Alberta', 'name_es' => 'Alberta',
            'slug' => 'alberta', 'latitude' => 53.9333, 'longitude' => -116.5765,
        ]);
        $manitoba = Region::create([
            'country_id' => $ca->id, 'code' => 'MB',
            'name' => 'Manitoba', 'name_es' => 'Manitoba',
            'slug' => 'manitoba', 'latitude' => 53.7609, 'longitude' => -98.8139,
        ]);
        $novascotia = Region::create([
            'country_id' => $ca->id, 'code' => 'NS',
            'name' => 'Nova Scotia', 'name_es' => 'Nueva Escocia',
            'slug' => 'nova-scotia', 'latitude' => 44.6820, 'longitude' => -63.7443,
        ]);

        $toronto  = City::create(['region_id' => $ontario->id,  'name' => 'Toronto',   'slug' => 'toronto',   'latitude' => 43.6532, 'longitude' => -79.3832]);
        $ottawa   = City::create(['region_id' => $ontario->id,  'name' => 'Ottawa',    'slug' => 'ottawa',    'latitude' => 45.4215, 'longitude' => -75.6972]);
        $montreal = City::create(['region_id' => $quebec->id,   'name' => 'Montreal',  'slug' => 'montreal',  'latitude' => 45.5017, 'longitude' => -73.5673]);
        $vancouver= City::create(['region_id' => $bc->id,       'name' => 'Vancouver', 'slug' => 'vancouver', 'latitude' => 49.2827, 'longitude' => -123.1207]);
        $calgary  = City::create(['region_id' => $alberta->id,  'name' => 'Calgary',   'slug' => 'calgary',   'latitude' => 51.0447, 'longitude' => -114.0719]);
        $edmonton = City::create(['region_id' => $alberta->id,  'name' => 'Edmonton',  'slug' => 'edmonton',  'latitude' => 53.5461, 'longitude' => -113.4938]);
        $winnipeg = City::create(['region_id' => $manitoba->id, 'name' => 'Winnipeg',  'slug' => 'winnipeg',  'latitude' => 49.8951, 'longitude' => -97.1384]);
        $halifax  = City::create(['region_id' => $novascotia->id,'name'=> 'Halifax',   'slug' => 'halifax',   'latitude' => 44.6488, 'longitude' => -63.5752]);

        // ── USA ─────────────────────────────────────────────────────────────
        $us = Country::firstOrCreate(['code' => 'US'], [
            'name' => 'United States', 'name_es' => 'Estados Unidos',
            'slug' => 'united-states', 'flag_emoji' => '🇺🇸',
            'latitude' => 37.0902, 'longitude' => -95.7129, 'default_zoom' => 4,
        ]);

        $regions_us = [];
        $us_regions_data = [
            ['NY', 'New York',         'Nueva York',       'new-york',          40.7128,  -74.0060],
            ['DC', 'District of Columbia','D.C.',           'd-c',               38.9072,  -77.0369],
            ['VA', 'Virginia',          'Virginia',         'virginia',          37.4316,  -78.6569],
            ['CA', 'California',        'California',       'california',        36.7783, -119.4179],
            ['IL', 'Illinois',          'Illinois',         'illinois',          40.6331,  -89.3985],
            ['MA', 'Massachusetts',     'Massachusetts',    'massachusetts',     42.4072,  -71.3824],
            ['PA', 'Pennsylvania',      'Pensilvania',      'pennsylvania',      41.2033,  -77.1945],
            ['GA', 'Georgia',           'Georgia',          'georgia',           32.1656,  -82.9001],
            ['FL', 'Florida',           'Florida',          'florida',           27.6648,  -81.5158],
            ['TX', 'Texas',             'Texas',            'texas',             31.9686,  -99.9018],
            ['WA', 'Washington',        'Washington',       'washington',        47.7511, -120.7401],
            ['CO', 'Colorado',          'Colorado',         'colorado',          39.5501, -105.7821],
            ['AZ', 'Arizona',           'Arizona',          'arizona',           34.0489, -111.0937],
            ['MI', 'Michigan',          'Michigan',         'michigan',          44.3148,  -85.6024],
            ['OH', 'Ohio',              'Ohio',             'ohio',              40.4173,  -82.9071],
            ['MN', 'Minnesota',         'Minnesota',        'minnesota',         46.7296,  -94.6859],
            ['OR', 'Oregon',            'Oregón',           'oregon',            43.8041, -120.5542],
            ['HI', 'Hawaii',            'Hawái',            'hawaii',            20.7967, -156.3319],
            ['AK', 'Alaska',            'Alaska',           'alaska',            64.2008, -153.4937],
        ];

        foreach ($us_regions_data as [$code, $name, $name_es, $slug, $lat, $lon]) {
            $regions_us[$code] = Region::create([
                'country_id' => $us->id, 'code' => $code,
                'name' => $name, 'name_es' => $name_es,
                'slug' => $slug, 'latitude' => $lat, 'longitude' => $lon,
            ]);
        }

        $us_cities = [
            'new-york'      => City::create(['region_id' => $regions_us['NY']->id, 'name' => 'New York City',   'slug' => 'new-york',       'latitude' => 40.7128, 'longitude' => -74.0060]),
            'washington-dc' => City::create(['region_id' => $regions_us['DC']->id, 'name' => 'Washington D.C.', 'slug' => 'washington-dc',  'latitude' => 38.9072, 'longitude' => -77.0369]),
            'mclean'        => City::create(['region_id' => $regions_us['VA']->id, 'name' => 'McLean',          'slug' => 'mclean',          'latitude' => 38.9339, 'longitude' => -77.1773]),
            'los-angeles'   => City::create(['region_id' => $regions_us['CA']->id, 'name' => 'Los Angeles',     'slug' => 'los-angeles',    'latitude' => 34.0522, 'longitude' => -118.2437]),
            'san-francisco' => City::create(['region_id' => $regions_us['CA']->id, 'name' => 'San Francisco',   'slug' => 'san-francisco',  'latitude' => 37.7749, 'longitude' => -122.4194]),
            'chicago'       => City::create(['region_id' => $regions_us['IL']->id, 'name' => 'Chicago',         'slug' => 'chicago',        'latitude' => 41.8781, 'longitude' => -87.6298]),
            'boston'        => City::create(['region_id' => $regions_us['MA']->id, 'name' => 'Boston',          'slug' => 'boston',         'latitude' => 42.3601, 'longitude' => -71.0589]),
            'philadelphia'  => City::create(['region_id' => $regions_us['PA']->id, 'name' => 'Philadelphia',    'slug' => 'philadelphia',   'latitude' => 39.9526, 'longitude' => -75.1652]),
            'atlanta'       => City::create(['region_id' => $regions_us['GA']->id, 'name' => 'Atlanta',         'slug' => 'atlanta',        'latitude' => 33.7490, 'longitude' => -84.3880]),
            'miami'         => City::create(['region_id' => $regions_us['FL']->id, 'name' => 'Miami',           'slug' => 'miami',          'latitude' => 25.7617, 'longitude' => -80.1918]),
            'tampa'         => City::create(['region_id' => $regions_us['FL']->id, 'name' => 'Tampa',           'slug' => 'tampa',          'latitude' => 27.9506, 'longitude' => -82.4572]),
            'houston'       => City::create(['region_id' => $regions_us['TX']->id, 'name' => 'Houston',         'slug' => 'houston',        'latitude' => 29.7604, 'longitude' => -95.3698]),
            'dallas'        => City::create(['region_id' => $regions_us['TX']->id, 'name' => 'Dallas',          'slug' => 'dallas',         'latitude' => 32.7767, 'longitude' => -96.7970]),
            'austin'        => City::create(['region_id' => $regions_us['TX']->id, 'name' => 'Austin',          'slug' => 'austin',         'latitude' => 30.2672, 'longitude' => -97.7431]),
            'seattle'       => City::create(['region_id' => $regions_us['WA']->id, 'name' => 'Seattle',         'slug' => 'seattle',        'latitude' => 47.6062, 'longitude' => -122.3321]),
            'denver'        => City::create(['region_id' => $regions_us['CO']->id, 'name' => 'Denver',          'slug' => 'denver',         'latitude' => 39.7392, 'longitude' => -104.9903]),
            'phoenix'       => City::create(['region_id' => $regions_us['AZ']->id, 'name' => 'Phoenix',         'slug' => 'phoenix',        'latitude' => 33.4484, 'longitude' => -112.0740]),
            'detroit'       => City::create(['region_id' => $regions_us['MI']->id, 'name' => 'Detroit',         'slug' => 'detroit',        'latitude' => 42.3314, 'longitude' => -83.0458]),
            'cleveland'     => City::create(['region_id' => $regions_us['OH']->id, 'name' => 'Cleveland',       'slug' => 'cleveland',      'latitude' => 41.4993, 'longitude' => -81.6944]),
            'minneapolis'   => City::create(['region_id' => $regions_us['MN']->id, 'name' => 'Minneapolis',     'slug' => 'minneapolis',    'latitude' => 44.9778, 'longitude' => -93.2650]),
            'portland'      => City::create(['region_id' => $regions_us['OR']->id, 'name' => 'Portland',        'slug' => 'portland',       'latitude' => 45.5152, 'longitude' => -122.6784]),
            'honolulu'      => City::create(['region_id' => $regions_us['HI']->id, 'name' => 'Honolulu',        'slug' => 'honolulu',       'latitude' => 21.3099, 'longitude' => -157.8581]),
            'anchorage'     => City::create(['region_id' => $regions_us['AK']->id, 'name' => 'Anchorage',       'slug' => 'anchorage',      'latitude' => 61.2181, 'longitude' => -149.9003]),
        ];

        // ── MEXICO ───────────────────────────────────────────────────────────
        $mx = Country::firstOrCreate(['code' => 'MX'], [
            'name' => 'Mexico', 'name_es' => 'México',
            'slug' => 'mexico', 'flag_emoji' => '🇲🇽',
            'latitude' => 23.6345, 'longitude' => -102.5528, 'default_zoom' => 5,
        ]);

        $mx_regions_data = [
            ['CDMX', 'Mexico City',      'Ciudad de México',   'ciudad-de-mexico',   19.4326,  -99.1332],
            ['NL',   'Nuevo León',        'Nuevo León',         'nuevo-leon',         25.5922, -100.1119],
            ['JAL',  'Jalisco',           'Jalisco',            'jalisco',            20.6597, -103.3496],
            ['YUC',  'Yucatán',           'Yucatán',            'yucatan',            20.9674,  -89.5926],
            ['QROO', 'Quintana Roo',      'Quintana Roo',       'quintana-roo',       19.1817,  -88.4791],
            ['SON',  'Sonora',            'Sonora',             'sonora',             29.0729, -110.9559],
            ['CHIH', 'Chihuahua',         'Chihuahua',          'chihuahua',          31.6904, -106.4245],
            ['BC',   'Baja California',   'Baja California',    'baja-california',    30.8406, -115.2838],
            ['PUE',  'Puebla',            'Puebla',             'puebla',             19.0414,  -98.2063],
            ['VER',  'Veracruz',          'Veracruz',           'veracruz',           19.1738,  -96.1342],
            ['SIN',  'Sinaloa',           'Sinaloa',            'sinaloa',            25.1721, -107.4795],
            ['QRO',  'Querétaro',         'Querétaro',          'queretaro',          20.5888, -100.3899],
            ['OAX',  'Oaxaca',            'Oaxaca',             'oaxaca',             17.0732,  -96.7266],
        ];

        $regions_mx = [];
        foreach ($mx_regions_data as [$code, $name, $name_es, $slug, $lat, $lon]) {
            $regions_mx[$code] = Region::create([
                'country_id' => $mx->id, 'code' => $code,
                'name' => $name, 'name_es' => $name_es,
                'slug' => $slug, 'latitude' => $lat, 'longitude' => $lon,
            ]);
        }

        $mx_cities = [
            'cdmx'        => City::create(['region_id' => $regions_mx['CDMX']->id, 'name' => 'Ciudad de México', 'slug' => 'ciudad-de-mexico', 'latitude' => 19.4326, 'longitude' => -99.1332]),
            'monterrey'   => City::create(['region_id' => $regions_mx['NL']->id,   'name' => 'Monterrey',        'slug' => 'monterrey',         'latitude' => 25.6866, 'longitude' => -100.3161]),
            'guadalajara' => City::create(['region_id' => $regions_mx['JAL']->id,  'name' => 'Guadalajara',      'slug' => 'guadalajara',       'latitude' => 20.6597, 'longitude' => -103.3496]),
            'merida'      => City::create(['region_id' => $regions_mx['YUC']->id,  'name' => 'Mérida',           'slug' => 'merida',            'latitude' => 20.9674, 'longitude' => -89.5926]),
            'cancun'      => City::create(['region_id' => $regions_mx['QROO']->id, 'name' => 'Cancún',           'slug' => 'cancun',            'latitude' => 21.1619, 'longitude' => -86.8515]),
            'hermosillo'  => City::create(['region_id' => $regions_mx['SON']->id,  'name' => 'Hermosillo',       'slug' => 'hermosillo',        'latitude' => 29.0729, 'longitude' => -110.9559]),
            'juarez'      => City::create(['region_id' => $regions_mx['CHIH']->id, 'name' => 'Ciudad Juárez',    'slug' => 'ciudad-juarez',     'latitude' => 31.6904, 'longitude' => -106.4245]),
            'tijuana'     => City::create(['region_id' => $regions_mx['BC']->id,   'name' => 'Tijuana',          'slug' => 'tijuana',           'latitude' => 32.5149, 'longitude' => -117.0382]),
            'puebla'      => City::create(['region_id' => $regions_mx['PUE']->id,  'name' => 'Puebla',           'slug' => 'puebla',            'latitude' => 19.0414, 'longitude' => -98.2063]),
            'veracruz'    => City::create(['region_id' => $regions_mx['VER']->id,  'name' => 'Veracruz',         'slug' => 'veracruz',          'latitude' => 19.1738, 'longitude' => -96.1342]),
            'culiacan'    => City::create(['region_id' => $regions_mx['SIN']->id,  'name' => 'Culiacán',         'slug' => 'culiacan',          'latitude' => 24.8091, 'longitude' => -107.3940]),
            'queretaro'   => City::create(['region_id' => $regions_mx['QRO']->id,  'name' => 'Querétaro',        'slug' => 'queretaro',         'latitude' => 20.5888, 'longitude' => -100.3899]),
            'oaxaca'      => City::create(['region_id' => $regions_mx['OAX']->id,  'name' => 'Oaxaca',           'slug' => 'oaxaca',            'latitude' => 17.0732, 'longitude' => -96.7266]),
        ];

    }
}

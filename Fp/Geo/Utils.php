<?php

namespace Fp\Geo;

class Utils {

    public static $diametre_terre = 12756200;

    public static function google_geolocate($adresse, $ville = null, $pays = 'france') {
        $location = ( $adresse ) ? $location = $adresse : '';
        $location = ( $ville ) ? $location.=",$ville" : $location;
        $location = ( $pays ) ? $location .=",$pays" : $location;
        $wsurl = 'http://maps.google.com/maps/geo?q=' . urlencode($location) . '&output=csv';


        $contextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );
        
        $data = explode(',', file_get_contents($wsurl, false, stream_context_create($contextOptions)));

        if ((int) $data[0]) {
            return array(floatval($data[2]), floatval($data[3]));
        }
        return null;
    }

    /**
     * @param float  $latitude en degre
     * @param float  $longitude en degre
     * @param int 	 $distance_max en metre
     * @param string $as nom de la colonne distance calculée
     * @param string $row_longitude nom de la colonne longitude
     * @param string $row_latitude  nom de la colonne latitude
     * @return string
     */
    public static function queryDistance($latitude, $longitude, $distance_max, $as = 'distance', $row_longitude = 'longitude', $row_latitude = 'latitude') {
        $query = array();
        $diametre_terre = self::$diametre_terre;
        $coef_rad = str_replace(',', '.', (string) (M_PI / 180));
        $lat_cos_radian = str_replace(',', '.', (string) cos(self::degTorad($latitude)));
        $latitude = str_replace(',', '.', (string) $latitude);
        $longitude = str_replace(',', '.', (string) $longitude);

        $r = "ROUND($diametre_terre * 
				ASIN(
					SQRT(
						POWER(
							SIN(
								($latitude - $row_latitude) * $coef_rad / 2), 2
							)
							+ $lat_cos_radian *  COS($row_latitude * $coef_rad) *  POWER(SIN(($longitude - $row_longitude) * $coef_rad / 2), 2)
						)
					),4
				) as $as";

        $query['select'] = $r;

        $p = self::getPositionRange($latitude, $longitude, $distance_max);
        $query['where'] = " ( $row_longitude between {$p['min_long']} and {$p['max_long']}
							AND 
							$row_latitude between {$p['min_lat']} and {$p['max_lat']} ) ";
        return $query;
    }

    /**
     * @param float $latitude  en degré
     * @param float $longitude en degré
     * @param int $distance  en mètre
     * @return retourne une zone de recherche
     */
    public static function getPositionRange($latitude = 47.353889465332, $longitude = -0.76416701078413, $distance = 1000) {
        $min_long = $longitude - self::longitudeRange($latitude, $distance);
        $max_long = $longitude + self::longitudeRange($latitude, $distance);
        $min_lat = $latitude - self::latitudeRange($distance);
        $max_lat = $latitude + self::latitudeRange($distance);

        $arr = array();
        $arr['max_lat'] = str_replace(',', '.', (string) $max_lat);
        $arr['min_lat'] = str_replace(',', '.', (string) $min_lat);
        $arr['max_long'] = str_replace(',', '.', (string) $max_long);
        $arr['min_long'] = str_replace(',', '.', (string) $min_long);
        $arr['ref_lat'] = str_replace(',', '.', (string) $latitude);
        $arr['ref_long'] = str_replace(',', '.', (string) $longitude);
        $arr['range'] = str_replace(',', '.', (string) $distance);
        return $arr;
    }

    /**
     * @param float $lat en degre
     * @param int $distance en metre
     * @return number retourne la corespondance approximative en degré de la distance
     */
    public static function longitudeRange($lat, $distance) {
        return str_replace(',', '.', (string) ($distance / (abs(cos(deg2rad($lat)) * 111) * 1000)));
    }

    /**
     * @param float $distance en metre 
     * @return number retourne la corespondance approximative en degré de la distance 
     */
    public static function latitudeRange($distance) {
        return str_replace(',', '.', (string) ($distance / (111 * 1000)));
    }

    public static function radToDeg($radian) {
        return $radian * (180 / M_PI);
    }

    public static function degToRad($degre) {
        return $degre * (M_PI / 180);
    }

}

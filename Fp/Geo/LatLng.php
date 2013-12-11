<?php 
namespace Fp\Geo;
class LatLng {
	public $latitude;
	public $longitude;
	public static $earth_radius = 12756200;
	
	public function __construct($latitude, $longitude) {		
		$coef_rad = (float) str_replace(',','.', (string)(M_PI/180));		
		$this->latitude  = (float) str_replace(',','.',(string)$latitude);
		$this->longitude = (float) str_replace(',','.',(string)$longitude);		
	}	
	
	// return la corespondance degré/distance
	public function longitudeRange($lat,$distance) {		
		return $distance/abs(cos(deg2rad($lat))*111);
	}
	
	public function latitudeRange($distance) {
		return ($distance/111);
	}
	
	// convertion radian degré
	public static function deg2rad($deg) {
		return $deg*(M_PI/180); 
	}
	
	public static function rad2deg($rad) {
		return $rad*(180/M_PI);
	}	
		
	/*
	 * @todo la formule parait douteuse... à tester
	 */
	public function getDistance(GeoLatLng $GeoLatLng) {
        $latitudeRad   = self::deg2rad($this->latitude);        
        $longitudeRad  = self::deg2rad($this->longitude);   
        
        $objLatitudeRad   = self::deg2rad($this->latitude);        
        $objLongitudeRad  = self::deg2rad($this->longitude);  
             
        return acos((sin($latitudeRad) * sin($objLatitudeRad)) + (cos($latitudeRad) * cos($objLatitudeRad) * cos($longitudeRad - $objLongitudeRad))) * self::earth_radius;
    }
    //http://en.wikipedia.org/wiki/ISO_6709
    public function __toString() {
    	return $this->latitude.' '.$this->longitude;
    }
}


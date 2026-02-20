<?php
/**
 * Bulk Orders Imorting and uploading
 *
 * @author    Krupaludev <krupaludev@icloud.com>
 * @version   1.0.0
 */


/**
 * Class TRSUnitConvertor
 */
class TRSUnitConvertor
{
    private $value = null;

    private $baseUnit = false;

    private $units = [];

    function defineUnits()
    {
        $this->units = [
            // Units Of Length
            "m"   => ["base" => "m", "conversion" => 1],                                             // meter - base unit for distance
            "km"  => ["base" => "m", "conversion" => 1000],                                         // kilometer
            "dm"  => ["base" => "m", "conversion" => 0.1],                                          // decimeter
            "cm"  => ["base" => "m", "conversion" => 0.01],                                         // centimeter
            "mm"  => ["base" => "m", "conversion" => 0.001],                                        // milimeter
            "μm"  => ["base" => "m", "conversion" => 0.000001],                                     // micrometer
            "nm"  => ["base" => "m", "conversion" => 0.000000001],                                  // nanometer
            "pm"  => ["base" => "m", "conversion" => 0.000000000001],                               // picometer
            "in"  => ["base" => "m", "conversion" => 0.0254],                                       // inch
            "ft"  => ["base" => "m", "conversion" => 0.3048],                                       // foot
            "yd"  => ["base" => "m", "conversion" => 0.9144],                                       // yard
            "mi"  => ["base" => "m", "conversion" => 1609.3440],                                    // mile
            "h"   => ["base" => "m", "conversion" => 0.1016],                                        // hand

            // Units Of Area
            "m2"  => ["base" => "m2", "conversion" => 1],                                           // meter square - base unit for area
            "km2" => ["base" => "m2", "conversion" => 1000000],                                    // kilometer square
            "cm2" => ["base" => "m2", "conversion" => 0.0001],                                     // centimeter square
            "mm2" => ["base" => "m2", "conversion" => 0.000001],                                   // milimeter square
            "ft2" => ["base" => "m2", "conversion" => 0.092903],                                   // foot square
            "mi2" => ["base" => "m2", "conversion" => 2589988.11],                                 // mile square
            "ac"  => ["base" => "m2", "conversion" => 4046.86],                                     // acre
            "ha"  => ["base" => "m2", "conversion" => 10000],                                       // hectare

            // Units Of Volume
            "l"   => ["base" => "l", "conversion" => 1],                                             // litre - base unit for volume
            "cl"  => ["base" => "l", "conversion" => 0.010000],                                     // centilitre
            "ml"  => ["base" => "l", "conversion" => 0.001000],                                     // mililitre
            "pt"  => ["base" => "l", "conversion" => 0.568261],                                     // pint
            "gal" => ["base" => "l", "conversion" => 4.404884],                                    // gallon
            "m3"  => ["base" => "l", "conversion" => 1000],                                         // meter
            "km3" => ["base" => "l", "conversion" => 1000000000000],                               // kilometer
            "dm3" => ["base" => "l", "conversion" => 1],                                           // decimeter
            "cm3" => ["base" => "l", "conversion" => 0.001000],                                    // centimeter
            "mm3" => ["base" => "l", "conversion" => 0.000001],                                    // milimeter
            "μm3" => ["base" => "l", "conversion" => 0.000000000000001],                           // micrometer
            "nm3" => ["base" => "l", "conversion" => 0.000000000000000000000001],                  // nanometer
            "pm3" => ["base" => "l", "conversion" => 0.000000000000000000000000000000001],         // picometer
            "in3" => ["base" => "l", "conversion" => 0.016387],                                    // inch
            "ft3" => ["base" => "l", "conversion" => 28.316846],                                   // foot
            "yd3" => ["base" => "l", "conversion" => 764.554858],                                  // yard
            "mi3" => ["base" => "l", "conversion" => 4168181825400],                               // mile
            "h3"  => ["base" => "l", "conversion" => 1.048770],                                     // hand

            // Units Of Weight
            "kg"  => ["base" => "kg", "conversion" => 1],                                           // kilogram - base unit for weight
            "g"   => ["base" => "kg", "conversion" => 0.001],                                        // gram
            "mg"  => ["base" => "kg", "conversion" => 0.000001],                                    // miligram
            "N"   => ["base" => "kg", "conversion" => 9.80665002863885],                             // Newton (based on earth gravity)
            "st"  => ["base" => "kg", "conversion" => 6.35029],                                     // stone
            "lb"  => ["base" => "kg", "conversion" => 0.453592],                                    // pound
            "oz"  => ["base" => "kg", "conversion" => 0.0283495],                                   // ounce
            "t"   => ["base" => "kg", "conversion" => 1000],                                         // metric tonne
            "ukt" => ["base" => "kg", "conversion" => 1016.047],                                   // UK Long Ton
            "ust" => ["base" => "kg", "conversion" => 907.1847],                                   // US short Ton
        ];
    }

    function __construct($value, $unit)
    {
        // create units array
        $this->defineUnits();

        // unit optional
        if (!is_null($value)) {

            // set from unit
            $this->from($value, $unit);
        }
    }

    public function from($value, $unit)
    {
        // check if value has been set
        if (is_null($value)) {
            throw new Exception("Value Not Set");
        }

        if ($unit) {
            // check that unit exists
            if (array_key_exists($unit, $this->units)) {
                $unitLookup = $this->units[$unit];

                // convert unit to base unit for this unit type
                $this->baseUnit = $unitLookup["base"];
                $this->value = $this->convertToBase($value, $unitLookup);
            } else {
                throw new Exception("Unit Does Not Exist");
            }
        } else {
            $this->value = $value;
        }
    }

    public function to($unit, $decimals = null, $round = true)
    {
        // check if from value is set
        if (is_null($this->value)) {
            throw new Exception("From Value Not Set");
        }

        // check if to unit is set
        if (!$unit) {
            throw new Exception("Unit Not Set");
        }

        // if unit is array, itterate through each unit
        if (is_array($unit)) {
            return $this->toMany($unit, $decimals, $round);
        } else {
            // check unit symbol exists
            if (array_key_exists($unit, $this->units)) {
                $unitLookup = $this->units[$unit];

                // if from unit not provided, asume base unit of to unit type
                if ($this->baseUnit) {
                    if ($unitLookup["base"] != $this->baseUnit) {
                        throw new Exception("Cannot Convert Between Units of Different Types");
                    }
                } else {
                    $this->baseUnit = $unitLookup["base"];
                }

                // calculate converted value
                if (is_callable($unitLookup["conversion"])) {
                    // if unit has a conversion function, run value through it
                    $result = $unitLookup["conversion"]($this->value, true);
                } else {
                    $result = $this->value / $unitLookup["conversion"];
                }

                // result precision and rounding
                if (!is_null($decimals)) {
                    if ($round) {
                        // round to the specifidd number of decimals
                        $result = round($result, $decimals);
                    } else {
                        // truncate to the nearest number of decimals
                        $shifter = $decimals ? pow(10, $decimals) : 1;
                        $result = floor($result * $shifter) / $shifter;
                    }
                }

                return $result;
            } else {
                throw new Exception("Unit Does Not Exist");
            }
        }
    }

    private function toMany($unitList = [], $decimals = null, $round = true)
    {
        $resultList = [];

        foreach ($unitList as $key) {
            // convert units for each element in the array
            $resultList[$key] = $this->to($key, $decimals, $round);
        }

        return $resultList;
    }

    private function convertToBase($value, $unitArray)
    {
        if (is_callable($unitArray["conversion"])) {
            // if unit has a conversion function, run value through it
            return $unitArray["conversion"]($value, false);
        } else {
            return $value * $unitArray["conversion"];
        }
    }
}

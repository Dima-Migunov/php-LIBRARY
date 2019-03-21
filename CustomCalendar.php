<?php

class CustomCalendar{
  private static
    $monthsInYear       = 13,
    $daysOfFirstMonth   = 22,
    $daysOfSecondMonth  = 21,
    $periodOfLeapYear   = 5,
    $week = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
    $pointYear          = 1990,
    $firstDayOfPointYear  = 'Monday',
    $deltaDaysOfLeapYears = -1; // in days

  public static function getDay( string $date ){
    list( $days, $months, $year ) = explode( '.', $date );
    
    $days         = intval( $days );
    $deltaYears   = intval( $year ) - self::$pointYear;
    $leapYears    = floor( abs( $deltaYears / self::$periodOfLeapYear ) );
    $monthAmount  = $deltaYears * self::$monthsInYear + intval( $months ) - 1;
    
    if( $monthAmount < 0 ){
      return 'Date in the Past ;)';
    }

    $fmonths      = floor( abs( $monthAmount ) / 2 );
    $smonths      = 0;
    
    if( $fmonths > 0 ){
      $smonths  = abs( $monthAmount ) - $fmonths;
    }
    
    $days += $fmonths * self::$daysOfFirstMonth
            + $smonths * self::$daysOfSecondMonth
            + $leapYears * self::$deltaDaysOfLeapYears;

    $weekday  = $days % count( self::$week ) - 1
              + array_search( self::$firstDayOfPointYear, self::$week);
    
    return self::$week[ $weekday ];
  }
  
  public static function daysInYear(){
    $fmonths  = ceil( self::$monthsInYear / 2 );
    $smonths  = self::$monthsInYear - $fmonths;
    
    return $fmonths * self::$daysOfFirstMonth + $smonths * self::$daysOfSecondMonth;
  }
}

print_r( CustomCalendar::getDay( '17.11.2013' ) );


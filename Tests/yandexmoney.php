<?php
function parseSMS($response)
{
    $matches    = [];
    $regex      = '#(\d{4})\D+|(\d+(?:[.,]\d{1,2})?)\D+\.|(41001\d{9,10})#u';
    $result     = preg_match_all($regex, $response, $matches);

    if(!$result) {
        return null;
    }

    return [
        'password'  => $matches[1][0],
        'amount'    => str_replace(',', '.', $matches[2][1]),
        'wallet'    => $matches[3][2],
    ];
}

<?php

/**
 * CLASH OF CLANS API, proof of concept
 * Example of how to retrieve and cache data from the official Clash of Clans API 
 *
 * May the Great Dragoness be with you!
 * In-Game-Support: Owen Meany [at] Drachenhorde, #9GG2UY08 :)
 *
 * --
 *
 * Copyright (c) 2016 Owen Meany <http://www.clansweb.com/me/rekomat>, 
 * Drachenhorde, http://drachenhorde.clans.de
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this 
 * software and associated documentation files (the "Software"), to deal in the Software 
 * without restriction, including without limitation the rights to use, copy, modify, 
 * merge, publish, distribute, sublicense, and/or sell copies of the Software, and to 
 * permit persons to whom the Software is furnished to do so, subject to the following 
 * conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all copies 
 * or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A 
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT 
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF 
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR 
 * THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 */

require_once('api_token.php');

define('CACHE_DIR', 'cache');

$clans = [
  [
    'name' => 'Drachenhorde',
    'hash' => '#9GG2UY08'
  ],
  [
    'name' => 'Drachenhorde2.0',
    'hash' => '#P9G9LYP'
  ],
  [
    'name' => 'Drachenhorde3.0',
    'hash' => '#YJ8PCU8U'
  ],
  [
    'name' => 'Drachennest',
    'hash' => '#89GLGGPU'
  ],
];

/**
 * load_data
 * gets live data via api
 *
 * @param str request_url  
 * @return str data
 */ 
function load_data($request_url) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . ACCESS_TOKEN));
  curl_setopt($ch, CURLOPT_URL, $request_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $subs_return = curl_exec($ch);
  curl_close($ch);
  return $subs_return;
}

/**
 * get_data
 * returns the data read from api
 * if data is not cached yet or if cached data is expired (older than 1h)
 * fresh data is requested and cached. 
 * 
 * @param str file
 * @param str request_url
 * @return str data
 */
function get_data($file, $request_url) {
  if (!is_dir(CACHE_DIR)) { mkdir(CACHE_DIR); }
  $current_time = time();
  $expire_time = $current_time - (1 * 60 * 60);
  $file_time = file_exists($file) ? filemtime($file) : 0;
  if ($file_time < $expire_time) {
    $content = load_data($request_url);
    file_put_contents($file, $content);
  } else {
    $content = file_get_contents($file);
  }
  return $content;
}


//////////////////////////////////////////////////////////////////////////////////////////
// count donations for given clans
//////////////////////////////////////////////////////////////////////////////////////////

// loop throug all clans 
$out = '';
foreach ($clans as &$clan) {
  // retrieve and decode data
  $clan_data = get_data(
    CACHE_DIR .'/members_'. substr($clan['hash'], 1) .'.json', 
    'https://api.clashofclans.com/v1/clans/'. urlencode($clan['hash']) .'/members'
  );
  $clan_data = json_decode($clan_data, true);
  // count donations 
  $donationsTotal = 0;
  foreach($clan_data['items'] as $member) {
    $donationsTotal += $member['donations'];
  }
  $out .= ($clan['name'] . ': '. $donationsTotal ."\n");
}
echo '<pre>'. $out .'</pre>';

?>

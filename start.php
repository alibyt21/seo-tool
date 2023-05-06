<?php
header("Access-Control-Allow-Origin: *");
error_reporting(0);
ini_set('max_execution_time', '0');
define("DEEP_LEVEL", 500);

function in_array_r($needle, $haystack, $strict = false)
{
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}

include_once 'vendor/autoload.php';

use Goutte\Client;



class Search
{
    protected static $startPos = 0;
    protected static $endPos = 0;
    protected static $i;
    protected static $data = array();
    protected static $count;
    protected static $score;
    public static $error;

    public function __construct($query)
    {
        self::$data[self::$startPos][0] = $query;
        self::$endPos++;
    }

    public function singleSearch()
    {
        self::$error = true;
        $client = new Client();
        self::$i = 0;
        $currentQuery = self::$data[self::$startPos][0];
        $crawler = $client->request('GET', "https://www.google.com/search?q={$currentQuery}&rlz=1C1RXQR_enIR944IR944&oq={$currentQuery}&aqs=chrome.0.69i59l2j69i57j0i271l2j69i60l3.600j0j7&sourceid=chrome&ie=UTF-8");
        self::$score = 0;
        /*get results url*/
        $crawler->filter('.egMi0 > a')->each(function ($node) {
            self::$error = false;
            self::$i++;
            $resultUrl = $node->attr('href');
            /* cleaning url */
            // sample $resultUrl = '/url?q=https://www.roboclick.co/Blog/Sign-In-Instagram&sa=U&ved=2ahUKEwjFwKPc7Mv5AhWq8rsIHQzUAJoQFnoECAUQAg&usg=AOvVaw05M5eizZJlbVUKm_Y1lBaO';
            $start = strpos($resultUrl, '=');
            $end = strpos($resultUrl, '&');
            $cleanUrl = substr($resultUrl, $start + 1, $end - $start - 1);
            self::$data[self::$startPos][self::$i] = $cleanUrl;

            /* get first query result count */
            if (is_null(self::$count) && self::$startPos >= 1) {
                foreach (self::$data[0] as $oneData) {
                    self::$count++;
                }
            }

            if (self::$startPos >= 1) {
                $mainQueryIndex = array_search(self::$data[self::$startPos][self::$i], self::$data[0]);

                if ($mainQueryIndex) {
                    $difference = self::$i - $mainQueryIndex;
                    $score = 10 - abs($difference);
                    $weightedScore = $score * ((20 - (($mainQueryIndex - 1) * 2.22222222)) / 10);
                    self::$score += $weightedScore;
                } else {
                    self::$score += 0;
                }
                self::$data[self::$startPos][20] = (string)(int)self::$score;
            }
        });

        self::$i = 0;
        self::$score = 0;

        $crawler->filter('div > div > div > div > div > a > div > span')->each(function ($node) {

            $next = $node->text();
            // echo "<pre>";
            // var_dump($next);
            // echo "<pre>";
            if (!is_null($next) && !in_array_r($next, self::$data)) {
                //echo '</br>HTHWETWJSDFSMFNXL@#@#%#@%@#!~</br></br>';
                self::$data[self::$endPos][0] = $next;
                self::$endPos++;
            } else {
                return;
            }
        });
    }

    public function search()
    {
        do {
            $this->singleSearch();
            self::$startPos++;
            usleep(rand(2000, 5000));
        } while (count(self::$data) < DEEP_LEVEL && count(self::$data) > 1 && !(self::$error));
    }

    public function getRelatedQueries()
    {
        return self::$data;
    }
}
$querySearch = $_GET['query'];
$test = new search($querySearch);
$test->search();


$serachResult = $test->getRelatedQueries();
// echo "<pre>";
// var_dump($serachResult);
// echo "</pre>";


/* with score */
foreach ($serachResult as $row) {
    if (is_string($row[20])) {
        echo ($row[0] . "," . $row[20]);
        echo "</br>";
    }
}



/*without scores*/
// if (!(search::$error)) {
//     $resultArr = [];
//     $resultCount = 0;
//     foreach ($serachResult as $row) {
//         if ($resultCount <= 10 && $resultCount > 0) {
//             array_push($resultArr, $row[0]);
//         }
//         $resultCount++;
//     }
// } else {
//     $resultArr = 'blocked';
// }
// echo (json_encode($resultArr));




/*
foreach ($test->getRelatedQueries() as $value) {
    if(str_contains($value,'وردپرس') || str_contains($value,'محتوا') || str_contains($value,'مدیریت') || str_contains($value,'سیستم') || str_contains($value,'سی ام اس') || str_contains($value,'cms')){
    }
    echo "$value <br>";

}
*/
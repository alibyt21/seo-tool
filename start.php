<?php
include_once 'vendor/autoload.php';
use Goutte\Client;



class Search{
    protected static $startPos = 0;
    protected static $endPos = 0;
    protected static $i;
    protected static $data = array();
    protected static $count;
    protected static $score;

    public function __construct($query){
        self::$data[self::$startPos][0] = $query;
        self::$endPos++;
    }

    public function singleSearch()
    {
        $client = new Client();
        self::$i = 0;
        $currentQuery = self::$data[self::$startPos][0];
        $crawler = $client->request('GET', "https://www.google.com/search?q={$currentQuery}");
        self::$score = 0;
        /*get results url*/
        $crawler->filter('.egMi0 > a')->each(function ($node){
            
            self::$i++;
            $resultUrl = $node->attr('href');
            /* cleaning url */
            // sample $resultUrl = '/url?q=https://www.roboclick.co/Blog/Sign-In-Instagram&sa=U&ved=2ahUKEwjFwKPc7Mv5AhWq8rsIHQzUAJoQFnoECAUQAg&usg=AOvVaw05M5eizZJlbVUKm_Y1lBaO';
            $start = strpos($resultUrl,'=');
            $end = strpos($resultUrl,'&');
            $cleanUrl = substr($resultUrl,$start + 1, $end - $start - 1);
            self::$data[self::$startPos][self::$i] = $cleanUrl;

            /* get first query result count */
            if(is_null(self::$count) && self::$startPos >= 1){
                foreach(self::$data[0] as $oneData){
                    self::$count++;
                }
            }

            if(self::$startPos >= 1){
                $mainQueryIndex = array_search(self::$data[self::$startPos][self::$i],self::$data[0]);
                if ($mainQueryIndex){
                    $difference = self::$i-$mainQueryIndex;
                    self::$score += 10 - abs($difference);
                    self::$data[self::$startPos][20] = self::$score;
                }
                
            }

        });
        self::$i = 0;
        self::$score = 0;

        $crawler->filter('div > div > div > div > div > a > div > span')->each(function ($node){
            
            $next = $node->text();
            // echo "<pre>";
            // var_dump($next);
            // echo "<pre>";
            if(!is_null($next) && !in_array($next,self::$data)){
                self::$data[self::$endPos][0] = $next;
                self::$endPos++;
            }else{
                return;
            }

        });
    }

    public function search()
    {
        while(self::$endPos-self::$startPos <= 100){
            $this->singleSearch();
            self::$startPos++;
            sleep(rand(0,2));
        }
    }

    public function getRelatedQueries()
    {
        return self::$data;
    }

}

$test = new search('ثبت نام اینستاگرام');
$test->search();


$resilt = $test->getRelatedQueries();
echo "<pre>";
var_dump($resilt);
echo "</pre>";

foreach($resilt as $row){
    echo "<pre>";
    var_dump($row[0]."          score:".$row[20]);
    echo "</pre>";
}

/*
foreach ($test->getRelatedQueries() as $value) {
    if(str_contains($value,'وردپرس') || str_contains($value,'محتوا') || str_contains($value,'مدیریت') || str_contains($value,'سیستم') || str_contains($value,'سی ام اس') || str_contains($value,'cms')){
    }
    echo "$value <br>";

}
*/
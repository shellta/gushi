<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\DomCrawler\Crawler;

class Spider
{
    /**
     * @var Spider
     */
    private static $instance;

    private $baseUrl;

    private $targetUrl;

    private $page;

    private $headers;

    private $suffix;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var Client
     */
    private $client;

    private $total;

    private function __construct()
    {
        $this->baseUrl = 'https://www.gushiwen.org/shiwen/default_';
        $this->page = 1;
        $this->suffix = '.aspx';

        $this->headers = [
            'upgrade-insecure-requests' => '1',
            'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'accept-encoding' => 'gzip, deflate, br',
            'accept-language' => 'zh-CN,zh;q=0.9',
            'cache-control' => 'max-age=0',
            'dnt' => '1',
            'referer' => 'https://www.gushiwen.org/shiwen/default_3A1A1.aspx',
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.62 Safari/537.36'
        ];

        $this->client = new Client([
            'headers' => $this->headers,
            'timeout' => 5.0,
            'verify' => false,
            'cookies' => true
        ]);
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function load()
    {
        $targets = [
            '3A1A' => '先秦',
            '3A2A' => '两汉',
            '3A3A' => '魏晋',
            '3A4A' => '南北朝',
            '3A5A' => '隋代',
            '3A6A' => '唐代',
            '3A7A' => '五代',
            '3A8A' => '宋代',
            '3A9A' => '金朝',
            '3A10A' => '元代',
            '3A11A' => '明代',
            '3A12A' => '清代',
        ];

        foreach ($targets as $key => $value) {

            $this->targetUrl = $key;
            $this->page = 1;
            $this->total = null;

            while (is_null($this->total) || $this->page <= $this->total) {
                $this->sendRequest()->parse();

                echo "当前: {$value}, 进度 {$this->page} / {$this->total}", "\n";

                $this->page++;
                usleep(1500000);
            }
        }
    }

    private function sendRequest()
    {
        $response = null;

        $uri = $this->baseUrl . $this->targetUrl . $this->page . $this->suffix;

        try {
            $response = $this->client->request('GET', $uri);
        } catch (GuzzleException $e) {
        }

        $this->response = $response;

        return $this;
    }

    private function parse()
    {
        $values = [];

        $html = $this->response->getBody()->getContents();
        $crawler = new Crawler($html);

        if (is_null($this->total)) {
            $this->total = (int)$crawler->filter('#sumPage')->first()->text();
        }

        $crawler->filter('.main3 .left .sons')
            ->reduce(function (Crawler $node, $i) use (&$values) {
                $value = [
                    'title'        => '',
                    'dynasty'      => '',
                    'author'       => '',
                    'content_text' => '',
                    'content_html' => '',
                    'tags'         => ''
                ];

                // 标题
                $titleNode = $node->filter('b');

                if ($titleNode->count() > 0) {
                    $value['title'] = $titleNode->first()->text();
                }

                // 作者
                $author = $node->filter('p.source a');

                if ($author->count() === 2) {
                    $value['dynasty'] = $author->first()->text();
                    $value['author'] = $author->last()->text();
                }

                // 内容
                $content = $node->filter('.contson');

                if ($content->count() > 0) {
                    $value['content_text'] = $content->first()->text();
                    $value['content_html'] = $content->first()->html();
                }

                // 标签
                $tags = $node->filter('.tag a');

                if ($tags->count() > 0) {
                    $tagStr = '';

                    foreach ($tags as $tag) {
                        $tagStr .= $tag->nodeValue . ',';
                    }

                    $value['tags'] = rtrim($tagStr, ',');
                }

                if ($value['title']) {
                    $values[] = $value;
                }
            });

        DB::insert($values);
    }
}

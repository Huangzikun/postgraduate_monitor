<?php


namespace App\Console;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use QL\QueryList;

class MonitorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '脚本';


    public function handle()
    {
        $urlConfig = [
            [
                'domain' => 'https://www.guet.edu.cn',
                'path' => '/yjszs/info/1038/1571.htm',
                'find' => '.contentstyle165561',
                'msg' => '桂电的通知信息变化了.',
            ],
            [
                'domain' => 'https://www.guet.edu.cn',
                'path' => '/yjszs/list.jsp?urltype=tree.TreeTempUrl&wbtreeid=1038',
                'find' => '.winstyle165557',
                'msg' => '桂电的通知信息变化了.'
            ],
            [
                'domain' => 'https://yjsy.nwnu.edu.cn',
                'path' => '/2713/list.htm',
                'find' => '#AjaxList',
                'msg' => '西北师范的通知信息变化了.'
            ],
            [
                'domain' => 'http://yjs.gzy.edu.cn',
                'path' => '/zsjy/zsxx.htm',
                'find' => '.winstyle191805',
                'msg' => '贵州中医药的通知信息变化了.'
            ],
            [
                'domain' => 'https://yjsy.glut.edu.cn',
                'path' => '/info/1189/4361.htm',
                'find' => '.v_news_content',
                'msg' => '桂林理工的通知信息变化了.'
            ],
            [
                'domain' => 'http://202.203.192.18',
                'path' => '/pub/yjsb/zs/zstz/index.htm',
                'find' => '.tab_list3',
                'msg' => '云南财经的通知信息变化了.'
            ],
            [
                'domain' => 'http://www.yz.gxnu.edu.cn',
                'path' => '/xxgg/list.htm',
                'find' => '.wp_article_list_table',
                'msg' => '广西师范的通知信息变化了.'
            ]
        ];

        $time = 0;
        while(true) {
            $time ++;
            $push = [];
            $diff = [];

            foreach ($urlConfig as $idx => $config) {
                $client = new Client([
                    'base_uri' => $config['domain'],
                ]);

                try {
                    $res = $client
                        ->get($config['path']);

                    if($res->getStatusCode() == 200) {
                        $res = $res->getBody()->getContents();

                    } else {
                        throw new \Exception('http code.');
                    }
                } catch (GuzzleException $e) {
                    dump($e->getCode(), $e->getMessage());
                    continue;
                }

                $text = QueryList::getInstance()->setHtml($res)->find($config['find'])->text();

                $fileName = storage_path($idx . ".txt");
                if (!file_exists($fileName)) {
                    $f = fopen($fileName, 'w+');
                    fclose($f);
                }

                $file = file_get_contents($fileName);
                if ($file != $text) {
                    $push[] = $config['msg'] . $config['domain'] . $config['path'];
                    $diff[$config['domain'] . $config['path']] = [
                        'file' => $file,
                        'text' => $text,
                    ];

                }
                file_put_contents($fileName, $text);
            }

            if (!empty($push)) {
                $this->mail($push);
                dump($push);


                $strDiff = [];
                foreach ($diff as $url => $info) {
                    $strDiff[] = $url . ": "
                        . $info['file']
                        . "================================="
                        . PHP_EOL
                        . $info['text'];
                }
                file_put_contents(storage_path('diff.txt'), $strDiff);
            }

            dump("success : {$time}");
            file_put_contents(storage_path('time.txt'), $time);

            sleep(60);
        }

    }

    public function mail(array $info)
    {
        Mail::raw(implode(PHP_EOL, $info), function ($message) {
            $message->to("820683890@qq.com")->subject('信息监控变化');
            $message->to("381560728@qq.com")->subject('信息监控变化');
        });
    }
}

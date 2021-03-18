<?php


namespace App\Http\Controllers;



use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use QL\QueryList;

class MonitorController extends Controller
{
    public function monitorWeb(Request $request)
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
        ];

        foreach ($urlConfig as $idx => $config) {

            $client = new Client([
                'base_uri' => $config['domain'],
            ]);

            try {
                $res = $client
                    ->get($config['path'])->getBody()->getContents();
            } catch (GuzzleException $e) {
            }

            $text = QueryList::getInstance()->setHtml($res)->find($config['find'])->text();

            $fileName = storage_path($idx . ".txt");
            if(!file_exists($fileName)) {
                $f = fopen($fileName, 'w+');
                fclose($f);
            }

            $file = file_get_contents($fileName);
            if($file != $text) {
                $push[] = $config['msg'] . $config['domain'] . $config['path'];
            }
            file_put_contents($fileName, $text);
        }

        if(!empty($push)) {
            $this->mail($push);
        }

        return "success";

    }

    public function mail(array $info)
    {
        Mail::raw(implode(PHP_EOL, $info), function ($message) {
            $message->to("820683890@qq.com")->subject('信息监控变化');
            $message->to("381560728@qq.com")->subject('信息监控变化');
        });
    }
}

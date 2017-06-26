<?php

/**
 * Copyright 2016 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

namespace LINE\LINEBot\EchoBot;

use AI\AI;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Exception\InvalidEventRequestException;
use LINE\LINEBot\Exception\InvalidSignatureException;

class Route
{
    public function register(\Slim\App $app)
    {
        $app->get('/test', function(){
            $ai = new AI();
        });
        $app->post("/callback", function (\Slim\Http\Request $req, \Slim\Http\Response $res) use($app) {
            $bot = $this->bot;
            $logger = $this->logger;

            /**
             * Signature.
             */
            /*$signature = $req->getHeader(HTTPHeader::LINE_SIGNATURE);
            if (empty($signature)) {
                return $res->withStatus(400, 'Bad Request');
            }*/
            $body = $req->getBody();
            /*$body = '{"events":[{"type":"message","replyToken":"5c32e7193d4e4ebf9f9326a656babeb6","source":{"userId":"U547ba62dc793c6557abbb42ab347f15f","type":"user"},"timestamp":1498463825764,"message":{"type":"text","id":"6296397218198","text":"q_anime ordinal scale"}}]}';*/
            file_put_contents("body.txt", $body);
            $body = json_decode($body, true);
            $ai = new AI();
            foreach ($body['events'] as $event) {
                if ($event['type'] === "message" && $event['message']['type'] === "text") {
                    var_dump($event);
                    $st = $ai->prepare($event['message']['text']);
                    if ($st->execute()) {
                        $reply = $st->fetch_reply();
                        if (is_array($reply)) {
                            $build = new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($reply[0], $reply[0]);   
                            $bot->pushMessage($event['source']['userId'], $build);
                            $build = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($reply[1]);   
                            $bot->pushMessage($event['source']['userId'], $build);
                        } else {
                            $build = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($reply);   
                            $bot->pushMessage($event['source']['userId'], $build);
                        }
                    } else {
                        $build = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("Mohon maaf saya belum mengerti \"{$event['message']['text']}\"");   
                        $bot->pushMessage($event['source']['userId'], $build);
                    }
                }
            }
            #$res->write($body);
            return $res;
        });
    }
}

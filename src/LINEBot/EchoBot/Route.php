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
        $app->get("/callback", function (\Slim\Http\Request $req, \Slim\Http\Response $res) use($app) {
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
            $body = '{"events":[{"type":"message","replyToken":"5c32e7193d4e4ebf9f9326a656babeb6","source":{"userId":"U547ba62dc793c6557abbb42ab347f15f","type":"user"},"timestamp":1498463825764,"message":{"type":"text","id":"6296397218198","text":"Halo"}}]}';

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
                            $build = new \LINE\LINEBot\MessageBuilder\TestMessageBuilder($reply[1]);   
                            $bot->pushMessage($event['source']['userId'], $build);
                        } else {
                            $build = new \LINE\LINEBot\MessageBuilder\TestMessageBuilder($reply);   
                            $bot->pushMessage($event['source']['userId'], $build);
                        }
                    } else {
                        $build = new \LINE\LINEBot\MessageBuilder\TestMessageBuilder("Mohon maaf saya belum mengerti \"{$event['message']['text']}\"");   
                        $bot->pushMessage($event['source']['userId'], $build);
                    }
                }
            }
            #$res->write($body);
            return $res;
        });










        $app->get('/callbackz', function (\Slim\Http\Request $req, \Slim\Http\Response $res) {
            
            /** 
             * @var \LINE\LINEBot $bot 
             */
            $bot = $this->bot;
            
            /** 
             * @var \Monolog\Logger $logger 
             */
            $logger = $this->logger;

            $signature = $req->getHeader(HTTPHeader::LINE_SIGNATURE);
            /*if (empty($signature)) {
                return $res->withStatus(400, 'Bad Request');
            }*/
            #$body = $req->getBody();
            file_put_contents("body.txt", $body);
            /*// Check request with signature and parse request
            $body = json_decode($body,true);
            $body = $body['events'];
            foreach ($body as $event) {
                if ($event['type']==="message" and $event['message']['type']==="text") {
                    $ai = new AI();
                    $st = $ai->prepare($event['message']['text']);
                    if ($st->execute()) {
                        $replyText = $st->fetch_reply();
                    } else {
                        $replyText = "Mohon maaf saya belum mengerti \"{$getText}\"";
                    }
                    file_put_contents("debug_reply.txt", json_encode($replyText, 128));
                  
                    if (is_array($replyText)) {
                        $imageMessageBuilder = (new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($replyText[0], $replyText[1]));
                        file_put_contents("event_debug_replyzz.txt", json_encode($event, 128));
                        $ss = $bot->pushMessage(/*$event['source']['userId']*//*"U547ba62dc793c6557abbb42ab347f15f", $imageMessageBuilder);
                        var_dump($ss);
                        $logger->info('Reply text: ' . $replyText[1]);
                        $resp = $bot->replyText($event['replyToken'], $replyText[1]);
                    } else {
                        $logger->info('Reply text: ' . $replyText);
                        $resp = $bot->replyText($event['replyToken'], $replyText);
                    }
                    $logger->info($resp->getHTTPStatus() . ': ' . $resp->getRawBody());
                } else {
                    continue;
                }
            }*/
          /*  
            try {
                $events = $bot->parseEventRequest($body, $signature[0]);
            } catch (InvalidSignatureException $e) {
                return $res->withStatus(400, 'Invalid signature');
            } catch (InvalidEventRequestException $e) {
                return $res->withStatus(400, "Invalid event request");
            }*/

           /* foreach ($events as $event) {
                if (!($event instanceof MessageEvent)) {
                    $logger->info('Non message event has come');
                    continue;
                }

                if (!($event instanceof TextMessage)) {
                    $logger->info('Non text message has come');
                    continue;
                }

                $getText = $event->getText();
                $ai = new AI();
                $st = $ai->prepare($getText);
                if ($st->execute()) {
                    $replyText = $st->fetch_reply();
                } else {
                    $replyText = "Mohon maaf saya belum mengerti \"{$getText}\"";
                }
                file_put_contents("debug_reply.txt", json_encode($replyText, 128));
                if (is_array($replyText)) {
                    $imageMessageBuilder = (new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($replyText[0], $replyText[0]));
                    file_put_contents("event_debug_replyzz.txt", json_encode($event, 128));
                    $bot->pushMessage($event['source']['userId'], $imageMessageBuilder);
                    $logger->info('Reply text: ' . $replyText[1]);
                    $resp = $bot->replyText($event->getReplyToken(), $replyText[1]);
                } else {
                    $logger->info('Reply text: ' . $replyText);
                    $resp = $bot->replyText($event->getReplyToken(), $replyText);
                }
                $logger->info($resp->getHTTPStatus() . ': ' . $resp->getRawBody());
            }*/

            $res->write('OK');
            return $res;
        });
        $app->post('/callbackp', function (\Slim\Http\Request $req, \Slim\Http\Response $res) {
            
            /** 
             * @var \LINE\LINEBot $bot 
             */
            $bot = $this->bot;
            
            /** 
             * @var \Monolog\Logger $logger 
             */
            $logger = $this->logger;

            $signature = $req->getHeader(HTTPHeader::LINE_SIGNATURE);
            if (empty($signature)) {
                return $res->withStatus(400, 'Bad Request');
            }

            // Check request with signature and parse request
            try {
                $events = $bot->parseEventRequest($req->getBody(), $signature[0]);
            } catch (InvalidSignatureException $e) {
                return $res->withStatus(400, 'Invalid signature');
            } catch (InvalidEventRequestException $e) {
                return $res->withStatus(400, "Invalid event request");
            }

            foreach ($events as $event) {
                if (!($event instanceof MessageEvent)) {
                    $logger->info('Non message event has come');
                    continue;
                }

                if (!($event instanceof TextMessage)) {
                    $logger->info('Non text message has come');
                    continue;
                }

                $getText = $event->getText();
                file_put_contents("test.txt", $getText);
                $ai = new AI();
                $st = $ai->prepare($getText);
                if ($st->execute()) {
                    $replyText = $st->fetch_reply();
                    $replyText = is_array($replyText) ? json_encode($replyText) : $replyText;
                } else {
                    $replyText = "Mohon maaf saya belum mengerti \"{$getText}\"";
                }
                file_put_contents("reply.txt", $replyText);
                $logger->info('Reply text: ' . $replyText);
                $resp = $bot->replyText($event->getReplyToken(), $replyText);
                $logger->info($resp->getHTTPStatus() . ': ' . $resp->getRawBody());
            }

            $res->write('OK');
            return $res;
        });
    }
}

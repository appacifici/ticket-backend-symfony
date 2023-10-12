<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TicketPurchaseTest extends WebTestCase
{
    public function testMissinFieldPurchase(): void
    {
        $client = self::createClient();
                
        $client->request(
            'POST',
            "http://79.55.63.147/ticket/purchase",
            [],
            [],
            [],
            $this->getPayloadMissinFieldPurchase()
        );        

        $response = $client->getResponse()->getContent();        

        self::assertEquals( $response, $this->getResponseMissinFieldPurchase() );        
    }

    private function getPayloadMissinFieldPurchase() {
        return '{
            "userId": 1,
            "puschase":[
               {
                   "eventId": 1,
                   "placeType": 2                   
               },
               {
                   "eventId": 1,
                   "sectorId": 1,
                   "placeType": 2,
                   "placeId": 2 
               },
               {
                   "eventId": 1,
                   "sectorId": 1,
                   "placeType": 2,
                   "placeId": 3    
               },       
               {
                   "eventId": 1,
                   "sectorId": 2,
                   "placeType": 1 
                },      
                {
                   "eventId": 2,
                   "sectorId": 3,
                   "placeType": 1 
                },      
                {
                   "eventId": 2,
                   "sectorId": 3,
                   "placeType": 1 
                }
           ]
        }';
    }

    private function getResponseMissinFieldPurchase() {
        return '{"success":false,"errors":[{"message":"Missin Field in Purchase element","code":3,"pushcases":[[{"code":6,"message":"Missin PlaceId element"},{"code":7,"message":"Missin SectorId element"}]]}]}';
    }

}
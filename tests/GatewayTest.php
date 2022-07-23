<?php

declare(strict_types=1);

namespace Gov\Data;

use DateTime;
use Psr\Log\NullLogger;
use PHPUnit\Framework\TestCase;
use Http\Mock\Client as MockClient;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Http\Discovery\Psr17FactoryDiscovery;
use Gov\Data\Exception\BadRequestException;
use Gov\Data\Schema\OasaRidership\Ridership;
use Psr\Http\Message\StreamFactoryInterface;
use Gov\Data\Exception\UnauthorizedException;
use Gov\Data\Schema\RoadTrafficAttica\Traffic;
use Psr\Http\Message\ResponseFactoryInterface;
use Gov\Data\Schema\OasaRidership\OasaRidershipCollection;
use Gov\Data\Schema\RoadTrafficAttica\RoadTrafficAtticaCollection;

class GatewayTest extends TestCase
{
    private const TOKEN = "123";

    private ResponseFactoryInterface $responseFactory;
    private StreamFactoryInterface $streamFactory;
    private ClientInterface $client;

    public function setUp(): void
    {
        $this->responseFactory = Psr17FactoryDiscovery::findResponseFactory();
        $this->streamFactory = Psr17FactoryDiscovery::findStreamFactory();
    }

    public function testNotJsonPayload(): void
    {
        $gateway = $this->createGateway('Invalid json');

        $this->expectException(BadRequestException::class);

        $gateway->fetch(Gateway::ROAD_TRAFFIC_ATTICA, new DateTime(), new DateTime());
    }

    public function testShouldCatchNotAuthorizedRequest(): void
    {
        $gateway = $this->createGateway('{"detail": "Δεν δόθηκαν διαπιστευτήρια."}', 401);

        $this->expectException(UnauthorizedException::class);

        $gateway->fetch(Gateway::ROAD_TRAFFIC_ATTICA, new DateTime(), new DateTime());
    }

    public function testShouldCatchBadRequestRequest(): void
    {
        $gateway = $this->createGateway('"Bad query parameters"', 400);

        $this->expectException(BadRequestException::class);

        $gateway->fetch(Gateway::ROAD_TRAFFIC_ATTICA, new DateTime(), new DateTime());
    }

    public function testShouldFetchRoadTrafficAttica(): void
    {
        $gateway = $this->createGateway($this->getSuccessRoadTraddicAtticaData());

        $response = $gateway->fetch(Gateway::ROAD_TRAFFIC_ATTICA, new DateTime(), new DateTime());

        $this->assertInstanceOf(RoadTrafficAtticaCollection::class, $response);
        $this->assertEquals(3, $response->count());

        $item = $response->current();

        $this->assertInstanceOf(Traffic::class, $item);
        $this->assertInstanceOf(DateTime::class, $item->getProcessTime());
        $this->assertEquals("2022-07-23T00:00:00Z", $item->getProcessTime()->format('Y-m-d\TH:i:s\Z'));

        if ($this->client instanceof MockClient) {
            $request = $this->client->getLastRequest();

            $this->assertEquals("data.gov.gr", $request->getUri()->getHost());
            $this->assertEquals("/api/v1/query/road_traffic_attica", $request->getUri()->getPath());
        }
    }

    public function testShouldFetchOasaRidership(): void
    {
        $gateway = $this->createGateway($this->getSuccessOasaRidershipData());
        $response = $gateway->fetch(Gateway::OASA_RIDERSHIP, new DateTime(), new DateTime());

        $this->assertInstanceOf(OasaRidershipCollection::class, $response);

        $item = $response->current();
        $this->assertInstanceOf(Ridership::class, $item);
    }

    private function createGateway(string $mockResponse = null, $statusCode = 200): Gateway
    {
        $this->client = $this->getClient();
        $response = $this->responseFactory->createResponse();
        $stream = $this->streamFactory->createStream($mockResponse);
        $response = $response->withBody($stream)
            ->withStatus($statusCode);

        $client = $this->mockResponse($this->client, $response);
        return new Gateway($client, self::TOKEN, new NullLogger());
    }

    private function getClient(): ClientInterface
    {
        return new MockClient();
    }

    private function mockResponse(ClientInterface $client, ResponseInterface $response): ClientInterface
    {
        if ($client instanceof MockClient) {
            $client->addResponse($response);

            return $client;
        }

        return $client;
    }

    private function getSuccessRoadTraddicAtticaData(): string
    {
        return <<<JSON
[
    {
        "deviceid": "MS116",
        "countedcars": 38040,
        "appprocesstime": "2022-07-23T00:00:00Z",
        "road_name": "Λ. ΚΗΦΙΣΟΥ",
        "road_info": "ΚΥΡΙΟΣ ΔΡΟΜΟΣ ΜΕ ΚΑΤΕΥΘΥΝΣΗ ΠΕΙΡΑΙΑ ΜΕΤΑ ΤΗ ΡΑΜΠΑ ΕΞΟΔΟΥ ΤΗΣ Λ. ΚΗΦΙΣΟΥ ΠΡΟΣ ΑΓ. ΙΩ. ΡΕΝΤΗ",
        "average_speed": 98.48790746582544
    },
    {
        "deviceid": "MS125",
        "countedcars": 5480,
        "appprocesstime": "2022-07-23T00:00:00Z",
        "road_name": "Λ. ΚΗΦΙΣΟΥ",
        "road_info": "ΡΑΜΠΑ ΕΞΟΔΟΥ ΠΡΟΣ ΟΔΟ ΠΕΙΡΑΙΩΣ ΚΑΙ ΓΕΦΥΡΑ ΑΝΑΣΤΡΟΦΗΣ ΤΟΥ ΚΛΑΔΟΥ ΤΗΣ Λ. ΚΗΦΙΣΟΥ ΜΕ ΚΑΤΕΥΘΥΝΣΗ ΠΕΙΡΑΙΑ",
        "average_speed": 60.26277372262774
    },
    {
        "deviceid": "MS126",
        "countedcars": 62840,
        "appprocesstime": "2022-07-23T00:00:00Z",
        "road_name": "Λ. ΚΗΦΙΣΟΥ",
        "road_info": "ΚΥΡΙΟΣ ΔΡΟΜΟΣ ΜΕ ΚΑΤΕΥΘΥΝΣΗ ΛΑΜΙΑ ΠΡΙΝ ΑΠΟ ΤΗΝ ΟΔΟ ΠΕΙΡΑΙΩΣ (ΥΨΟΣ ΟΔΟΥ ΜΕΤΣΟΒΟΥ)",
        "average_speed": 78.62316995544239
    }
]
JSON;
    }

    public function getSuccessOasaRidershipData(): string
    {
        return <<<JSON
[
    {
        "dv_validations": 5234,
        "dv_agency": "001",
        "dv_platenum_station": "UKN",
        "dv_route": null,
        "routes_per_hour": null,
        "load_dt": "2022-07-22T05:48:44Z",
        "date_hour": "2022-07-22T00:00:00Z"
    },
    {
        "dv_validations": 155,
        "dv_agency": "002",
        "dv_platenum_station": "KΑT",
        "dv_route": null,
        "routes_per_hour": null,
        "load_dt": "2022-07-22T05:48:59Z",
        "date_hour": "2022-07-22T00:00:00Z"
    },
    {
        "dv_validations": 353,
        "dv_agency": "002",
        "dv_platenum_station": "KΑTΕΧΑKΗ",
        "dv_route": null,
        "routes_per_hour": null,
        "load_dt": "2022-07-22T05:48:59Z",
        "date_hour": "2022-07-22T00:00:00Z"
    }
]
JSON;
    }
}

<?php

declare(strict_types=1);

namespace Gov\Data;

use DateTime;
use ReflectionClass;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use Gov\Data\Schema\ApiCollection;
use Psr\Http\Message\UriInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Http\Discovery\Psr17FactoryDiscovery;
use Gov\Data\Exception\BadRequestException;
use Gov\Data\Exception\UnauthorizedException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Gov\Data\Transformer\RoadTrafficAtticaTransformer;
use Gov\Data\Schema\RoadTrafficAttica\RoadTrafficAtticaCollection;

class Gateway
{
    private const ENDPOINT = "https://data.gov.gr/api/v1/query";

    public const ROAD_TRAFFIC_ATTICA = "road_traffic_attica";

    private const DATA = [
        self::ROAD_TRAFFIC_ATTICA => [
            'collection' => RoadTrafficAtticaCollection::class,
            'transformer' => RoadTrafficAtticaTransformer::class
        ]
    ];

    private RequestFactoryInterface $requestFactory;

    public function __construct(
        private readonly ClientInterface $client,
        private readonly string $token,
        private readonly LoggerInterface $logger
    ) {
        $this->requestFactory = Psr17FactoryDiscovery::findRequestFactory();
    }

    /**
     * @throws UnathorizedException
     * @throws BadRequestException
     * @throws ClientExceptionInterface
     */
    public function fetch(string $resource, DateTime $from, DateTime $to): ApiCollection
    {
        if (!array_key_exists($resource, self::DATA)) {
            throw new InvalidArgumentException(
                sprintf('Resource `%s` does not exists', $resource)
            );
        }
        $request = $this->requestFactory->createRequest('GET', $this->getResourceEndpoint($resource));
        $request = $this->authorize($request);
        $request = $request->withUri($this->createUriQuery($request, $from, $to));

        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            $this->logger->error($e->__toString());
            throw new BadRequestException($e->getMessage(), 500, $e);
        }

        $this->assertValid($response);

        $payload = json_decode($response->getBody()->__toString(), true);

        if ($payload === false || $payload === null) {
            throw new BadRequestException(sprintf("Invalid response body [%s]", json_last_error_msg()), 400);
        }

        $transformer = new ReflectionClass(self::DATA[$resource]['transformer']);
        $items = array_map(static function (array $item) use ($transformer) {
            return $transformer->newInstance()->transform($item);
        }, $payload);

        $collection = new ReflectionClass(self::DATA[$resource]['collection']);

        return $collection->newInstanceArgs([$items]);
    }

    private function getResourceEndpoint(string $resource): string
    {
        return sprintf("%s/%s", self::ENDPOINT, $resource);
    }

    private function authorize(RequestInterface $request): RequestInterface
    {
        return $request->withHeader('Authorization', sprintf("Token %s", $this->token));
    }

    private function createUriQuery(RequestInterface $request, DateTime $from, DateTime $to): UriInterface
    {
        $query = http_build_query([
            "date_from" => $from->format('Y-m-d'),
            "date_to" => $to->format('Y-m-d')
        ]);

        return $request->getUri()->withQuery($query);
    }

    private function assertValid(ResponseInterface $response): void
    {
        if ($response->getStatusCode() === 401) {
            $body = json_decode($response->getBody()->__toString(), true);
            throw new UnauthorizedException($body['detail'], 401);
        }

        $valid = [200, 201, 204];
        if (!in_array($response->getStatusCode(), $valid)) {
            throw new BadRequestException($response->getBody()->__toString(), 400);
        }
    }
}

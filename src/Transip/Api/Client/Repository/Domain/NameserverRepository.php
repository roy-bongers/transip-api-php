<?php

namespace Transip\Api\Client\Repository\Domain;

use Transip\Api\Client\Entity\Domain\Nameserver;
use Transip\Api\Client\Repository\ApiRepository;
use Transip\Api\Client\Repository\DomainRepository;

class NameserverRepository extends ApiRepository
{
    public const RESOURCE_NAME = 'nameservers';

    protected function getRepositoryResourceNames(): array
    {
        return [DomainRepository::RESOURCE_NAME, self::RESOURCE_NAME];
    }

    /**
     * @param string $domainName
     * @return Nameserver[]
     */
    public function getByDomainName(string $domainName): array
    {
        $nameservers      = [];
        $response         = $this->httpClient->get($this->getResourceUrl($domainName));
        $nameserversArray = $response['nameservers'] ?? [];

        foreach ($nameserversArray as $nameserverArray) {
            $nameservers[] = new Nameserver($nameserverArray);
        }

        return $nameservers;
    }

    /**
     * @param string       $domainName
     * @param Nameserver[] $nameservers
     */
    public function update(string $domainName, array $nameservers): void
    {
        $this->httpClient->put($this->getResourceUrl($domainName), ['nameservers' => $nameservers]);
    }
}
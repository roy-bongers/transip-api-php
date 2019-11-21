<?php

namespace Transip\Api\Client\Repository\Domain;

use Transip\Api\Client\Entity\Domain\WhoisContact;
use Transip\Api\Client\Repository\ApiRepository;

class ContactRepository extends ApiRepository
{
    protected function getRepositoryResourceNames(): array
    {
        return ['domains', 'contacts'];
    }

    /**
     * @param string $domainName
     * @return WhoisContact[]
     */
    public function getByDomainName(string $domainName): array
    {
        $contacts      = [];
        $response      = $this->httpClient->get($this->getResourceUrl($domainName));
        $contactsArray = $response['contacts'] ?? [];

        foreach ($contactsArray as $contactArray) {
            $contacts[] = new WhoisContact($contactArray);
        }

        return $contacts;
    }

    /**
     * @param string         $domainName
     * @param WhoisContact[] $contacts
     */
    public function update(string $domainName, array $contacts): void
    {
        $this->httpClient->put($this->getResourceUrl($domainName), ['contacts' => $contacts]);
    }
}

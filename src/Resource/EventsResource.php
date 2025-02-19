<?php

declare(strict_types=1);

namespace amcintosh\FreshBooks\Resource;

use Http\Client\HttpClient;
use Spatie\DataTransferObject\DataTransferObject;
use amcintosh\FreshBooks\Builder\IncludesBuilder;
use amcintosh\FreshBooks\Exception\FreshBooksException;
use amcintosh\FreshBooks\Exception\FreshBooksNotImplementedException;
use amcintosh\FreshBooks\Model\DataModel;
use amcintosh\FreshBooks\Model\ListModel;
use amcintosh\FreshBooks\Model\VisState;

/**
 * Resource for calls to /events endpoints.
 *
 * @package amcintosh\FreshBooks\Resource
 */
class EventsResource extends AccountingResource
{
    /**
     * The the url to the events resource.
     *
     * @param  string $accountId
     * @param  int $resourceId
     * @return string
     */
    protected function getUrl(string $accountId, int $resourceId = null): string
    {
        if (!is_null($resourceId)) {
            return "/events/account/{$accountId}/{$this->accountingPath}/{$resourceId}";
        }
        return "/events/account/{$accountId}/{$this->accountingPath}";
    }

    /**
     * Create a FreshBooksException from the json response from the events endpoint.
     *
     * @param  int $statusCode HTTP status code
     * @param  array $responseData The json-parsed response
     * @param  string $rawRespone The raw response body
     * @return void
     */
    protected function handleError(int $statusCode, array $responseData, string $rawRespone): void
    {
        if (!array_key_exists('message', $responseData) || !array_key_exists('code', $responseData)) {
            throw new FreshBooksException('Unknown error', $statusCode, null, $rawRespone);
        }

        $message = $responseData['message'];
        $errorCode = null;
        $details = [];

        foreach ($responseData['details'] as $detail) {
            if (
                in_array('type.googleapis.com/google.rpc.BadRequest', $detail)
                && array_key_exists('fieldViolations', $detail)
            ) {
                $details = $detail['fieldViolations'];
            }
        }
        var_dump($details);
        throw new FreshBooksException($message, $statusCode, null, $rawRespone, $errorCode, $details);
    }
}

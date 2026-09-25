<?php

namespace Wexample\SymfonyWex\Api\Controller\Entity;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyApi\Api\Attribute\QueryOption\LengthQueryOption;
use Wexample\SymfonyApi\Api\Attribute\QueryOption\PageQueryOption;
use Wexample\SymfonyApi\Api\Attribute\QueryOption\StringQueryOption;
use Wexample\SymfonyApi\Api\Class\ApiResponse;
use Wexample\SymfonyApi\Api\Controller\AbstractApiController;
use Wexample\SymfonyHelpers\Controller\AbstractController;
use Wexample\SymfonyWex\Api\Normalizer\Entity\ProcessItem\DefaultProcessItemNormalizer;
use Wexample\SymfonyWex\Repository\ProcessItemRepository;
use Wexample\SymfonyWex\Repository\ProcessRepository;

/**
 * Where the files of a process stand, read a page at a time.
 *
 * Asked for by the process, like its runs, and narrowed to some states when
 * the reader filters: the page opens on the ones that need something done.
 */
#[Route(path: 'api/process-item/', name: 'api_process_item_')]
class ProcessItemController extends AbstractApiController
{
    final public const string QUERY_OPTION_PROCESS = 'process';

    /** States, comma-separated; none is every state. */
    final public const string QUERY_OPTION_STATES = 'states';

    final public const string ROUTE_LIST = 'list';

    #[Route(path: 'list', name: self::ROUTE_LIST, methods: AbstractController::ROUTE_OPTIONS_METHOD_ONLY_GET, options: AbstractController::ROUTE_OPTIONS_ONLY_EXPOSE)]
    #[PageQueryOption]
    #[LengthQueryOption]
    #[StringQueryOption(key: self::QUERY_OPTION_PROCESS, default: '')]
    #[StringQueryOption(key: self::QUERY_OPTION_STATES, default: '')]
    public function list(
        Request $request,
        ProcessItemRepository $processItemRepository,
        ProcessRepository $processRepository,
        DefaultProcessItemNormalizer $normalizer,
    ): ApiResponse {
        $process = $processRepository->find(
            self::getQueryOptionValue($request, self::QUERY_OPTION_PROCESS, '')
        );

        if (! $process) {
            return self::apiResponseError('Unknown process.');
        }

        $states = array_values(array_filter(explode(
            ',',
            (string) self::getQueryOptionValue($request, self::QUERY_OPTION_STATES, '')
        )));
        $builder = $processItemRepository->queryByProcess($process, $states);

        $pagination = self::getQueryOptionPagination(
            request: $request,
            total: $processItemRepository->countAll($builder)
        );

        return self::apiResponsePaginated(
            pagination: $pagination,
            items: $normalizer->normalizeCollection(
                $processItemRepository->findPaginated(
                    page: $pagination->page,
                    length: $pagination->length,
                    builder: $builder
                )
            )
        );
    }
}

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
use Wexample\SymfonyWex\Api\Normalizer\Entity\ProcessRun\DefaultProcessRunNormalizer;
use Wexample\SymfonyWex\Repository\ProcessRepository;
use Wexample\SymfonyWex\Repository\ProcessRunRepository;

/**
 * What the runs of a process are read through.
 *
 * A listing is always asked for by the process that was run: a run says little
 * on its own, and nothing here answers with the runs of the whole board.
 */
#[Route(path: 'api/process-run/', name: 'api_process_run_')]
class ProcessRunController extends AbstractApiController
{
    final public const string QUERY_OPTION_PROCESS = 'process';

    final public const string ROUTE_LIST = 'list';

    #[Route(path: 'list', name: self::ROUTE_LIST, methods: AbstractController::ROUTE_OPTIONS_METHOD_ONLY_GET, options: AbstractController::ROUTE_OPTIONS_ONLY_EXPOSE)]
    #[PageQueryOption]
    #[LengthQueryOption]
    #[StringQueryOption(key: self::QUERY_OPTION_PROCESS, default: '')]
    public function list(
        Request $request,
        ProcessRunRepository $processRunRepository,
        ProcessRepository $processRepository,
        DefaultProcessRunNormalizer $normalizer,
    ): ApiResponse {
        $process = $processRepository->find(
            self::getQueryOptionValue($request, self::QUERY_OPTION_PROCESS, '')
        );

        if (! $process) {
            return self::apiResponseError('Unknown process.');
        }

        $builder = $processRunRepository->queryByProcess($process);

        $pagination = self::getQueryOptionPagination(
            request: $request,
            total: $processRunRepository->countAll($builder)
        );

        return self::apiResponsePaginated(
            pagination: $pagination,
            items: $normalizer->normalizeCollection(
                $processRunRepository->findPaginated(
                    page: $pagination->page,
                    length: $pagination->length,
                    builder: $builder
                )
            )
        );
    }
}

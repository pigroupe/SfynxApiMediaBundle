<?php
namespace Sfynx\ApiMediaBundle\Layers\Presentation\Coordination\Media\Command;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sfynx\ApiMediaBundle\Layers\Application\Cqrs\Jwt\Command\JwtCommand;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Token\TokenService;

/**
 * Class CreateJwtTokenController.
 *
 * @category   Sfynx\ApiMediaBundle\Layers
 * @package    Presentation
 * @subpackage Coordination\Media\Command
 */
class CreateJwtTokenController
{
    /** @var TokenService */
    protected $tokenService;

    /**
     * CreateJwtTokenController constructor.
     *
     * @param TokenService $tokenService
     */
    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * Create a new media
     *
     * @param Request $request
     */
    public function execute(Request $request)
    {
        $response = new Response();

        try {
            /** @var JwtCommand $JwtCommand */
            $JwtCommand = $this->tokenService->setSigningKey($request->request->all());

            $response->setStatusCode(Response::HTTP_CREATED);
            $response->setContent($JwtCommand->toJson());
        } catch (\Exception $e) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent($e->getMessage());
        }

        return $response;
    }
}

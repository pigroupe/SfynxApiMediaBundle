<?php
namespace Sfynx\ApiMediaBundle\Layers\Presentation\Coordination\Media\Query;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Generalisation\Interfaces\MediaManagerInterface;
use Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception\MediaNotFoundException;

/**
 * Class GetOnBinaryController
 * @category   Sfynx\ApiMediaBundle\Layers
 * @package    Presentation
 * @subpackage Coordination\Media\Query
 */
class GetOneBinaryController
{
    /** @var MediaManagerInterface */
    protected $manager;

    /**
     * GetOnBinaryController constructor.
     * @param MediaManagerInterface $manager
     */
    public function __construct(MediaManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Get a specific binary media
     *
     * @param Request $request
     * @param string $reference
     * @param string $_format
     */
    public function execute(Request $request, $reference, $_format)
    {
        $response = (new GetOneController($this->manager))->execute($request, $reference, $_format);
        if ($response->getStatusCode() == Response::HTTP_OK) {
            $response->headers->set('Content-Type', 'application/octet-stream');
        }

        return $response;
    }
}

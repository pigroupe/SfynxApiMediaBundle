<?php
namespace Sfynx\ApiMediaBundle\Layers\Presentation\Coordination\Media\Command;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Generalisation\Interfaces\MediaManagerInterface;
use Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception\MediaNotFoundException;

/**
 * Class DeleteOneController
 * @category   Sfynx\ApiMediaBundle\Layers
 * @package    Presentation
 * @subpackage Coordination\Media\Command
 */
class DeleteOneController
{
    /** @var MediaManagerInterface */
    protected $manager;

    /**
     * DeleteOneController constructor.
     * @param MediaManagerInterface $manager
     */
    public function __construct(MediaManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Delete a specific media
     *
     * @param Request $request
     * @param $reference
     */
    public function execute(Request $request, $reference)
    {
        $response = new Response();
        try {
            $this->manager->deleteMedia($reference);
            $response->setStatusCode(Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent($e->getMessage());
        }

        return $response;
    }
}

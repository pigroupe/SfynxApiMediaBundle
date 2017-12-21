<?php
namespace Sfynx\ApiMediaBundle\Layers\Presentation\Coordination\Media\Command;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Generalisation\Interfaces\MediaManagerInterface;
use Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception\MediaNotFoundException;

/**
 * Class UpdateOneController
 * @category   Sfynx\ApiMediaBundle\Layers
 * @package    Presentation
 * @subpackage Coordination\Media\Command
 */
class UpdateOneController
{
    /** @var MediaManagerInterface */
    protected $manager;

    /**
     * UpdateOneController constructor.
     * @param MediaManagerInterface $manager
     */
    public function __construct(MediaManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Update a specific media
     *
     * @param Request $request
     * @param $reference
     */
    public function execute(Request $request, $reference)
    {
        $response = new Response();
        try {
            $media = $this->manager->retrieveMedia($reference);
            $media->setMetadata(array_merge(
                $media->getMetadata(),
                $request->request->get('metadata', [])
            ));
            $this->manager->merge($media, true);
            $response->setStatusCode(Response::HTTP_OK);
        } catch (MediaNotFoundException $e) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent($e->getMessage());
            $response->headers->set('Content-Type', 'text/html');
        } catch (\Exception $e) {
            $response->setStatusCode(Response::HTTP_SERVICE_UNAVAILABLE);
            $response->setContent($e->getMessage());
            $response->headers->set('Content-Type', 'text/html');
        }

        return $response;
    }
}

<?php
namespace Sfynx\ApiMediaBundle\Layers\Presentation\Coordination\Media\Query;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Generalisation\Interfaces\MediaManagerInterface;
use Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception\MediaNotFoundException;
use Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception\NoMatchedTransformerException;

/**
 * Class GetOneController
 * @category   Sfynx\ApiMediaBundle\Layers
 * @package    Presentation
 * @subpackage Coordination\Media\Query
 */
class GetOneController
{
    /** @var MediaManagerInterface */
    protected $manager;

    /**
     * GetOneController constructor.
     * @param MediaManagerInterface $manager
     */
    public function __construct(MediaManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Get a specific media
     *
     * @param Request $request
     * @param string $reference
     * @param string $_format
     */
    public function execute(Request $request, $reference, $_format)
    {
        $response = new Response();
        try {
            $media = $this->manager->retrieveMedia($reference);
            try {
                $responseMedia = $this->manager->transform(
                    $media,
                    array_merge(
                        $request->query->all(),
                        array('format' => $_format)
                    )
                );
            } catch (InvalidOptionsException $e) {
                $responseMedia = $this->manager->transform(
                    $media,
                    array('format' => $_format)
                );
            }

            $response->setPublic();
            $response->setStatusCode(Response::HTTP_OK);
            $response->headers->set('Content-Type', $responseMedia->getContentType());
            $response->headers->set('Content-Length', $responseMedia->getContentLength());
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->setETag($responseMedia->getETag());
            $response->setLastModified($responseMedia->getLastModifiedAt());
            $response->setContent($responseMedia->getContent());

            if (null !== $responseMedia->getContentDisposition()) {
                $response->headers->set('Content-Disposition', $responseMedia->getContentDisposition());
            }
        } catch (MediaNotFoundException $e) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent($e->getMessage());
            $response->headers->set('Content-Type', 'text/html');
        } catch (NoMatchedTransformerException $e) {
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

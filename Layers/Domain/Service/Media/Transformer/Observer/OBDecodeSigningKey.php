<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Observer;

use Exception;
use stdClass;

use Sfynx\CoreBundle\Layers\Domain\Workflow\Observer\Generalisation\Command\AbstractObserver;
use Sfynx\CoreBundle\Layers\Domain\Service\Request\Generalisation\RequestInterface;
use Sfynx\CoreBundle\Layers\Infrastructure\Exception\WorkflowException;
use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Command\MediaCommand;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Specification\SpecIsGetOriginalContent;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Specification\SpecIsCacheFromStorage;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Specification\SpecIsCacheLocale;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Specification\SpecIsCacheStorage;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Token\TokenService;
use Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception\MediaNotAuthorisationException;

/**
 * Class OBDecodeSigningKey
 *
 * @category Sfynx\ApiMediaBundle\Layers
 * @package Domain
 * @subpackage Service\Media\Transformer\Observer
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @copyright  2016 PI-GROUPE
 */
class OBDecodeSigningKey extends AbstractObserver
{
    /** @var MediaCommand */
    protected $wfCommand;
    /** @var Media  */
    protected $media;
    /** @var TokenService */
    protected $tokenService;
    /** @var RequestInterface */
    protected $request;

    /**
     * OBSetParameters constructor.
     * @param Media $media
     * @param TokenService $tokenService
     * @param RequestInterface $request
     */
    public function __construct(Media $media, TokenService $tokenService, RequestInterface $request)
    {
        $this->media = $media;
        $this->tokenService = $tokenService;
        $this->request = $request;
    }

    /**
     * Set Parameters and specifications used by observer
     *
     * @return AbstractObserver
     * @throws WorkflowException
     */
    protected function execute(): AbstractObserver
    {
        // we get signing informations (connected, username, roles)
        $signingFromMediaRegistration = $this->media->getSigning();

        // imagesUrl: http://tgapidoc.alterway.devs/media/2130863729-1530180919-b9dc6484df960c683989f9f26b2f31ae-8928.pdf?signingKey=eyJhbGciOiJSUzI1NiJ9.eyJzdWIiOiJNZWRpYSBkb3dubG9hZCIsImV4cCI6MTUzMDI4MzQ2MiwiY29udGV4dCI6eyJkYXRlIjp7ImNyZWF0ZWRfYXQiOjE1MzAyNzk4NjIsInN0YXJ0IjoxNTMwMjc5ODYyfSwicmFuZ2VpcCI6WyIxMC4yNTUuMC4yIl0sInVuaXF1ZSI6ZmFsc2UsIngtdGVuYW50LWlkIjoiIiwidXNlciI6eyJ1c2VybmFtZSI6ImFkbWluQGFsdGVyd2F5LmZyIiwicm9sZXMiOlsiUk9MRV9BRE1JTiIsIlJPTEVfVVNFUiJdfSwibWVkaWEiOnsibWVkaWFUeXBlIjoiQ2FkYXN0cmVQbGFuIiwibWVkaWFJZCI6IjU3NzUiLCJuYW1lIjoiY2FkYXN0cmVQbGFuIiwidXJpIjoiaHR0cDovL3RnYXBpZG9jLmFsdGVyd2F5LmRldnMvbWVkaWEvMjEzMDg2MzcyOS0xNTMwMTgwOTE5LWI5ZGM2NDg0ZGY5NjBjNjgzOTg5ZjlmMjZiMmYzMWFlLTg5MjgiLCJleHRlbnNpb24iOiJwZGYiLCJzaXplIjoiMjE2NDI2NiJ9fSwia2lkIjoiZWFhMzk5YWNhNTg0YzQ2OWEwMTgyODEyYTNiNTBjNGZlNTJmZDBhMjBjY2FhOWYxODBlMTliNzBiZGMwY2YyMiIsImlhdCI6MTUzMDI3OTg2Mn0.RuEsLw4LXQIrmFxmgoBeN1AY9Em1zBgwGek0PVJUXPwTp3OZMNhO0qwUmJWx_a-kPanAlzkzxYT9bE_VGdtZfFpsDZRGv7pa_VCia0batwUOuIRle3rSKixNY9j0HrL5wD23VYaRW2WyySHGIjnGg6rWaP8xI8eMNu2hmNPzw2iIYEN6W7PgCi530823JfkpZxixa86DQER3prwVZjJCoRwCK7EW2hsdfsAoFz8-6VlqN_wMsI1GcDLf3tH2SsDPciNjF33bBQ13yEtx6Lm9AUXFQze7kgDnJbIozJjOuSPaQQsnI5Zy2-vYONCv76oDi876M4gNJaQ8-UFlgb7xzw
        $signingFromMediaRegistration = [
            'connected' => true,
            'roles' => [
                0 => 'ROLE_ADMIN',
//                1 => 'ROLE_USER',
                2 => 'ROLE_USER_ERROR',
            ],
            'usernames' => [
                'admin@alterway.fr'
            ],
            'rangeip' => [
                '10.255.0.2.2'
            ]
        ];

        // we get command values
        $options = array_filter($this->wfCommand->toArray());

        // if the media has been saved with no permission and no rangeip constraints
        if (empty($signingFromMediaRegistration['rangeip'])) {
            if (null === $signingFromMediaRegistration
                || empty($signingFromMediaRegistration['connected'])
                || false == $signingFromMediaRegistration['connected']

            ) {
                return $this;
            } elseif (empty($options['signingKey'])) {
                // if the media has been saved with permission and there is no jwt token
                throw new MediaNotAuthorisationException();
            }
        }

        // we get playload information of th jwt token.
        // Return 503 Response if the JWT has been expired
        $playload = $this->tokenService->decode($options['signingKey']);

        if (!empty($signingFromMediaRegistration['rangeip'])
            && !empty(array_intersect($playload['context']['rangeip'], $signingFromMediaRegistration['rangeip']))
        ) {
            throw new MediaNotAuthorisationException();
        }

        // Return 503 response if the start date is not yet effective.
        if (!empty($playload['context']['date']['start'])) {
            $startTime = $playload['context']['date']['start'];
            $now = time(true);
            $diff = $startTime - $now;

            if ($diff > 0) {
                throw new MediaNotAuthorisationException();
            }
        }

        $cacheKids = [];
        $cacheKids = ['eaa399aca584c469a0182812a3b50c4fe52fd0a20ccaa9f180e19b70bdc0cf22'];
        // if the media has been saved with an unique upload authorisation
        if ( !empty($playload['kid'])
            && in_array($playload['kid'], $cacheKids)
        ) {
            throw new MediaNotAuthorisationException();
        }

        return $this;

        if (null !== $signingFromMediaRegistration && !(
                ( // if the media has been saved with the connection constraint and user roles accepted for authorization.
                    !empty($signingFromMediaRegistration['connected']) && true == $signingFromMediaRegistration['connected']
                    && !empty($signingFromMediaRegistration['roles']) && empty($signingFromMediaRegistration['usernames'])
                    && !empty(array_intersect($playload['context']['user']['roles'], $signingFromMediaRegistration['roles']))
                ) ||  // if the media has been saved with only the connection constraint for authorization.
                (
                    !empty($signingFromMediaRegistration['connected']) && true == $signingFromMediaRegistration['connected']
                    && !empty($signingFromMediaRegistration['usernames']) && empty($signingFromMediaRegistration['roles'])
                    && in_array($playload['context']['user']['username'], $signingFromMediaRegistration['usernames'])
                ) ||  // if the media has been saved with the connection constraint and role and username  is not accepted for authorization
                (
                    !empty($signingFromMediaRegistration['connected']) && true == $signingFromMediaRegistration['connected']
                    && !empty($signingFromMediaRegistration['usernames']) && !empty($signingFromMediaRegistration['roles'])
                    && !empty(array_intersect($playload['context']['user']['roles'], $signingFromMediaRegistration['roles']))
                    && in_array($playload['context']['user']['username'], $signingFromMediaRegistration['usernames'])
                )
            )
        ) {
            throw new MediaNotAuthorisationException();
        }
    }
}

<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Resolver;

use Symfony\Component\OptionsResolver\Options;

/**
 * Class DocumentResolver
 *
 * @category PromotionContext
 * @package Presentation
 * @subpackage Request\DateList\Command
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
class DocumentResolver extends DefaultResolver
{
    const FORMATS = ['pdf', 'doc', 'docx', 'rtf', 'xls', 'xlsx', 'odt'];
}

<?php
namespace HHIT\Doctrine\ORM\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 */
class BindEntity
{

    /**
     * @var string
     * @Required
     */
    public $name;
}

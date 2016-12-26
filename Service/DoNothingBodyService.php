<?php

namespace Maith\NewsletterBundle\Service;

/**
 * Description of DoNothingBodyHandler.
 *
 * @author Rodrigo Santellan
 */
class DoNothingBodyService implements BodyHandlerInterface
{
    public function changeBody($body, $trackLinks = false, $email = '', $id = '')
    {
        return $body;
    }
}

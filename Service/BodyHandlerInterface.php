<?php

namespace Maith\NewsletterBundle\Service;

/**
 *
 * @author rodrigo
 */
interface BodyHandlerInterface {
  
  public function changeBody($body, $trackLinks = false, $email = '', $id = '');
}
